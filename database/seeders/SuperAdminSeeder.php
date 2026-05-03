<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::withoutCompanyScope()->updateOrCreate(
            ['email' => 'superadmin@boms.com'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('SuperSecret123!'),
                'role'       => 'super_admin',
                'company_id' => null,
                'is_active'  => true,
            ]
        );
    }
}
