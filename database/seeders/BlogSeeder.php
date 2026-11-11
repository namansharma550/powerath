<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

/** Starter posts: php artisan db:seed --class=BlogSeeder (safe to re-run; edit or delete them in the admin). */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['Welcome to Powerath: Fuel for Every Athlete', 'Why we started Powerath and what you can expect from our range.',
                '<p>Powerath is short for <strong>Power Athlete</strong> — and that is who we build for. Whether you are running long distances, chasing a personal best in the gym or putting on lean muscle, the right nutrition makes the work count.</p><h2>What we stand for</h2><ul><li>Clean, honest formulas</li><li>Products made for how athletes actually train</li><li>Fast delivery and real support</li></ul><p>Explore our series and find the formula that fits your goals.</p>', 1],
            ['How to Choose the Right Supplement for Your Goal', 'A quick guide to matching supplements to endurance, performance and muscle-building goals.',
                '<p>Not every supplement is right for every athlete. Start with your goal, then build up.</p><h2>Endurance</h2><p>Focus on sustained energy, hydration and recovery between sessions.</p><h2>Performance</h2><p>Look for support around power, focus and consistent output in training.</p><h2>Muscle building</h2><p>Pair a protein-rich diet with formulas that support growth and strength.</p><p>Always check labels and talk to a professional if you have health conditions or compete under anti-doping rules.</p>', 8],
            ['Recovery Basics: Getting More From Every Session', 'Sleep, food, hydration and smart supplementation: the simple recovery habits that pay off.',
                '<p>Progress happens during recovery, not just in the session. Four habits make the biggest difference:</p><ol><li><strong>Sleep</strong> — aim for 7–9 hours.</li><li><strong>Protein</strong> — spread it across the day.</li><li><strong>Hydration</strong> — replace what you sweat.</li><li><strong>Rest days</strong> — plan them, do not skip them.</li></ol>', 15],
        ];

        foreach ($posts as [$title, $excerpt, $body, $daysAgo]) {
            Post::firstOrCreate(['slug' => str($title)->slug()], [
                'title' => $title, 'excerpt' => $excerpt, 'body' => $body,
                'is_published' => true, 'published_at' => now()->subDays($daysAgo),
            ]);
        }
    }
}
