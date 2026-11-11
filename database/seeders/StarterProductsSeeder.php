<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Starter capsule products, saved as inactive drafts with no price so nothing goes live by accident.
 * Fill in price, stock, supplement facts and directions in the admin, then switch "Active" on.
 *   php artisan db:seed --class=StarterProductsSeeder
 * Re-running refreshes names/categories/images but never touches a price, stock, description or active flag you changed.
 */
class StarterProductsSeeder extends Seeder
{
    public function run(): void
    {
        $img = DemoImages::available() ? new DemoImages : null;
        $brand = Brand::firstOrCreate(['slug' => 'powerath'], ['name' => 'Powerath']);

        $series = [
            'performance' => ['performance-series', 'Performance Series', ['body' => '#e3262e', 'cap' => '#151515']],
            'endurance' => ['endurance-series', 'Endurance Series', ['body' => '#1b6fd6', 'cap' => '#151515']],
            'muscle' => ['muscle-builder-testosterone-boosters', 'Muscle Builder & Testosterone Boosters', ['body' => '#2a2a2a', 'cap' => '#e3262e']],
        ];

        // [name, series, capsules, label title lines, sku]
        $products = [
            ['L-Arginine 120 Caps', 'performance', 120, ['L-ARGININE'], 'PWR-LARG-120'],
            ['HMB 1600 120 Caps', 'performance', 120, ['HMB', '1600'], 'PWR-HMB-120'],
            ['Pre-Workout Pump Complex 120 Caps', 'performance', 120, ['PUMP', 'COMPLEX'], 'PWR-PUMP-120'],
            ['TUDCA 60 Caps', 'muscle', 60, ['TUDCA'], 'PWR-TUDCA-60'],
            ['Tribulus 90 Caps', 'muscle', 90, ['TRIBULUS'], 'PWR-TRIB-90'],
            ['D-Aspartic Acid (DAA) 60 Caps', 'muscle', 60, ['D-ASPARTIC', 'ACID'], 'PWR-DAA-60'],
            ['Testosterone Support Complex 60 Caps', 'muscle', 60, ['T-SUPPORT', 'COMPLEX'], 'PWR-TSC-60'],
            ['Endurance Complex 150 Caps', 'endurance', 150, ['ENDURANCE', 'COMPLEX'], 'PWR-ENDU-150'],
            ['Adaptogen Complex 60 Caps', 'endurance', 60, ['ADAPTOGEN', 'COMPLEX'], 'PWR-ADAP-60'],
            ['Rhodiola Complex 60 Caps', 'endurance', 60, ['RHODIOLA', 'COMPLEX'], 'PWR-RHOD-60'],
        ];

        // sku => [regular price, sale price|null], from the reference store's listed amounts (applied only while a price is still empty)
        $prices = [
            'PWR-LARG-120' => [2500, null], 'PWR-HMB-120' => [2500, null], 'PWR-PUMP-120' => [4500, 3200],
            'PWR-TUDCA-60' => [2500, null], 'PWR-TRIB-90' => [3500, 2500], 'PWR-DAA-60' => [3400, 2500], 'PWR-TSC-60' => [4000, 2800],
            'PWR-ENDU-150' => [8000, 6000], 'PWR-ADAP-60' => [5500, 3800], 'PWR-RHOD-60' => [4000, 2400],
        ];

        foreach ($products as [$name, $key, $caps, $title, $sku]) {
            [$catSlug, $catName, $colors] = $series[$key];
            $category = Category::where('slug', $catSlug)->first();
            $slug = (string) str($name)->slug();

            $product = Product::firstOrNew(['slug' => $slug]);
            $isNew = ! $product->exists;

            $product->fill(['name' => $name, 'type' => 'simple', 'brand_id' => $brand->id, 'sku' => $sku]);
            if ($isNew) {
                $product->fill([
                    'stock_qty' => 0, 'is_active' => false,
                    'short_description' => "{$caps} capsules · {$catName}",
                    'description' => "<p>{$caps} capsules. Part of our {$catName}.</p><p><em>Supplement facts, directions and warnings to be added before this product goes live.</em></p>",
                ]);
            }
            if ($product->price === null) {
                [$product->price, $product->sale_price] = $prices[$sku];
            }
            if ($img && ($isNew || ! $product->image)) {
                [$product->image, $product->gallery] = $img->product($slug, 'bottle', $colors + ['title' => $title, 'sub' => "{$caps} CAPSULES"]);
            }
            $product->save();

            if ($category) {
                $product->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }
}
