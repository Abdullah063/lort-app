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

        $t = match ($this->lang) {
            'en' => [
                'subject' => "{$name}, we have personalized tips for you!",
                'headerTitle' => 'Your Personal Tips',
                'headerSubtitle' => 'We analyzed your profile with AI',
                'greeting' => "Hi",
                'intro' => "We reviewed your profile and prepared personalized recommendations just for you. Here are our AI-powered suggestions to help you grow on LORT:",
                'sectionTitle' => 'Your Personalized Recommendations',
                'tip1Title' => 'Improve Your Profile',
                'tip1Text' => 'A complete profile gets 3x more matches',
                'tip2Title' => 'Expand Your Network',
                'tip2Text' => 'Connect with professionals in your industry',
                'tip3Title' => 'Set Your Goals',
                'tip3Text' => 'Clear goals help us find better matches',
                'ctaButton' => 'Open LORT App',
                'helpTitle' => 'Need Help?',
                'helpText' => 'Feel free to contact us if you have any questions.',
                'support' => 'Support: support@lortapp.com',
                'footer' => 'This email was sent automatically.',
                'allRights' => '© 2026 LORT. All rights reserved.',
            ],
            'ar' => [
                'subject' => "{$name}، لدينا نصائح مخصصة لك!",
                'headerTitle' => 'نصائحك الشخصية',
                'headerSubtitle' => 'قمنا بتحليل ملفك الشخصي بالذكاء الاصطناعي',
                'greeting' => "مرحبا",
                'intro' => "راجعنا ملفك الشخصي وأعددنا توصيات مخصصة لك. إليك اقتراحاتنا المدعومة بالذكاء الاصطناعي لمساعدتك على النمو في LORT:",
                'sectionTitle' => 'توصياتك المخصصة',
                'tip1Title' => 'حسّن ملفك الشخصي',
                'tip1Text' => 'الملف الكامل يحصل على 3 أضعاف التطابقات',
                'tip2Title' => 'وسّع شبكتك',
                'tip2Text' => 'تواصل مع محترفين في مجالك',
                'tip3Title' => 'حدد أهدافك',
                'tip3Text' => 'الأهداف الواضحة تساعدنا في إيجاد تطابقات أفضل',
                'ctaButton' => 'افتح تطبيق LORT',
                'helpTitle' => 'هل تحتاج مساعدة؟',
                'helpText' => 'لا تتردد في الاتصال بنا إذا كان لديك أي أسئلة.',
                'support' => 'support@lortapp.com :الدعم',
                'footer' => 'تم إرسال هذا البريد الإلكتروني تلقائيا.',
                'allRights' => '© 2026 LORT. جميع الحقوق محفوظة.',
            ],
            'de' => [
                'subject' => "{$name}, wir haben personalisierte Tipps für dich!",
                'headerTitle' => 'Deine persönlichen Tipps',
                'headerSubtitle' => 'Wir haben dein Profil mit KI analysiert',
                'greeting' => "Hallo",
                'intro' => "Wir haben dein Profil überprüft und personalisierte Empfehlungen für dich vorbereitet. Hier sind unsere KI-gestützten Vorschläge, um dir auf LORT zu helfen:",
                'sectionTitle' => 'Deine personalisierten Empfehlungen',
                'tip1Title' => 'Verbessere dein Profil',
                'tip1Text' => 'Ein vollständiges Profil erhält 3x mehr Matches',
                'tip2Title' => 'Erweitere dein Netzwerk',
                'tip2Text' => 'Verbinde dich mit Fachleuten in deiner Branche',
                'tip3Title' => 'Setze deine Ziele',
                'tip3Text' => 'Klare Ziele helfen uns, bessere Matches zu finden',
                'ctaButton' => 'LORT App öffnen',
                'helpTitle' => 'Brauchst du Hilfe?',
                'helpText' => 'Zögere nicht, uns zu kontaktieren, wenn du Fragen hast.',
                'support' => 'Support: support@lortapp.com',
                'footer' => 'Diese E-Mail wurde automatisch gesendet.',
                'allRights' => '© 2026 LORT. Alle Rechte vorbehalten.',
            ],
            default => [
                'subject' => "{$name}, sana özel önerilerimiz var!",
                'headerTitle' => 'Sana Özel Öneriler',
                'headerSubtitle' => 'Profilini AI ile analiz ettik',
                'greeting' => "Merhaba",
                'intro' => "Profilini inceledik ve sana özel öneriler hazırladık. İşte LORT'ta daha hızlı büyümen için AI destekli tavsiyelerimiz:",
                'sectionTitle' => 'Kişisel Önerilerin',
                'tip1Title' => 'Profilini Güçlendir',
                'tip1Text' => 'Tam doldurulmuş profiller 3 kat daha fazla eşleşme alıyor',
                'tip2Title' => 'Ağını Genişlet',
                'tip2Text' => 'Sektöründeki profesyonellerle bağlantı kur',
                'tip3Title' => 'Hedeflerini Belirle',
                'tip3Text' => 'Net hedefler daha iyi eşleşmeler bulmamıza yardımcı olur',
                'ctaButton' => "LORT'u Aç",
                'helpTitle' => 'Yardıma mı ihtiyacın var?',
                'helpText' => 'Herhangi bir sorun varsa bizimle iletişime geçmekten çekinme.',
                'support' => 'Destek: support@lortapp.com',
                'footer' => 'Bu e-posta otomatik olarak gönderilmiştir.',
                'allRights' => '© 2026 LORT. Tüm hakları saklıdır.',
            ],
        };

        $dir = $this->lang === 'ar' ? 'rtl' : 'ltr';

        return $this->subject($t['subject'])
                    ->html('
<!DOCTYPE html>
<html lang="' . $this->lang . '" dir="' . $dir . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $t['headerTitle'] . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6; color: #333; background-color: #f5f5f5;
        }
        .container {
            max-width: 600px; margin: 30px auto; background: white;
            border-radius: 16px; box-shadow: 0 4px 24px rgba(255, 107, 53, 0.1); overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #FF6B35 0%, #E55A2B 100%);
            color: white; padding: 50px 30px; text-align: center;
        }
        .logo { font-size: 42px; font-weight: bold; margin-bottom: 15px; letter-spacing: 3px; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header-title { font-size: 28px; font-weight: 600; margin-bottom: 10px; }
        .header-subtitle { font-size: 16px; opacity: 0.95; font-weight: 300; }
        .ai-badge {
            display: inline-block; background: rgba(255,255,255,0.2); border-radius: 20px;
            padding: 6px 16px; font-size: 13px; margin-top: 12px; backdrop-filter: blur(4px);
        }
        .content { padding: 40px 30px; }
        .greeting { font-size: 20px; color: #333; margin-bottom: 10px; font-weight: 600; }
        .customer-name { color: #FF6B35; font-weight: 700; }
        .message { font-size: 15px; color: #666; line-height: 1.8; margin-bottom: 35px; }
        .recommendation-box {
            background: linear-gradient(135deg, #FFF8F6 0%, #FFE5DC 100%);
            border-left: 4px solid #FF6B35; border-radius: 12px;
            padding: 30px; margin-bottom: 30px;
        }
        .recommendation-title {
            font-size: 18px; font-weight: 700; color: #FF6B35; margin-bottom: 15px;
        }
        .recommendation-text { font-size: 15px; color: #444; line-height: 1.9; }
        .tips-section { margin: 30px 0; }
        .tip-item {
            display: flex; align-items: flex-start; padding: 15px 0;
            border-bottom: 1px solid #FFE5DC;
        }
        .tip-item:last-child { border-bottom: none; }
        .tip-icon {
            width: 40px; height: 40px; background: linear-gradient(135deg, #FF6B35, #E55A2B);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            color: white; font-weight: bold; font-size: 16px; flex-shrink: 0; margin-right: 15px;
            text-align: center; line-height: 40px;
        }
        .tip-content { flex: 1; }
        .tip-title { font-size: 15px; font-weight: 700; color: #333; margin-bottom: 3px; }
        .tip-text { font-size: 13px; color: #888; }
        .cta-section { text-align: center; margin: 35px 0; }
        .cta-button {
            display: inline-block; background: linear-gradient(135deg, #FF6B35 0%, #E55A2B 100%);
            color: white; text-decoration: none; padding: 16px 40px; border-radius: 30px;
            font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }
        .help-section {
            background: #F8F9FA; border-radius: 12px; padding: 25px;
            text-align: center; margin: 25px 0;
        }
        .help-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 10px; }
        .help-text { font-size: 14px; color: #666; margin-bottom: 15px; }
        .support-link { color: #FF6B35; text-decoration: none; font-weight: 600; font-size: 15px; }
        .footer {
            background: #2C2C2C; color: #999; padding: 30px; text-align: center; font-size: 13px;
        }
        .footer p { margin-bottom: 8px; }
        .footer-logo { color: #FF6B35; font-weight: bold; font-size: 16px; margin-bottom: 10px; }
        @media (max-width: 600px) {
            .container { margin: 10px; border-radius: 12px; }
            .header { padding: 40px 20px; }
            .logo { font-size: 36px; }
            .header-title { font-size: 24px; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">LORT</div>
            <div class="header-title">' . $t['headerTitle'] . '</div>
            <div class="header-subtitle">' . $t['headerSubtitle'] . '</div>
            <div class="ai-badge">AI Powered</div>
        </div>

        <div class="content">
            <div class="greeting">
                ' . $t['greeting'] . ' <span class="customer-name">' . $name . '</span>,
            </div>

            <p class="message">' . $t['intro'] . '</p>

            <div class="recommendation-box">
                <div class="recommendation-title">' . $t['sectionTitle'] . '</div>
                <div class="recommendation-text">' . nl2br(e($this->recommendation)) . '</div>
            </div>

            <div class="tips-section">
                <div class="tip-item">
                    <div class="tip-icon">1</div>
                    <div class="tip-content">
                        <div class="tip-title">' . $t['tip1Title'] . '</div>
                        <div class="tip-text">' . $t['tip1Text'] . '</div>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">2</div>
                    <div class="tip-content">
                        <div class="tip-title">' . $t['tip2Title'] . '</div>
                        <div class="tip-text">' . $t['tip2Text'] . '</div>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">3</div>
                    <div class="tip-content">
                        <div class="tip-title">' . $t['tip3Title'] . '</div>
                        <div class="tip-text">' . $t['tip3Text'] . '</div>
                    </div>
                </div>
            </div>

            <div class="cta-section">
                <a href="https://lortapp.com" class="cta-button">' . $t['ctaButton'] . '</a>
            </div>

            <div class="help-section">
                <div class="help-title">' . $t['helpTitle'] . '</div>
                <p class="help-text">' . $t['helpText'] . '</p>
                <a href="mailto:support@lortapp.com" class="support-link">' . $t['support'] . '</a>
            </div>
        </div>

        <div class="footer">
            <div class="footer-logo">LORT</div>
            <p>' . $t['footer'] . '</p>
            <p>' . $t['allRights'] . '</p>
        </div>
    </div>
</body>
</html>');
    }
}