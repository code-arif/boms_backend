<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Plain Paratha',    'price' => 15, 'category' => 'Bread'],
            ['name' => 'Egg Paratha',      'price' => 25, 'category' => 'Bread'],
            ['name' => 'Boiled Egg (x2)', 'price' => 20, 'category' => 'Protein'],
            ['name' => 'Omelette',         'price' => 30, 'category' => 'Protein'],
            ['name' => 'Halwa Puri',       'price' => 40, 'category' => 'Set'],
            ['name' => 'Tea (Cup)',        'price' => 10, 'category' => 'Drinks'],
            ['name' => 'Milk (Glass)',     'price' => 15, 'category' => 'Drinks'],
            ['name' => 'Orange Juice',    'price' => 35, 'category' => 'Drinks'],
        ];

        // Seed for the first company (company_id = 1)
        foreach ($items as $item) {
            MenuItem::create([
                ...$item,
                'company_id'   => 1,
                'is_available' => true,
            ]);
        }
    }
}
