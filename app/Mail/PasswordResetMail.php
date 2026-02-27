<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function build()
    {
        return $this->subject('Şifre Sıfırlama Kodunuz')
                   ->html("
                        <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px;'>
                            <h2 style='color:#1a1a1a;'>Şifre Sıfırlama</h2>
                            <p style='color:#555;'>Şifrenizi sıfırlamak için aşağıdaki kodu kullanın:</p>
                            <div style='background:#f4f4f4;border-radius:8px;padding:24px;text-align:center;margin:24px 0;'>
                                <span style='font-size:36px;font-weight:bold;letter-spacing:8px;color:#1a1a1a;'>{$this->code}</span>
                            </div>
                            <p style='color:#888;font-size:13px;'>Bu kod <strong>5 dakika</strong> geçerlidir. Eğer bu isteği siz yapmadıysanız şifreniz güvendedir, bu e-postayı dikkate almayın.</p>
                        </div>
                    ");
    }
}