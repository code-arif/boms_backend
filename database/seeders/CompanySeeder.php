<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['id' => 1],
            [
                'name'   => 'Default Company',
                'slug'   => Str::slug('Default Company'),
                'status' => 'active',
                'plan'   => 'free',
            ]
        );
    }
}
