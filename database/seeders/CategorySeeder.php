<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'individual', 'name' => 'Individual', 'sort_order' => 1],
            ['code' => 'corporate',  'name' => 'Corporate',  'sort_order' => 2],
            ['code' => 'other',      'name' => 'Other',      'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}