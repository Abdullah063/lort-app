<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goal;
use App\Models\Interest;

class HedefVeIlgiAlaniSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Hedefler ----
        $goals = [
            [
                'name'        => 'Ürün Satmak',
                'description' => 'Ürünlerinizi Uluslararası pazarlara tanıtın',
            ],
            [
                'name'        => 'Hizmet Satmak',
                'description' => 'Hizmetinizi Dünya çapında sunun',
            ],
            [
                'name'        => 'Network Genişletmek',
                'description' => 'Ticari ilişkiler kurun ve ağınızı genişletin',
            ],
            [
                'name'        => 'E-Ticaret Kurmak',
                'description' => 'Online satış altyapınızı oluşturun',
            ],
            [
                'name'        => 'Bayilik Kurmak',
                'description' => 'Bayilik ağınızı genişletin',
            ],
            [
                'name'        => 'Franchise Almak',
                'description' => 'Başarılı markaların franchise\'ını edinin',
            ],
            [
                'name'        => 'Marka Bilinirliği',
                'description' => 'Markanızı daha geniş kitlelere tanıtın',
            ],
            [
                'name'        => 'Lojistik İmkanları',
                'description' => 'Lojistik fırsatlarını keşfedin ve değerlendirin',
            ],
            [
                'name'        => 'Mamul Yarı Mamul Hammadde Temini',
                'description' => 'Hammadde, yarı mamul ve mamul ürün tedariki sağlayın',
            ],
        ];

        foreach ($goals as $goal) {
            Goal::create($goal);
        }

        // ---- İlgi Alanları ----
        $interests = [
            'Ekonomi Grupları',
            'Ekonomik Topluluklar',
            'Eğitim Dernekleri',
            'Gastronomi',
            'Müzeler',
            'Müzik',
            'Seyahat / Gezi',
            'Sosyal Kurumlar',
            'Spor',
            'Ticaret Odaları',
            'Yardım Dernekleri',
            'İnsan ve Kültürel Servisler',
        ];

        foreach ($interests as $interest) {
            Interest::create(['name' => $interest]);
        }
    }
}