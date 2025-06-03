<?php

namespace Database\Seeders;

use App\Models\DesignStyles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignStylesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designStyles = [
            "Minimalistic",
            "Beautiful",
            "Professional",
            "Big & Bold",
            "Typography",
            "Sharp"
        ];

        foreach ($designStyles as $designStyle) {
            DesignStyles::create([
                'name' => $designStyle,
            ]);
        }

        // to run this seeder use
        // php artisan db:seed --class=DesignStylesSeeder
    }
}
