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
    ]);
  }
}
