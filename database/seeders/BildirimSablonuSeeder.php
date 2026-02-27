<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class BildirimSablonuSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Like
            ['template_code' => 'like', 'language_code' => 'tr', 'title' => 'Yeni Beğeni!', 'content' => '{{liker_name}} seni beğendi!'],
            ['template_code' => 'like', 'language_code' => 'en', 'title' => 'New Like!', 'content' => '{{liker_name}} liked you!'],
            ['template_code' => 'like', 'language_code' => 'ar', 'title' => 'إعجاب جديد!', 'content' => '{{liker_name}} أعجب بك!'],

            // Super Like
            ['template_code' => 'super_like', 'language_code' => 'tr', 'title' => 'Süper Beğeni!', 'content' => '{{liker_name}} seni süper beğendi!'],
            ['template_code' => 'super_like', 'language_code' => 'en', 'title' => 'Super Like!', 'content' => '{{liker_name}} super liked you!'],
            ['template_code' => 'super_like', 'language_code' => 'ar', 'title' => 'إعجاب خارق!', 'content' => '{{liker_name}} أعجب بك بشدة!'],

            // Message
            ['template_code' => 'message', 'language_code' => 'tr', 'title' => 'Yeni Mesaj', 'content' => '{{sender_name}} sana bir mesaj gönderdi'],
            ['template_code' => 'message', 'language_code' => 'en', 'title' => 'New Message', 'content' => '{{sender_name}} sent you a message'],
            ['template_code' => 'message', 'language_code' => 'ar', 'title' => 'رسالة جديدة', 'content' => '{{sender_name}} أرسل لك رسالة'],

            // Match
            ['template_code' => 'match', 'language_code' => 'tr', 'title' => 'Yeni Eşleşme!', 'content' => '{{matched_name}} ile eşleştiniz!'],
            ['template_code' => 'match', 'language_code' => 'en', 'title' => 'New Match!', 'content' => 'You matched with {{matched_name}}!'],
            ['template_code' => 'match', 'language_code' => 'ar', 'title' => 'تطابق جديد!', 'content' => 'لقد تطابقت مع {{matched_name}}!'],

            // Welcome
            ['template_code' => 'welcome', 'language_code' => 'tr', 'title' => 'Hoş Geldiniz!', 'content' => 'Merhaba {{name}}, platforma hoş geldiniz!'],
            ['template_code' => 'welcome', 'language_code' => 'en', 'title' => 'Welcome!', 'content' => 'Hello {{name}}, welcome to the platform!'],
            ['template_code' => 'welcome', 'language_code' => 'ar', 'title' => 'مرحباً!', 'content' => 'مرحباً {{name}}، أهلاً بك في المنصة!'],

            // Package Congrats
            ['template_code' => 'package_congrats', 'language_code' => 'tr', 'title' => 'Paket Aktif!', 'content' => '{{package_name}} paketiniz aktif edildi!'],
            ['template_code' => 'package_congrats', 'language_code' => 'en', 'title' => 'Package Active!', 'content' => 'Your {{package_name}} package is now active!'],
            ['template_code' => 'package_congrats', 'language_code' => 'ar', 'title' => 'الباقة مفعلة!', 'content' => 'تم تفعيل باقتك {{package_name}}!'],

            // Receipt
            ['template_code' => 'receipt', 'language_code' => 'tr', 'title' => 'Ödeme Alındı', 'content' => '{{amount}} tutarında ödemeniz alınmıştır.'],
            ['template_code' => 'receipt', 'language_code' => 'en', 'title' => 'Payment Received', 'content' => 'Your payment of {{amount}} has been received.'],
            ['template_code' => 'receipt', 'language_code' => 'ar', 'title' => 'تم استلام الدفع', 'content' => 'تم استلام دفعتك بمبلغ {{amount}}.'],
        ];

        foreach ($templates as $t) {
            NotificationTemplate::updateOrCreate(
                ['template_code' => $t['template_code'], 'language_code' => $t['language_code']],
                $t
            );
        }
    }
}
