<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/** The store's main product series: php artisan db:seed --class=SeriesCategorySeeder */
class SeriesCategorySeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            ['Endurance Series', 'Sustained energy, stamina and recovery for long-distance and high-volume training.'],
            ['Performance Series', 'Power, focus and strength support for peak output in every session.'],
            ['Muscle Builder & Testosterone Boosters', 'Support for lean muscle growth, strength and healthy testosterone levels.'],
            ['Apparel & Accessories', 'Shakers, straps, towels and gear for the gym.'],
        ];

        $slugs = array_map(fn ($s) => (string) str($s[0])->slug(), $series);

        // Push any other top-level categories (e.g. the demo ones) behind the series.
        Category::whereNull('parent_id')->whereNotIn('slug', $slugs)->where('sort_order', '<', 10)->increment('sort_order', 10);

        foreach ($series as $i => [$name, $description]) {
            Category::updateOrCreate(
                ['slug' => $slugs[$i]],
                ['name' => $name, 'description' => $description, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }
}
