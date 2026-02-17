<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageDefinition;
use App\Models\PackageFeature;
use App\Models\PackageLimit;

class PaketSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Free Paket ----
        $free = PackageDefinition::create([
            'name'          => 'free',
            'display_name'  => 'Ücretsiz',
            'description'   => 'Temel özelliklerle başlayın',
            'monthly_price' => 0,
            'yearly_price'  => 0,
            'currency'      => 'TRY',
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        // ---- Silver Paket ----
        $silver = PackageDefinition::create([
            'name'          => 'silver',
            'display_name'  => 'Silver',
            'description'   => 'Profesyonel ağ kurma özellikleri',
            'monthly_price' => 149.99,
            'yearly_price'  => 1499.99,
            'currency'      => 'TRY',
            'is_active'     => true,
            'sort_order'    => 2,
        ]);

        // ---- Gold Paket ----
        $gold = PackageDefinition::create([
            'name'          => 'gold',
            'display_name'  => 'Gold',
            'description'   => 'Sınırsız erişim ve öncelikli eşleşme',
            'monthly_price' => 299.99,
            'yearly_price'  => 2999.99,
            'currency'      => 'TRY',
            'is_active'     => true,
            'sort_order'    => 3,
        ]);

        // ---- Paket Özellikleri ----
        $features = [
            // FREE
            ['package_id' => $free->id, 'feature_code' => 'ad_free',           'feature_name' => 'Reklamsız Kullanım',          'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 1],
            ['package_id' => $free->id, 'feature_code' => 'unlimited_message',  'feature_name' => 'Sınırsız Mesajlaşma',         'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 2],
            ['package_id' => $free->id, 'feature_code' => 'gallery_limit',      'feature_name' => 'Fotoğraf Galerisi',           'value' => '3',     'value_type' => 'number',  'sort_order' => 3],
            ['package_id' => $free->id, 'feature_code' => 'listing_limit',      'feature_name' => 'Aktif İlan',                  'value' => '1',     'value_type' => 'number',  'sort_order' => 4],
            ['package_id' => $free->id, 'feature_code' => 'advanced_search',    'feature_name' => 'Gelişmiş Arama ve Filtreleme','value' => 'false', 'value_type' => 'boolean', 'sort_order' => 5],
            ['package_id' => $free->id, 'feature_code' => 'basic_stats',        'feature_name' => 'Temel İstatistikler',         'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 6],
            ['package_id' => $free->id, 'feature_code' => 'favorite_list',      'feature_name' => 'Favori Listesi',              'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 7],
            ['package_id' => $free->id, 'feature_code' => 'multi_lang_showcase','feature_name' => 'Çoklu Dil Vitrini',           'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 8],
            ['package_id' => $free->id, 'feature_code' => 'online_status',      'feature_name' => 'Çevrimiçi Durum Görünümü',    'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 9],
            ['package_id' => $free->id, 'feature_code' => 'ai_priority',        'feature_name' => 'AI Önceliği',                 'value' => '0',     'value_type' => 'number',  'sort_order' => 10],
            ['package_id' => $free->id, 'feature_code' => 'verified_badge',     'feature_name' => 'Doğrulanmış Rozet',           'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 11],
            ['package_id' => $free->id, 'feature_code' => 'top_search',         'feature_name' => 'Aramalarda Üst Sıra',         'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 12],
            ['package_id' => $free->id, 'feature_code' => 'instant_translate',  'feature_name' => 'Anlık Mesaj Çevirisi',        'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 13],

            // SILVER
            ['package_id' => $silver->id, 'feature_code' => 'ad_free',           'feature_name' => 'Reklamsız Kullanım',          'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 1],
            ['package_id' => $silver->id, 'feature_code' => 'unlimited_message',  'feature_name' => 'Sınırsız Mesajlaşma',         'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 2],
            ['package_id' => $silver->id, 'feature_code' => 'gallery_limit',      'feature_name' => 'Fotoğraf Galerisi',           'value' => '10',    'value_type' => 'number',  'sort_order' => 3],
            ['package_id' => $silver->id, 'feature_code' => 'listing_limit',      'feature_name' => 'Aktif İlan',                  'value' => '10',    'value_type' => 'number',  'sort_order' => 4],
            ['package_id' => $silver->id, 'feature_code' => 'advanced_search',    'feature_name' => 'Gelişmiş Arama ve Filtreleme','value' => 'true',  'value_type' => 'boolean', 'sort_order' => 5],
            ['package_id' => $silver->id, 'feature_code' => 'basic_stats',        'feature_name' => 'Temel İstatistikler',         'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 6],
            ['package_id' => $silver->id, 'feature_code' => 'favorite_list',      'feature_name' => 'Favori Listesi',              'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 7],
            ['package_id' => $silver->id, 'feature_code' => 'multi_lang_showcase','feature_name' => 'Çoklu Dil Vitrini',           'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 8],
            ['package_id' => $silver->id, 'feature_code' => 'online_status',      'feature_name' => 'Çevrimiçi Durum Görünümü',    'value' => 'true',  'value_type' => 'boolean', 'sort_order' => 9],
            ['package_id' => $silver->id, 'feature_code' => 'ai_priority',        'feature_name' => 'AI Önceliği',                 'value' => '20',    'value_type' => 'number',  'sort_order' => 10],
            ['package_id' => $silver->id, 'feature_code' => 'verified_badge',     'feature_name' => 'Doğrulanmış Rozet',           'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 11],
            ['package_id' => $silver->id, 'feature_code' => 'top_search',         'feature_name' => 'Aramalarda Üst Sıra',         'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 12],
            ['package_id' => $silver->id, 'feature_code' => 'instant_translate',  'feature_name' => 'Anlık Mesaj Çevirisi',        'value' => 'false', 'value_type' => 'boolean', 'sort_order' => 13],

            // GOLD
            ['package_id' => $gold->id, 'feature_code' => 'ad_free',           'feature_name' => 'Reklamsız Kullanım',          'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 1],
            ['package_id' => $gold->id, 'feature_code' => 'unlimited_message',  'feature_name' => 'Sınırsız Mesajlaşma',         'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 2],
            ['package_id' => $gold->id, 'feature_code' => 'gallery_limit',      'feature_name' => 'Fotoğraf Galerisi',           'value' => '50',        'value_type' => 'number',  'sort_order' => 3],
            ['package_id' => $gold->id, 'feature_code' => 'listing_limit',      'feature_name' => 'Aktif İlan',                  'value' => 'unlimited', 'value_type' => 'text',    'sort_order' => 4],
            ['package_id' => $gold->id, 'feature_code' => 'advanced_search',    'feature_name' => 'Gelişmiş Arama ve Filtreleme','value' => 'true',      'value_type' => 'boolean', 'sort_order' => 5],
            ['package_id' => $gold->id, 'feature_code' => 'basic_stats',        'feature_name' => 'Temel İstatistikler',         'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 6],
            ['package_id' => $gold->id, 'feature_code' => 'favorite_list',      'feature_name' => 'Favori Listesi',              'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 7],
            ['package_id' => $gold->id, 'feature_code' => 'multi_lang_showcase','feature_name' => 'Çoklu Dil Vitrini',           'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 8],
            ['package_id' => $gold->id, 'feature_code' => 'online_status',      'feature_name' => 'Çevrimiçi Durum Görünümü',    'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 9],
            ['package_id' => $gold->id, 'feature_code' => 'ai_priority',        'feature_name' => 'AI Önceliği',                 'value' => '100',       'value_type' => 'number',  'sort_order' => 10],
            ['package_id' => $gold->id, 'feature_code' => 'verified_badge',     'feature_name' => 'LORD Verified Gold Badge',    'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 11],
            ['package_id' => $gold->id, 'feature_code' => 'top_search',         'feature_name' => 'Aramalarda Üst Sıra',         'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 12],
            ['package_id' => $gold->id, 'feature_code' => 'instant_translate',  'feature_name' => 'Anlık Mesaj Çevirisi',        'value' => 'true',      'value_type' => 'boolean', 'sort_order' => 13],
        ];

        foreach ($features as $feature) {
            PackageFeature::create($feature);
        }

        // ---- Paket Limitleri ----
        $limits = [
            // FREE
            ['package_id' => $free->id, 'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',     'limit_value' => 5,  'period' => 'daily'],
            ['package_id' => $free->id, 'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',    'limit_value' => 10, 'period' => 'daily'],
            ['package_id' => $free->id, 'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni','limit_value' => 1, 'period' => 'daily'],

            // SILVER
            ['package_id' => $silver->id, 'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',     'limit_value' => -1, 'period' => 'daily'],
            ['package_id' => $silver->id, 'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',    'limit_value' => 50, 'period' => 'daily'],
            ['package_id' => $silver->id, 'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni','limit_value' => 5, 'period' => 'daily'],

            // GOLD
            ['package_id' => $gold->id, 'limit_code' => 'daily_message',    'limit_name' => 'Günlük Mesaj',     'limit_value' => -1, 'period' => 'daily'],
            ['package_id' => $gold->id, 'limit_code' => 'daily_like',       'limit_name' => 'Günlük Beğeni',    'limit_value' => -1, 'period' => 'daily'],
            ['package_id' => $gold->id, 'limit_code' => 'daily_super_like', 'limit_name' => 'Günlük Süper Beğeni','limit_value' => -1, 'period' => 'daily'],
        ];

        foreach ($limits as $limit) {
            PackageLimit::create($limit);
        }
    }
}
