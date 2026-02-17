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
            'Ürün Satmak',
            'Hizmet Satmak',
            'Yatırımcı Bulmak',
            'İş Ortağı Bulmak',
            'İhracat Yapmak',
            'İthalat Yapmak',
            'Distribütör Bulmak',
            'Franchise Vermek',
            'Tedarikçi Bulmak',
            'Network Genişletmek',
        ];

        foreach ($goals as $goal) {
            Goal::create(['name' => $goal]);
        }

        // ---- İlgi Alanları ----
        $interests = [
            'Ticaret Odaları',
            'Gastronomi',
            'Teknoloji',
            'Finans',
            'Sağlık',
            'Eğitim',
            'Turizm',
            'İnşaat',
            'Tekstil',
            'Enerji',
            'Lojistik',
            'Tarım',
            'E-Ticaret',
            'Yazılım',
            'Danışmanlık',
        ];

        foreach ($interests as $interest) {
            Interest::create(['name' => $interest]);
        }
    }
}