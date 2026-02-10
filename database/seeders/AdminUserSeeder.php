<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (role 1)
        User::updateOrCreate(
            ['name' => 'adminchelas'],
            [
                'email' => 'adminchelas@local.test',
                'password' => Hash::make('chelasla66admin'),
                'role' => 1,
            ]
        );

        // Vendedor (role 2)
        User::updateOrCreate(
            ['name' => 'vendedor'],
            [
                'email' => 'vendedor@local.test',
                'password' => Hash::make('vendedor1'),
                'role' => 2,
            ]
        );
    }
}
