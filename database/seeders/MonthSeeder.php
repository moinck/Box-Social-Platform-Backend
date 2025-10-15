<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = [
            ['month_number' => 1, 'month_name' => 'January'],
            ['month_number' => 2, 'month_name' => 'February'],
            ['month_number' => 3, 'month_name' => 'March'],
            ['month_number' => 4, 'month_name' => 'April'],
            ['month_number' => 5, 'month_name' => 'May'],
            ['month_number' => 6, 'month_name' => 'June'],
            ['month_number' => 7, 'month_name' => 'July'],
            ['month_number' => 8, 'month_name' => 'August'],
            ['month_number' => 9, 'month_name' => 'September'],
            ['month_number' => 10, 'month_name' => 'October'],
            ['month_number' => 11, 'month_name' => 'November'],
            ['month_number' => 12, 'month_name' => 'December'],
        ];
        DB::table('months')->insert($months);
    }
}
