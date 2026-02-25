<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PackageCongratsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $packageName,
        public string $period,
        public string $amount,
        public string $expiresAt,
        public string $lang = 'tr'
    ) {}

    public function build()
    {
        $name = $this->user->name;

        $t = match ($this->lang) {
            'en' => [
                'subject' => "Welcome to {$this->packageName}, {$name}! 🎉",
                'headerTitle' => 'Congratulations!',
                'headerSubtitle' => "You're now a {$this->packageName} member",
                'greeting' => 'Hi',
                'intro' => "Your {$this->packageName} membership is now active! Here are your subscription details:",
                'packageLabel' => 'Package',
                'periodLabel' => 'Period',
                'amountLabel' => 'Amount',
                'expiresLabel' => 'Valid Until',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly',
                'ctaButton' => 'Explore Your Benefits',
                'helpTitle' => 'Need Help?',
                'helpText' => 'Feel free to contact us if you have any questions.',
                'support' => 'Support: support@lortapp.com',
                'footer' => 'This email was sent automatically.',
                'allRights' => '© 2026 LORT. All rights reserved.',
            ],
            'ar' => [
                'subject' => "مرحبًا بك في {$this->packageName}، {$name}! 🎉",
                'headerTitle' => 'تهانينا!',
                'headerSubtitle' => "أنت الآن عضو في {$this->packageName}",
                'greeting' => 'مرحبا',
                'intro' => "عضويتك في {$this->packageName} أصبحت نشطة الآن! إليك تفاصيل اشتراكك:",
                'packageLabel' => 'الباقة',
                'periodLabel' => 'المدة',
                'amountLabel' => 'المبلغ',
                'expiresLabel' => 'صالح حتى',
                'monthly' => 'شهري',
                'yearly' => 'سنوي',
                'ctaButton' => 'استكشف مزاياك',
                'helpTitle' => 'هل تحتاج مساعدة؟',
                'helpText' => 'لا تتردد في الاتصال بنا إذا كان لديك أي أسئلة.',
                'support' => 'support@lortapp.com :الدعم',
                'footer' => 'تم إرسال هذا البريد الإلكتروني تلقائيا.',
                'allRights' => '© 2026 LORT. جميع الحقوق محفوظة.',
            ],
            default => [
                'subject' => "{$name}, {$this->packageName} paketine hoş geldin! 🎉",
                'headerTitle' => 'Tebrikler!',
                'headerSubtitle' => "Artık {$this->packageName} üyesisin",
                'greeting' => 'Merhaba',
                'intro' => "{$this->packageName} üyeliğin aktif! İşte abonelik detayların:",
                'packageLabel' => 'Paket',
                'periodLabel' => 'Dönem',
                'amountLabel' => 'Tutar',
                'expiresLabel' => 'Geçerlilik',
                'monthly' => 'Aylık',
                'yearly' => 'Yıllık',
                'ctaButton' => 'Avantajlarını Keşfet',
                'helpTitle' => 'Yardıma mı ihtiyacın var?',
                'helpText' => 'Herhangi bir sorun varsa bizimle iletişime geçmekten çekinme.',
                'support' => 'Destek: support@lortapp.com',
                'footer' => 'Bu e-posta otomatik olarak gönderilmiştir.',
                'allRights' => '© 2026 LORT. Tüm hakları saklıdır.',
            ],
        };

        $periodText = $this->period === 'yearly' ? $t['yearly'] : $t['monthly'];
        $dir = $this->lang === 'ar' ? 'rtl' : 'ltr';

        return $this->subject($t['subject'])
                    ->html('
<!DOCTYPE html>
<html lang="' . $this->lang . '" dir="' . $dir . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 30px auto; background: white; border-radius: 16px; box-shadow: 0 4px 24px rgba(255, 107, 53, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #FF6B35 0%, #E55A2B 100%); color: white; padding: 50px 30px; text-align: center; }
        .logo { font-size: 42px; font-weight: bold; margin-bottom: 15px; letter-spacing: 3px; }
        .header-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .header-subtitle { font-size: 18px; opacity: 0.95; }
        .badge { display: inline-block; background: rgba(255,255,255,0.25); border-radius: 20px; padding: 8px 20px; font-size: 14px; margin-top: 15px; font-weight: 600; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 20px; color: #333; margin-bottom: 10px; font-weight: 600; }
        .customer-name { color: #FF6B35; font-weight: 700; }
        .message { font-size: 15px; color: #666; line-height: 1.8; margin-bottom: 30px; }
        .details-box { background: #FFF8F6; border: 2px solid #FFE5DC; border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #FFE5DC; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-size: 14px; color: #888; font-weight: 500; }
        .detail-value { font-size: 15px; color: #333; font-weight: 700; }
        .cta-section { text-align: center; margin: 35px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #FF6B35 0%, #E55A2B 100%); color: white; text-decoration: none; padding: 16px 40px; border-radius: 30px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4); }
        .help-section { background: #F8F9FA; border-radius: 12px; padding: 25px; text-align: center; margin: 25px 0; }
        .help-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 10px; }
        .help-text { font-size: 14px; color: #666; margin-bottom: 15px; }
        .support-link { color: #FF6B35; text-decoration: none; font-weight: 600; }
        .footer { background: #2C2C2C; color: #999; padding: 30px; text-align: center; font-size: 13px; }
        .footer-logo { color: #FF6B35; font-weight: bold; font-size: 16px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">LORT</div>
            <div class="header-title">' . $t['headerTitle'] . '</div>
            <div class="header-subtitle">' . $t['headerSubtitle'] . '</div>
            <div class="badge">⭐ ' . e($this->packageName) . '</div>
        </div>
        <div class="content">
            <div class="greeting">' . $t['greeting'] . ' <span class="customer-name">' . e($name) . '</span>,</div>
            <p class="message">' . $t['intro'] . '</p>
            <div class="details-box">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;color:#888;font-size:14px">' . $t['packageLabel'] . '</td><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;text-align:right;font-weight:700;color:#FF6B35;font-size:15px">' . e($this->packageName) . '</td></tr>
                    <tr><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;color:#888;font-size:14px">' . $t['periodLabel'] . '</td><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;text-align:right;font-weight:700;font-size:15px">' . $periodText . '</td></tr>
                    <tr><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;color:#888;font-size:14px">' . $t['amountLabel'] . '</td><td style="padding:12px 0;border-bottom:1px solid #FFE5DC;text-align:right;font-weight:700;font-size:15px">' . e($this->amount) . ' TL</td></tr>
                    <tr><td style="padding:12px 0;color:#888;font-size:14px">' . $t['expiresLabel'] . '</td><td style="padding:12px 0;text-align:right;font-weight:700;font-size:15px">' . e($this->expiresAt) . '</td></tr>
                </table>
            </div>
            <div class="cta-section"><a href="https://lortapp.com" class="cta-button">' . $t['ctaButton'] . '</a></div>
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