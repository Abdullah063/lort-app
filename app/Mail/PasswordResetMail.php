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
                        <h2>Şifre Sıfırlama</h2>
                        <p>Şifre sıfırlama kodunuz: <strong>{$this->code}</strong></p>
                        <p>Bu kod 10 dakika geçerlidir.</p>
                        <p>Bu isteği siz yapmadıysanız dikkate almayınız.</p>
                    ");
    }
}