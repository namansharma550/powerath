<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sample catalog with generated images, safe to re-run:
 *   php artisan db:seed --class=DemoCatalogSeeder
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $img = DemoImages::available() ? new DemoImages : null;
        if (! $img) {
            $this->command?->warn('GD/FreeType fonts not found: seeding without images.');
        }

        $cats = [];
        foreach (['Protein', 'Creatine', 'Pre-workout', 'Recovery', 'Gym Accessories'] as $i => $name) {
            $cats[$name] = Category::updateOrCreate(['slug' => str($name)->slug()], ['name' => $name, 'sort_order' => $i, 'is_active' => true]);
        }

        $brands = [];
        foreach (['Powerath', 'IronCore', 'VoltLabs'] as $name) {
            $brands[$name] = Brand::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
        }

        $flavour = Attribute::firstOrCreate(['slug' => 'flavour'], ['name' => 'Flavour', 'sort_order' => 1]);
        $size = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size', 'type' => 'button', 'sort_order' => 2]);
        $val = fn (Attribute $a, string $v) => AttributeValue::firstOrCreate(['attribute_id' => $a->id, 'slug' => str($v)->slug()], ['value' => $v]);
        $choc = $val($flavour, 'Chocolate');
        $van = $val($flavour, 'Vanilla');
        $straw = $val($flavour, 'Strawberry');
        $water = $val($flavour, 'Watermelon');
        $lemon = $val($flavour, 'Lemon');
        $kg1 = $val($size, '1 kg');
        $kg2 = $val($size, '2 kg');

        $tub = fn (array $o) => ['kind' => 'tub', 'o' => $o];

        // key => [name, category, brand, price, sale, stock, featured, short, description, render, extra]
        $simple = [
            'creatine-monohydrate-250g' => ['Creatine Monohydrate 250g', 'Creatine', 'IronCore', 899, 799, 40, true,
                '100% pure micronised creatine, 83 servings.', '<p>Micronised creatine monohydrate for strength, power and recovery. 3g per serving, unflavoured, mixes easily.</p>',
                $tub(['body' => '#f2f1ee', 'label' => '#f2f1ee', 'text' => '#151515', 'lid' => '#151515', 'title' => ['CREATINE', 'MONOHYDRATE'], 'sub' => 'MICRONISED · 250G', 'tag' => '83 SERVINGS', 'scale' => 0.8])],
            'pre-workout-ignite' => ['Pre-Workout Ignite', 'Pre-workout', 'VoltLabs', 1299, null, 30, true,
                'Clean energy and focus, 30 servings.', '<p>Caffeine, citrulline and beta-alanine for pumps and focus without the crash. Take one scoop 20 minutes before training.</p>',
                $tub(['body' => '#e3262e', 'label' => '#151515', 'accent' => '#ff8a00', 'lid' => '#151515', 'title' => ['PRE-WORKOUT', 'IGNITE'], 'sub' => 'FRUIT PUNCH · 30 SERVINGS', 'tag' => 'EXPLOSIVE ENERGY', 'scale' => 0.85])],
            'bcaa-recovery-300g' => ['BCAA Recovery 300g', 'Recovery', 'Powerath', 999, 899, 35, false,
                '2:1:1 BCAAs with electrolytes.', '<p>Intra-workout BCAAs plus electrolytes to keep you hydrated and cut down muscle soreness.</p>',
                $tub(['body' => '#1b6fd6', 'label' => '#0e2a52', 'accent' => '#2ee6a6', 'lid' => '#151515', 'title' => ['BCAA', 'RECOVERY'], 'sub' => '2:1:1 · 300G', 'tag' => 'ZERO SUGAR', 'scale' => 0.78])],
            'daily-multivitamin' => ['Daily Multivitamin', 'Recovery', 'Powerath', 599, null, 60, false,
                'Vitamins and minerals for active people, 60 tablets.', '<p>A complete daily multivitamin to cover micronutrient gaps when you train hard.</p>',
                ['kind' => 'bottle', 'o' => ['title' => ['DAILY', 'MULTIVITAMIN'], 'sub' => '60 TABLETS']]],
            'shaker-bottle-700ml' => ['Shaker Bottle 700ml', 'Gym Accessories', 'Powerath', 299, 249, 100, true,
                'Leak-proof shaker with mixing ball.', '<p>BPA-free 700ml shaker with a stainless mixing ball, flip cap and measurement marks.</p>',
                ['kind' => 'shaker', 'o' => []]],
            'lifting-straps' => ['Lifting Straps', 'Gym Accessories', 'IronCore', 499, null, 80, false,
                'Heavy-duty cotton straps for deadlifts and pulls.', '<p>Padded, stitched cotton lifting straps for a secure grip on heavy pulls.</p>',
                ['kind' => 'straps', 'o' => []]],
            'gym-towel' => ['Gym Towel', 'Gym Accessories', 'Powerath', 349, null, 70, false,
                'Quick-dry microfibre towel.', '<p>Soft, absorbent and quick-drying towel that fits in your gym bag.</p>',
                ['kind' => 'towel', 'o' => []]],
        ];

        foreach ($simple as $slug => [$name, $cat, $brand, $price, $sale, $stock, $featured, $short, $desc, $render]) {
            $fields = ['name' => $name, 'type' => 'simple', 'price' => $price, 'sale_price' => $sale, 'stock_qty' => $stock,
                'brand_id' => $brands[$brand]->id, 'is_featured' => $featured, 'short_description' => $short, 'description' => $desc,
                'sku' => strtoupper(substr(str_replace('-', '', $slug), 0, 3)).'-'.str_pad((string) crc32($slug) % 1000, 3, '0', STR_PAD_LEFT)];
            if ($img) {
                [$fields['image'], $fields['gallery']] = $img->product($slug, $render['kind'], $render['o']);
            }
            $p = Product::updateOrCreate(['slug' => $slug], $fields);
            $p->categories()->sync([$cats[$cat]->id]);
        }

        // Variable: whey isolate, flavour x size
        $whey = $this->variable('whey-protein-isolate', [
            'name' => 'Whey Protein Isolate', 'brand_id' => $brands['Powerath']->id, 'is_featured' => true,
            'short_description' => '25g protein per scoop, low lactose, fast absorbing.',
            'description' => '<p>Cold-filtered whey isolate for post-workout recovery. 25g protein, under 1g sugar per serving.</p><ul><li>Low lactose</li><li>Mixes instantly</li><li>Third-party tested</li></ul>',
        ], $img, $tub(['body' => '#151515', 'label' => '#151515', 'accent' => '#e3262e', 'lid' => '#e3262e', 'title' => ['WHEY', 'ISOLATE'], 'sub' => '25G PROTEIN', 'tag' => 'CHOCOLATE', 'scale' => 0.95]));
        $whey->categories()->sync([$cats['Protein']->id]);
        $whey->attributeValues()->sync([$choc->id, $van->id, $kg1->id, $kg2->id]);
        $wheyTheme = [$choc->slug => ['#5a3320', 'CHOCOLATE'], $van->slug => ['#d9b84a', 'VANILLA']];
        foreach ([[$choc, $kg1, 2499], [$choc, $kg2, 4499], [$van, $kg1, 2499], [$van, $kg2, 4499]] as [$f, $s, $price]) {
            $sku = "WPI-{$f->slug}-{$s->slug}";
            $big = $s->id === $kg2->id;
            $image = $img?->variantImage($sku, 'tub', ['body' => '#151515', 'label' => '#151515', 'accent' => $wheyTheme[$f->slug][0], 'lid' => $wheyTheme[$f->slug][0],
                'title' => ['WHEY', 'ISOLATE'], 'sub' => strtoupper($s->value).' · 25G PROTEIN', 'tag' => $wheyTheme[$f->slug][1], 'scale' => $big ? 1.0 : 0.82]);
            $variant = $whey->variants()->updateOrCreate(['sku' => $sku], ['price' => $price, 'sale_price' => $big ? $price - 300 : null, 'stock_qty' => 25, 'image' => $image, 'is_active' => true]);
            $variant->attributeValues()->sync([$f->id, $s->id]);
        }

        // Variable: mass gainer, flavour only
        $gainer = $this->variable('mass-gainer-3kg', [
            'name' => 'Mass Gainer 3kg', 'brand_id' => $brands['IronCore']->id,
            'short_description' => '1,250 calories and 50g protein per serving.',
            'description' => '<p>High-calorie mass gainer with complex carbs and 50g protein for lean bulking.</p>',
        ], $img, $tub(['body' => '#e3262e', 'label' => '#151515', 'accent' => '#e3262e', 'lid' => '#151515', 'title' => ['MASS', 'GAINER'], 'sub' => '3 KG · 50G PROTEIN', 'tag' => 'CHOCOLATE', 'scale' => 1.0]));
        $gainer->categories()->sync([$cats['Protein']->id]);
        $gainer->attributeValues()->sync([$choc->id, $straw->id]);
        foreach ([[$choc, '#5a3320', 'CHOCOLATE'], [$straw, '#e0457b', 'STRAWBERRY']] as [$f, $hex, $label]) {
            $sku = "MG-{$f->slug}";
            $image = $img?->variantImage($sku, 'tub', ['body' => '#e3262e', 'label' => '#151515', 'accent' => $hex, 'lid' => '#151515',
                'title' => ['MASS', 'GAINER'], 'sub' => '3 KG · 50G PROTEIN', 'tag' => $label, 'scale' => 1.0]);
            $gainer->variants()->updateOrCreate(['sku' => $sku], ['price' => 3299, 'stock_qty' => 15, 'image' => $image, 'is_active' => true])
                ->attributeValues()->sync([$f->id]);
        }

        // Simple product that also appears in flavour filters
        Product::where('slug', 'bcaa-recovery-300g')->first()?->attributeValues()->sync([$water->id, $lemon->id]);

        if ($img) {
            foreach ([
                'protein' => ['tub', ['body' => '#151515', 'accent' => '#e3262e', 'lid' => '#e3262e', 'title' => ['WHEY', 'ISOLATE'], 'sub' => '25G PROTEIN', 'tag' => 'CHOCOLATE']],
                'creatine' => ['tub', ['body' => '#f2f1ee', 'label' => '#f2f1ee', 'text' => '#151515', 'lid' => '#151515', 'title' => ['CREATINE', 'MONOHYDRATE'], 'sub' => 'MICRONISED', 'tag' => '83 SERVINGS', 'scale' => 0.8]],
                'pre-workout' => ['tub', ['body' => '#e3262e', 'label' => '#151515', 'accent' => '#ff8a00', 'lid' => '#151515', 'title' => ['PRE-WORKOUT', 'IGNITE'], 'sub' => 'FRUIT PUNCH', 'tag' => 'EXPLOSIVE ENERGY', 'scale' => 0.85]],
                'recovery' => ['tub', ['body' => '#1b6fd6', 'label' => '#0e2a52', 'accent' => '#2ee6a6', 'lid' => '#151515', 'title' => ['BCAA', 'RECOVERY'], 'sub' => '2:1:1', 'tag' => 'ZERO SUGAR', 'scale' => 0.78]],
                'gym-accessories' => ['shaker', []],
            ] as $slug => [$kind, $o]) {
                Category::where('slug', $slug)->update(['image' => $img->category($slug, $kind, $o)]);
            }
        }

        Coupon::firstOrCreate(['code' => 'WELCOME10'], ['description' => '10% off', 'type' => 'percent', 'value' => 10, 'min_subtotal' => 500]);
    }

    private function variable(string $slug, array $fields, ?DemoImages $img, array $render): Product
    {
        $fields['type'] = 'variable';
        if ($img) {
            [$fields['image'], $fields['gallery']] = $img->product($slug, $render['kind'], $render['o']);
        }

        return Product::updateOrCreate(['slug' => $slug], $fields);
    }
}
