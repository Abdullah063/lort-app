<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        return $this->subject('Hoş Geldiniz!')
                    ->html("
                        <h2>Merhaba {$this->user->name},</h2>
                        <p>Lord App'e hoş geldiniz! Profiliniz başarıyla oluşturuldu.</p>
                        <p>Artık platformumuzu kullanmaya başlayabilirsiniz.</p>
                    ");
    }
}