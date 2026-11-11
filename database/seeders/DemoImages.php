<?php

namespace Database\Seeders;

/**
 * Renders Powerath-branded demo product images with GD (no external assets or licences involved).
 * These are development placeholders â€” upload real photography from the admin panel for production.
 */
class DemoImages
{
    private const FONT_DISPLAY = 'C:/Windows/Fonts/impact.ttf';
    private const FONT_TEXT = 'C:/Windows/Fonts/arialbd.ttf';

    private string $out;
    private string $logoPath;

    public function __construct()
    {
        $this->out = storage_path('app/public');
        $this->logoPath = public_path('assets/images/logo.png');
        foreach (['products', 'variants', 'categories'] as $d) {
            @mkdir("{$this->out}/{$d}", 0775, true);
        }
    }

    public static function available(): bool
    {
        return function_exists('imagettftext') && is_file(self::FONT_DISPLAY) && is_file(self::FONT_TEXT);
    }

    /** Render a set of product shots; returns [main, gallery[]] storage-relative paths. */
    public function product(string $key, string $kind, array $o): array
    {
        $obj = $this->object($kind, $o);

        $main = "products/{$key}.jpg";
        $this->save($this->compose($obj, 900, 900, 'light'), $main);

        $dark = "products/{$key}-dark.jpg";
        $this->save($this->compose($obj, 900, 900, 'dark'), $dark);

        $zoom = "products/{$key}-detail.jpg";
        $this->save($this->compose($obj, 900, 900, 'light', 1.9, 0.0, 0.1), $zoom);

        return [$main, [$dark, $zoom]];
    }

    public function variantImage(string $key, string $kind, array $o): string
    {
        $path = "variants/{$key}.jpg";
        $this->save($this->compose($this->object($kind, $o), 900, 900, 'light'), $path);

        return $path;
    }

    public function category(string $key, string $kind, array $o): string
    {
        $path = "categories/{$key}.jpg";
        $this->save($this->compose($this->object($kind, $o), 1200, 800, 'dark', 0.8), $path);

        return $path;
    }

    /* ---------------------------------------------------------------- objects */

    private function object(string $kind, array $o): \GdImage
    {
        return match ($kind) {
            'tub' => $this->tub($o),
            'bottle' => $this->bottle($o),
            'shaker' => $this->shaker($o),
            'straps' => $this->straps($o),
            'towel' => $this->towel($o),
        };
    }

    private function layer(int $w, int $h): \GdImage
    {
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        return $im;
    }

    private function col(\GdImage $im, string $hex, int $alpha = 0): int
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocatealpha($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), $alpha);
    }

    private function text(\GdImage $im, string $s, int $cx, int $baseline, float $size, string $hex, string $font = self::FONT_DISPLAY, float $spacing = 0): void
    {
        $color = $this->col($im, $hex);
        if ($spacing > 0) { // manual letter-spacing
            $chars = mb_str_split($s);
            $widths = array_map(fn ($c) => ($b = imagettfbbox($size, 0, $font, $c)) ? $b[2] - $b[0] : 0, $chars);
            $total = array_sum($widths) + $spacing * (count($chars) - 1);
            $x = $cx - $total / 2;
            foreach ($chars as $i => $c) {
                imagettftext($im, $size, 0, (int) $x, $baseline, $color, $font, $c);
                $x += $widths[$i] + $spacing;
            }

            return;
        }
        $b = imagettfbbox($size, 0, $font, $s);
        imagettftext($im, $size, 0, (int) ($cx - ($b[2] - $b[0]) / 2), $baseline, $color, $font, $s);
    }

    private function logoPlate(\GdImage $im, int $cx, int $top, int $w, int $h): void
    {
        $white = $this->col($im, '#ffffff');
        imagefilledrectangle($im, $cx - $w / 2, $top, $cx + $w / 2, $top + $h, $white);
        $logo = imagecreatefrompng($this->logoPath);
        [$lw, $lh] = [imagesx($logo), imagesy($logo)];
        $th = $h - 20;
        $tw = (int) ($lw * $th / $lh);
        imagecopyresampled($im, $logo, (int) ($cx - $tw / 2), $top + 10, 0, 0, $tw, $th, $lw, $lh);
    }

    /** Cylindrical shading applied to every opaque pixel, based on horizontal position. */
    private function shade(\GdImage $im, int $cx, int $radius, float $strength = 1.0): void
    {
        imagealphablending($im, false);
        [$w, $h] = [imagesx($im), imagesy($im)];
        for ($x = max(0, $cx - $radius); $x < min($w, $cx + $radius); $x++) {
            $u = ($x - $cx) / $radius;
            $m = 1 - $strength * (0.5 - 0.55 * pow(max(0, 1 - $u * $u), 0.6));
            $spec = $strength * 70 * exp(-pow(($u + 0.42) / 0.1, 2)) + $strength * 18 * exp(-pow(($u - 0.7) / 0.08, 2));
            for ($y = 0; $y < $h; $y++) {
                $c = imagecolorat($im, $x, $y);
                $a = ($c >> 24) & 127;
                if ($a === 127) {
                    continue;
                }
                $r = min(255, (int) ((($c >> 16) & 255) * $m + $spec));
                $g = min(255, (int) ((($c >> 8) & 255) * $m + $spec));
                $b = min(255, (int) (($c & 255) * $m + $spec));
                imagesetpixel($im, $x, $y, ($a << 24) | ($r << 16) | ($g << 8) | $b);
            }
        }
        imagealphablending($im, true);
    }

    private function tub(array $o): \GdImage
    {
        $o += ['body' => '#151515', 'accent' => '#e3262e', 'lid' => '#e3262e', 'label' => '#151515', 'text' => '#ffffff',
            'title' => ['PRODUCT'], 'sub' => '', 'tag' => '', 'scale' => 1.0];
        $s = $o['scale'];
        $W = 1000;
        $H = 1200;
        $im = $this->layer($W, $H);
        $cx = $W / 2;
        $R = (int) (300 * $s);
        $ry = (int) (60 * $s);
        $bodyTop = (int) ($H - 160 - 800 * $s);
        $bodyBottom = $H - 160;

        // body + rounded bottom
        imagefilledrectangle($im, $cx - $R, $bodyTop, $cx + $R, $bodyBottom - $ry, $this->col($im, $o['body']));
        imagefilledellipse($im, $cx, $bodyBottom - $ry, 2 * $R, 2 * $ry, $this->col($im, $o['body']));

        // label (everything below is positioned relative to its top edge)
        $lt = $bodyTop + (int) (90 * $s);
        $lb = $lt + (int) (580 * $s);
        imagefilledrectangle($im, $cx - $R, $lt, $cx + $R, $lb, $this->col($im, $o['label']));
        imagefilledrectangle($im, $cx - $R, $lt, $cx + $R, $lt + 14, $this->col($im, $o['accent']));
        imagefilledrectangle($im, $cx - $R, $lb - 14, $cx + $R, $lb, $this->col($im, $o['accent']));

        $this->logoPlate($im, $cx, $lt + (int) (36 * $s), (int) (300 * $s), (int) (180 * $s));
        $y = $lt + (int) (320 * $s);
        foreach ($o['title'] as $line) {
            $this->text($im, $line, $cx, $y, 54 * $s, $o['text']);
            $y += (int) (68 * $s);
        }
        if ($o['sub']) {
            $this->text($im, $o['sub'], $cx, $y + (int) (4 * $s), 19 * $s, $this->lighten($o['accent'], 0.4), self::FONT_TEXT, 2);
        }
        if ($o['tag']) {
            $bw = (int) (360 * $s);
            imagefilledrectangle($im, $cx - $bw / 2, $lb - (int) (88 * $s), $cx + $bw / 2, $lb - (int) (38 * $s), $this->col($im, $o['accent']));
            $this->text($im, $o['tag'], $cx, (int) ($lb - 52 * $s), 20 * $s, '#ffffff', self::FONT_TEXT, 2);
        }

        // lid
        $lidH = (int) (120 * $s);
        $lidTop = $bodyTop - $lidH + 20;
        imagefilledrectangle($im, $cx - $R - 8, $lidTop, $cx + $R + 8, $bodyTop + 20, $this->col($im, $o['lid']));
        imagefilledellipse($im, $cx, $lidTop, 2 * ($R + 8), 2 * $ry, $this->col($im, $this->lighten($o['lid'], 0.25)));
        imagefilledellipse($im, $cx, $bodyTop + 20, 2 * ($R + 8), 2 * $ry, $this->col($im, $o['lid']));
        for ($i = -$R + 20; $i < $R; $i += 28) { // grip ridges
            imagefilledrectangle($im, $cx + $i, $lidTop + 10, $cx + $i + 5, $bodyTop + 14, $this->col($im, $this->lighten($o['lid'], -0.18)));
        }

        $this->shade($im, (int) $cx, $R + 8);

        return $im;
    }

    private function bottle(array $o): \GdImage
    {
        $o += ['body' => '#e3262e', 'accent' => '#ffffff', 'cap' => '#151515', 'label' => '#ffffff', 'text' => '#151515', 'title' => ['MULTI'], 'sub' => ''];
        $W = 1000;
        $H = 1200;
        $im = $this->layer($W, $H);
        $cx = $W / 2;
        $R = 215;
        $bottom = $H - 160;
        $top = 330;

        imagefilledrectangle($im, $cx - $R, $top + 120, $cx + $R, $bottom - 50, $this->col($im, $o['body']));
        imagefilledellipse($im, $cx, $bottom - 50, 2 * $R, 100, $this->col($im, $o['body']));
        imagefilledpolygon($im, [$cx - $R, $top + 120, $cx - 110, $top + 20, $cx + 110, $top + 20, $cx + $R, $top + 120], $this->col($im, $o['body']));
        imagefilledrectangle($im, $cx - 110, $top - 10, $cx + 110, $top + 40, $this->col($im, $o['body']));

        imagefilledrectangle($im, $cx - $R, $top + 220, $cx + $R, $bottom - 120, $this->col($im, $o['label']));
        $this->logoPlate($im, $cx, $top + 245, 270, 170);
        $this->text($im, $o['title'][0], $cx, $top + 485, 46, $o['text']);
        if (isset($o['title'][1])) {
            $this->text($im, $o['title'][1], $cx, $top + 540, 38, '#e3262e');
        }
        if ($o['sub']) {
            $this->text($im, $o['sub'], $cx, $top + 580, 18, '#525252', self::FONT_TEXT, 3);
        }

        $capCol = $this->col($im, $o['cap']);
        imagefilledrectangle($im, $cx - 125, $top - 120, $cx + 125, $top - 6, $capCol);
        imagefilledellipse($im, $cx, $top - 120, 250, 40, $this->col($im, $this->lighten($o['cap'], 0.2)));
        for ($i = -110; $i < 125; $i += 24) {
            imagefilledrectangle($im, $cx + $i, $top - 108, $cx + $i + 5, $top - 12, $this->col($im, $this->lighten($o['cap'], 0.15)));
        }
        $this->shade($im, (int) $cx, 235);

        return $im;
    }

    private function shaker(array $o): \GdImage
    {
        $o += ['accent' => '#e3262e'];
        $W = 1000;
        $H = 1200;
        $im = $this->layer($W, $H);
        $cx = $W / 2;
        $bottom = $H - 150;
        $top = 360;
        $rt = 235;
        $rb = 190;

        imagefilledpolygon($im, [$cx - $rt, $top, $cx + $rt, $top, $cx + $rb, $bottom - 40, $cx - $rb, $bottom - 40], $this->col($im, '#2a2a2a'));
        imagefilledellipse($im, $cx, $bottom - 40, 2 * $rb, 80, $this->col($im, '#2a2a2a'));
        // measurement marks
        for ($i = 0; $i < 6; $i++) {
            $y = $top + 100 + $i * 100;
            imagefilledrectangle($im, $cx + 135, $y, $cx + 195, $y + 6, $this->col($im, '#ffffff'));
        }
        $this->logoPlate($im, $cx - 40, $top + 190, 250, 160);
        $this->text($im, 'POWER ATHLETE', $cx - 40, $top + 420, 24, '#ffffff', self::FONT_TEXT, 3);

        // lid + flip cap
        imagefilledrectangle($im, $cx - $rt - 12, $top - 70, $cx + $rt + 12, $top + 10, $this->col($im, $o['accent']));
        imagefilledellipse($im, $cx, $top - 70, 2 * ($rt + 12), 50, $this->col($im, $this->lighten($o['accent'], 0.25)));
        imagefilledrectangle($im, $cx - 120, $top - 150, $cx + 120, $top - 60, $this->col($im, '#151515'));
        imagefilledellipse($im, $cx, $top - 150, 240, 36, $this->col($im, '#333333'));
        $this->shade($im, (int) $cx, $rt + 14);

        return $im;
    }

    private function straps(array $o): \GdImage
    {
        $W = 1000;
        $H = 1200;
        $im = $this->layer($W, $H);
        $cx = $W / 2;
        $cy = 640;
        $half = 150; // roll radius (it lies on its side)

        // tail hanging from the front of the roll
        imagefilledpolygon($im, [$cx + 120, $cy + $half - 20, $cx + 220, $cy + $half - 20, $cx + 250, $cy + 440, $cx + 150, $cy + 440], $this->col($im, '#151515'));
        imagefilledrectangle($im, $cx + 160, $cy + $half - 20, $cx + 190, $cy + 440, $this->col($im, '#e3262e'));

        // roll body (horizontal cylinder) + right end
        $body = $this->col($im, '#1c1c1c');
        imagefilledrectangle($im, $cx - 280, $cy - $half, $cx + 280, $cy + $half, $body);
        imagefilledellipse($im, $cx + 280, $cy, 120, 2 * $half, $body);
        imagefilledrectangle($im, $cx - 280, $cy - 30, $cx + 280, $cy + 30, $this->col($im, '#e3262e'));
        $this->text($im, 'POWERATH', $cx + 10, $cy + 14, 38, '#ffffff', self::FONT_DISPLAY, 8);

        // left face showing the wound webbing
        for ($i = 0; $i < 7; $i++) {
            imagefilledellipse($im, $cx - 280, $cy, 120 - $i * 12, 2 * $half - $i * 42, $this->col($im, $i % 2 ? '#303030' : '#121212'));
        }
        imagefilledellipse($im, $cx - 280, $cy, 22, 36, $this->col($im, '#8f8d87'));

        // vertical shading across the roll
        imagealphablending($im, false);
        for ($y = $cy - $half; $y <= $cy + $half; $y++) {
            $u = ($y - $cy) / $half;
            $m = 1 - 0.5 + 0.55 * pow(max(0, 1 - $u * $u), 0.6);
            $spec = 55 * exp(-pow(($u + 0.45) / 0.1, 2));
            for ($x = $cx - 345; $x <= $cx + 345; $x++) {
                $c = imagecolorat($im, $x, $y);
                $a = ($c >> 24) & 127;
                if ($a === 127) {
                    continue;
                }
                $r = min(255, (int) ((($c >> 16) & 255) * $m + $spec));
                $g = min(255, (int) ((($c >> 8) & 255) * $m + $spec));
                $b = min(255, (int) (($c & 255) * $m + $spec));
                imagesetpixel($im, $x, $y, ($a << 24) | ($r << 16) | ($g << 8) | $b);
            }
        }
        imagealphablending($im, true);

        return $im;
    }

    private function towel(array $o): \GdImage
    {
        $W = 1000;
        $H = 1200;
        $im = $this->layer($W, $H);
        $cx = $W / 2;
        $y = 880;
        $cols = ['#e8e6e1', '#d6d3cc', '#151515'];
        foreach ($cols as $i => $c) {
            $w = 640 - $i * 20;
            $h = 150;
            imagefilledrectangle($im, $cx - $w / 2, $y - $h, $cx + $w / 2, $y, $this->col($im, $c));
            imagefilledellipse($im, $cx - $w / 2, $y - $h / 2, 60, $h, $this->col($im, $this->lighten($c, -0.1)));
            for ($t = 0; $t < $h; $t += 9) { // terry texture lines
                imagefilledrectangle($im, $cx - $w / 2 + 20, $y - $h + $t, $cx + $w / 2 - 6, $y - $h + $t + 2, $this->col($im, $this->lighten($c, $i === 2 ? 0.08 : -0.06)));
            }
            $y -= $h - 6;
        }
        imagefilledrectangle($im, $cx - 300, 505, $cx + 300, 530, $this->col($im, '#e3262e'));
        $this->logoPlate($im, $cx - 120, 598, 210, 128);

        return $im;
    }

    private function lighten(string $hex, float $amt): string
    {
        $hex = ltrim($hex, '#');
        $c = array_map(fn ($i) => hexdec(substr($hex, $i, 2)), [0, 2, 4]);
        $c = array_map(fn ($v) => (int) max(0, min(255, $amt >= 0 ? $v + (255 - $v) * $amt : $v * (1 + $amt))), $c);

        return sprintf('#%02x%02x%02x', ...$c);
    }

    /* ------------------------------------------------------------ composition */

    private function compose(\GdImage $obj, int $W, int $H, string $bg, float $fit = 0.86, float $offX = 0.0, float $offY = 0.0): \GdImage
    {
        $S = 2; // supersample for smooth edges
        $cw = $W * $S;
        $ch = $H * $S;
        $canvas = imagecreatetruecolor($cw, $ch);

        // background gradient
        [$c1, $c2] = $bg === 'dark' ? [[44, 44, 46], [10, 10, 11]] : [[250, 249, 246], [220, 217, 210]];
        for ($y = 0; $y < $ch; $y++) {
            $t = $y / $ch;
            imageline($canvas, 0, $y, $cw, $y, imagecolorallocate($canvas,
                (int) ($c1[0] + ($c2[0] - $c1[0]) * $t), (int) ($c1[1] + ($c2[1] - $c1[1]) * $t), (int) ($c1[2] + ($c2[2] - $c1[2]) * $t)));
        }

        // crop object to its visible bounds
        [$ox, $oy, $ow, $oh] = $this->bounds($obj);
        $scale = min($cw * $fit / $ow, $ch * $fit / $oh);
        $dw = (int) ($ow * $scale);
        $dh = (int) ($oh * $scale);
        $dx = (int) (($cw - $dw) / 2 + $offX * $cw);
        $dy = (int) (($ch - $dh) / 2 + $offY * $ch);

        // soft contact shadow
        imagealphablending($canvas, true);
        for ($i = 0; $i < 14; $i++) {
            $a = 118 + (int) ($i * 0.5);
            imagefilledellipse($canvas, (int) ($dx + $dw / 2), (int) ($dy + $dh - $dh * 0.02), (int) ($dw * (0.95 - $i * 0.022)), (int) ($dh * (0.075 - $i * 0.003)),
                imagecolorallocatealpha($canvas, 0, 0, 0, min(126, $a)));
        }

        imagecopyresampled($canvas, $obj, $dx, $dy, $ox, $oy, $dw, $dh, $ow, $oh);

        $final = imagecreatetruecolor($W, $H);
        imagecopyresampled($final, $canvas, 0, 0, 0, 0, $W, $H, $cw, $ch);

        return $final;
    }

    private function bounds(\GdImage $im): array
    {
        $w = imagesx($im);
        $h = imagesy($im);
        $minX = $w;
        $minY = $h;
        $maxX = 0;
        $maxY = 0;
        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                if ((imagecolorat($im, $x, $y) >> 24 & 127) < 100) {
                    $minX = min($minX, $x);
                    $maxX = max($maxX, $x);
                    $minY = min($minY, $y);
                    $maxY = max($maxY, $y);
                }
            }
        }

        return [$minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1];
    }

    private function save(\GdImage $im, string $relative): void
    {
        imagejpeg($im, "{$this->out}/{$relative}", 86);
    }
}