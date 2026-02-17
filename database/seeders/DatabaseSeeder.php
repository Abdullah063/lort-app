<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // SIRA ÖNEMLİ! Bağımlılıklara göre çalıştır
        $this->call([
            RolVeIzinSeeder::class,          // 1. Roller ve izinler
            PaketSeeder::class,              // 2. Paket tanımları, özellikler, limitler
            DilSeeder::class,                // 3. Desteklenen diller
            HedefVeIlgiAlaniSeeder::class,   // 4. Hedefler ve ilgi alanları
            BildirimSablonuSeeder::class,    // 5. Bildirim şablonları
            SuperAdminSeeder::class,         // 6. İlk super admin 
            TestVerisiSeeder::class,         // 7. Test kullanıcıları (en son!)
        ]);
    }
}