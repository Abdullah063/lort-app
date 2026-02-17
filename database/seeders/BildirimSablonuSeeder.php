<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class BildirimSablonuSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Hoşgeldin
            ['template_code' => 'welcome', 'language_code' => 'tr', 'title' => 'Hoş Geldiniz!',  'content' => 'Merhaba {{name}}, LORD uygulamasına hoş geldiniz! Hemen profilinizi tamamlayın ve iş bağlantıları kurmaya başlayın.'],
            ['template_code' => 'welcome', 'language_code' => 'en', 'title' => 'Welcome!',        'content' => 'Hello {{name}}, welcome to LORD! Complete your profile and start making business connections.'],
            ['template_code' => 'welcome', 'language_code' => 'fr', 'title' => 'Bienvenue!',      'content' => 'Bonjour {{name}}, bienvenue sur LORD! Complétez votre profil et commencez à créer des connexions.'],

            // Paket Kutlama
            ['template_code' => 'package_congrats', 'language_code' => 'tr', 'title' => 'Paket Aktif!',          'content' => 'Tebrikler {{name}}! {{package_name}} paketiniz aktif edildi. Yeni özelliklerinizi keşfedin.'],
            ['template_code' => 'package_congrats', 'language_code' => 'en', 'title' => 'Package Activated!',    'content' => 'Congratulations {{name}}! Your {{package_name}} plan is now active. Explore your new features.'],
            ['template_code' => 'package_congrats', 'language_code' => 'fr', 'title' => 'Forfait Activé!',       'content' => 'Félicitations {{name}}! Votre forfait {{package_name}} est activé. Découvrez vos nouvelles fonctionnalités.'],

            // Dekont
            ['template_code' => 'receipt', 'language_code' => 'tr', 'title' => 'Ödeme Onayı',     'content' => '{{name}}, {{amount}} {{currency}} tutarında ödemeniz başarıyla alınmıştır. Dekont bilgileriniz ekte yer almaktadır.'],
            ['template_code' => 'receipt', 'language_code' => 'en', 'title' => 'Payment Confirmed','content' => '{{name}}, your payment of {{amount}} {{currency}} has been received. Your receipt is attached.'],
            ['template_code' => 'receipt', 'language_code' => 'fr', 'title' => 'Paiement Confirmé','content' => '{{name}}, votre paiement de {{amount}} {{currency}} a été reçu. Votre reçu est en pièce jointe.'],

            // Eşleşme
            ['template_code' => 'match', 'language_code' => 'tr', 'title' => 'Yeni Eşleşme!',    'content' => '{{name}}, yeni bir iş bağlantınız var! {{matched_name}} ile eşleştiniz. Hemen sohbete başlayın.'],
            ['template_code' => 'match', 'language_code' => 'en', 'title' => 'New Match!',        'content' => '{{name}}, you have a new connection! You matched with {{matched_name}}. Start chatting now.'],
            ['template_code' => 'match', 'language_code' => 'fr', 'title' => 'Nouveau Match!',    'content' => '{{name}}, vous avez une nouvelle connexion! Vous êtes connecté avec {{matched_name}}. Commencez à discuter.'],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::create($template);
        }
    }
}