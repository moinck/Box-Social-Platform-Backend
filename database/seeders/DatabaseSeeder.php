<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Supper',
            'last_name' => 'Admin',
            'email' => 'admin.box@yopmail.com',
            'password' => Hash::make('adminbox123'),
            'status' => 'active',
            'role' => 'admin',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);


        // to add dummy data
        for ($i = 1; $i <= 15; $i++) {
            $name = fake()->firstName();
            $last_name = fake()->lastName();
            $company_name = $name . ' company';
            $email = strtolower($name . '.' . substr($last_name, 0, 2) . '@yopmail.com');

            User::create([
                'first_name' => $name,
                'last_name' => $last_name,
                'email' => $email,
                'fca_number' => fake()->randomNumber(5, true),
                'company_name' => $company_name,
                'password' => Hash::make('password'),
                'status' => 'active',
                'role' => 'customer',
            ]);
        }
    }
}
