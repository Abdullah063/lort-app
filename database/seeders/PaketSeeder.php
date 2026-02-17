<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageDefinition;
use App\Models\PackageLimit;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // PAKETLER
        // =============================================
        $free = PackageDefinition::create([
            'name'           => 'free',
            'display_name'   => 'Ücretsiz',
            'description'    => 'Başlangıç paketi',
            'monthly_price'  => 0,
            'yearly_price'   => 0,
            'currency'       => 'TRY',
            'is_active'      => true,
            'sort_order'     => 1,
        ]);

        $silver = PackageDefinition::create([
            'name'           => 'silver',
            'display_name'   => 'Silver',
            'description'    => 'Orta seviye paket',
            'monthly_price'  => 99.99,
            'yearly_price'   => 899.99,
            'currency'       => 'TRY',
            'is_active'      => true,
            'sort_order'     => 2,
        ]);

        $gold = PackageDefinition::create([
            'name'           => 'gold',
            'display_name'   => 'Gold',
            'description'    => 'Premium paket',
            'monthly_price'  => 199.99,
            'yearly_price'   => 1799.99,
            'currency'       => 'TRY',
            'is_active'      => true,
            'sort_order'     => 3,
        ]);

        // =============================================
        // LİMİTLER
        // -1 = sınırsız, 0 = kapalı
        // =============================================
        $limits = [
            // Günlük Like
            ['package_id' => $free->id,   'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',        'limit_value' => 10,  'period' => 'daily'],
            ['package_id' => $silver->id, 'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',        'limit_value' => 50,  'period' => 'daily'],
            ['package_id' => $gold->id,   'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',        'limit_value' => -1,  'period' => 'daily'],

            // Günlük Super Like
            ['package_id' => $free->id,   'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni',  'limit_value' => 1,   'period' => 'daily'],
            ['package_id' => $silver->id, 'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni',  'limit_value' => 5,   'period' => 'daily'],
            ['package_id' => $gold->id,   'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni',  'limit_value' => -1,  'period' => 'daily'],

            // Fotoğraf Galerisi
            ['package_id' => $free->id,   'limit_code' => 'gallery_limit',    'limit_name' => 'Fotoğraf Limiti',      'limit_value' => 3,   'period' => 'total'],
            ['package_id' => $silver->id, 'limit_code' => 'gallery_limit',    'limit_name' => 'Fotoğraf Limiti',      'limit_value' => 10,  'period' => 'total'],
            ['package_id' => $gold->id,   'limit_code' => 'gallery_limit',    'limit_name' => 'Fotoğraf Limiti',      'limit_value' => 50,  'period' => 'total'],

            // İlan Limiti
            ['package_id' => $free->id,   'limit_code' => 'listing_limit',    'limit_name' => 'İlan Limiti',          'limit_value' => 1,   'period' => 'total'],
            ['package_id' => $silver->id, 'limit_code' => 'listing_limit',    'limit_name' => 'İlan Limiti',          'limit_value' => 10,  'period' => 'total'],
            ['package_id' => $gold->id,   'limit_code' => 'listing_limit',    'limit_name' => 'İlan Limiti',          'limit_value' => -1,  'period' => 'total'],

            // Beni beğenenleri görme
            ['package_id' => $free->id,   'limit_code' => 'see_who_liked',    'limit_name' => 'Beğenenleri Görme',    'limit_value' => 0,   'period' => 'total'],
            ['package_id' => $silver->id, 'limit_code' => 'see_who_liked',    'limit_name' => 'Beğenenleri Görme',    'limit_value' => -1,  'period' => 'total'],
            ['package_id' => $gold->id,   'limit_code' => 'see_who_liked',    'limit_name' => 'Beğenenleri Görme',    'limit_value' => -1,  'period' => 'total'],

            // Günlük Mesaj
            ['package_id' => $free->id,   'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',         'limit_value' => 20,  'period' => 'daily'],
            ['package_id' => $silver->id, 'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',         'limit_value' => 100, 'period' => 'daily'],
            ['package_id' => $gold->id,   'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',         'limit_value' => -1,  'period' => 'daily'],
        ];

        foreach ($limits as $limit) {
            PackageLimit::create(array_merge($limit, ['is_active' => true]));
        }
    }
}