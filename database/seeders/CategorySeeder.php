<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
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

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
