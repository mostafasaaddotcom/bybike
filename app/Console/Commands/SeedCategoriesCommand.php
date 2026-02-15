<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class SeedCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the default product categories';

    /**
     * The default categories to seed.
     *
     * @var array<int, array{name: string, slug: string, sort_order: int}>
     */
    protected array $categories = [
        ['name' => 'American', 'slug' => 'american', 'sort_order' => 1],
        ['name' => 'Mexican', 'slug' => 'mexican', 'sort_order' => 2],
        ['name' => 'BBQ', 'slug' => 'bbq', 'sort_order' => 3],
        ['name' => 'Oriental', 'slug' => 'oriental', 'sort_order' => 4],
        ['name' => 'Shawarma', 'slug' => 'shawarma', 'sort_order' => 5],
        ['name' => 'Happy Items', 'slug' => 'happy-items', 'sort_order' => 6],
        ['name' => 'Happy Around', 'slug' => 'happy-around', 'sort_order' => 7],
        ['name' => 'Pasta Station', 'slug' => 'pasta-station', 'sort_order' => 8],
        ['name' => 'Dessert', 'slug' => 'dessert', 'sort_order' => 9],
        ['name' => 'Customizations', 'slug' => 'customizations', 'sort_order' => 10],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->categories as $category) {
            if (Category::where('slug', $category['slug'])->exists()) {
                $skipped++;

                continue;
            }

            Category::create($category);
            $created++;
        }

        $this->info("Categories seeded: {$created} created, {$skipped} skipped (already exist).");

        return self::SUCCESS;
    }
}
