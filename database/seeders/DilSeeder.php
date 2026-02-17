<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportedLanguage;

class DilSeeder extends Seeder
{
    public function run(): void
    {
        SupportedLanguage::create(['code' => 'tr', 'name' => 'Türkçe',    'is_active' => true, 'is_default' => true]);
        SupportedLanguage::create(['code' => 'en', 'name' => 'English',   'is_active' => true, 'is_default' => false]);
        SupportedLanguage::create(['code' => 'fr', 'name' => 'Français',  'is_active' => true, 'is_default' => false]);
        SupportedLanguage::create(['code' => 'de', 'name' => 'Deutsch',   'is_active' => false, 'is_default' => false]);
    }
}