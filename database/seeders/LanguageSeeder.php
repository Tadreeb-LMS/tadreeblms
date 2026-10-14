<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;


class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Language::create([
            'name' => 'English',
            'code' => 'en',
            'is_enabled' => true,
            'is_default' => true,
        ]);

        Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'is_enabled' => true,
            'is_default' => false,
        ]);
    }
}