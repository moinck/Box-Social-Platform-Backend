<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => "gemmalang1985@gmail.com"],
            [
                'first_name' => "Gemma",
                'last_name' => "Harvey",
                'fca_number' => 000000,
                'company_name' => "Test",
                'password' => Hash::make('Test@123'),
                'status' => 'active',
                'role' => 'customer',
                'is_verified' => true,
                'email_verified_at' => now(),
                'is_admin_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => "kausha@iihglobal.com"],
            [
                'first_name' => "IIH",
                'last_name' => "Global Test",
                'fca_number' => 000000,
                'company_name' => "Test",
                'password' => Hash::make('Test@123'),   
                'status' => 'active',
                'role' => 'customer',
                'is_verified' => true,
                'email_verified_at' => now(),
                'is_admin_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => "qa.iihtest@yopmail.com"],
            [
                'first_name' => "QA",
                'last_name' => "IIH Global",
                'fca_number' => 000000,
                'company_name' => "Test",
                'password' => Hash::make('Test@123'),   
                'status' => 'active',
                'role' => 'customer',
                'is_verified' => true,
                'email_verified_at' => now(),
                'is_admin_verified' => true,
            ]
        );
    }
}
