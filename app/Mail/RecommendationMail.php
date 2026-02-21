<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecommendationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $recommendation,
        public string $lang = 'tr'
    ) {}

    public function build()
    {
        $name = $this->user->name;

        $texts = match ($this->lang) {
            'en' => [
                'subject' => "{$name}, we have personalized tips for you!",
                'greeting' => "Hi {$name}",
                'intro' => "We reviewed your profile and prepared personalized recommendations for you:",
                'footer' => "As you update your profile, our recommendations will become even more personalized!",
                'team' => "Lord App Team",
            ],
            'de' => [
                'subject' => "{$name}, wir haben personalisierte Tipps für dich!",
                'greeting' => "Hallo {$name}",
                'intro' => "Wir haben dein Profil überprüft und personalisierte Empfehlungen für dich vorbereitet:",
                'footer' => "Je mehr du dein Profil aktualisierst, desto personalisierter werden unsere Empfehlungen!",
                'team' => "Lord App Team",
            ],
            'ar' => [
                'subject' => "{$name}، لدينا نصائح مخصصة لك!",
                'greeting' => "{$name} مرحبا",
                'intro' => "راجعنا ملفك الشخصي وأعددنا توصيات مخصصة لك:",
                'footer' => "كلما قمت بتحديث ملفك الشخصي، ستصبح توصياتنا أكثر تخصيصا!",
                'team' => "فريق Lord App",
            ],
            default => [
                'subject' => "{$name}, sana özel önerilerimiz var!",
                'greeting' => "Merhaba {$name}",
                'intro' => "Profilini inceledik ve sana özel önerilerimizi hazırladık:",
                'footer' => "Profilini güncelledikçe önerilerimiz de kişiselleşecek!",
                'team' => "Lord App Ekibi",
            ],
        };

        return $this->subject($texts['subject'])
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                            <h2 style='color: #333;'>{$texts['greeting']}</h2>
                            <p style='color: #555; font-size: 16px; line-height: 1.6;'>
                                {$texts['intro']}
                            </p>
                            <div style='background: #f8f9fa; border-left: 4px solid #4A90D9; padding: 16px; margin: 20px 0; border-radius: 4px;'>
                                <p style='color: #333; font-size: 15px; line-height: 1.8; margin: 0;'>
                                    {$this->recommendation}
                                </p>
                            </div>
                            <p style='color: #555; font-size: 14px;'>
                                {$texts['footer']}
                            </p>
                            <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                                {$texts['team']}
                            </p>
                        </div>
                    ");
    }
}