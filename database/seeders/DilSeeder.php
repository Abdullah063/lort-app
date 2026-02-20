<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportedLanguage;

class DilSeeder extends Seeder
{
    public function run(): void
    {
        SupportedLanguage::create(['code' => 'tr', 'name' => 'Türkçe',    'is_active' => true,  'is_default' => true]);
        SupportedLanguage::create(['code' => 'en', 'name' => 'English',   'is_active' => true,  'is_default' => false]);
        SupportedLanguage::create(['code' => 'ar', 'name' => 'العربية',   'is_active' => true,  'is_default' => false]);
       
    }
}