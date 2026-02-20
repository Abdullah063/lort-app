<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public static function send(string $phone, string $message): bool
    {
        if (!str_starts_with($phone, '90')) {
            $phone = '90' . ltrim($phone, '0');
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<mainbody>';
        $xml .= '<header>';
        $xml .= '<usercode>' . config('services.netgsm.username') . '</usercode>';
        $xml .= '<password>' . config('services.netgsm.password') . '</password>';
        $xml .= '<msgheader>' . config('services.netgsm.sender') . '</msgheader>';
        $xml .= '</header>';
        $xml .= '<body>';
        $xml .= '<msg><![CDATA[' . $message . ']]></msg>';
        $xml .= '<no>' . $phone . '</no>';
        $xml .= '</body>';
        $xml .= '</mainbody>';

        $response = Http::withBody($xml, 'text/xml')
            ->post('https://api.netgsm.com.tr/sms/send/otp');

        return str_contains($response->body(), '<code>0</code>');
    }
}
