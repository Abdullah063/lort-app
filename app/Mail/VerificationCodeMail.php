<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function build()
    {
        return $this->subject('E-posta Doğrulama Kodunuz')
                    ->html("
                        <h2>E-posta Doğrulama</h2>
                        <p>Doğrulama kodunuz: <strong>{$this->code}</strong></p>
                        <p>Bu kod 5 dakika geçerlidir.</p>
                    ");
    }
}