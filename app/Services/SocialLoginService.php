<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class SocialLoginService
{
    /**
     * Provider'a göre token doğrula ve kullanıcı bilgilerini döndür.
     *
     * @return array{provider: string, provider_id: string, email: string, name: string, surname: string, avatar: string|null}
     */
    public function verify(string $provider, string $token): array
    {
        return match ($provider) {
            'google' => $this->verifyGoogle($token),
            'apple'  => $this->verifyApple($token),
            default  => throw new Exception("Desteklenmeyen provider: {$provider}"),
        };
    }

    /**
     * Google id_token doğrula.
     * Mobil uygulama Google SDK ile aldığı id_token'ı gönderir.
     */
    private function verifyGoogle(string $idToken): array
    {
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if ($response->failed()) {
            throw new Exception('Google token doğrulanamadı');
        }

        $data = $response->json();

        // Google Client ID kontrolü
        $allowedClientIds = [
            config('services.google.client_id'),
            config('services.google.ios_client_id'),
            config('services.google.android_client_id'),
        ];

        if (!in_array($data['aud'] ?? null, array_filter($allowedClientIds))) {
            throw new Exception('Google token geçersiz client ID içeriyor');
        }

        return [
            'provider'    => 'google',
            'provider_id' => $data['sub'],
            'email'       => $data['email'],
            'name'        => $data['given_name'] ?? '',
            'surname'     => $data['family_name'] ?? '',
            'avatar'      => $data['picture'] ?? null,
        ];
    }

    /**
     * Apple identityToken doğrula.
     * Mobil uygulama Apple Sign In SDK ile aldığı identityToken'ı gönderir.
     */
    private function verifyApple(string $identityToken): array
    {
        // Apple public key'leri al
        $response = Http::get('https://appleid.apple.com/auth/keys');

        if ($response->failed()) {
            throw new Exception('Apple public key alınamadı');
        }

        $keys = $response->json('keys');

        // JWT header'ından kid al
        $headerB64 = explode('.', $identityToken)[0] ?? '';
        $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true);
        $kid = $header['kid'] ?? null;

        if (!$kid) {
            throw new Exception('Apple token geçersiz format');
        }

        // Doğru key'i bul
        $matchedKey = collect($keys)->firstWhere('kid', $kid);
        if (!$matchedKey) {
            throw new Exception('Apple public key bulunamadı');
        }

        // JWT payload'ı decode et
        $payloadB64 = explode('.', $identityToken)[1] ?? '';
        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);

        if (!$payload) {
            throw new Exception('Apple token decode edilemedi');
        }

        // Issuer ve audience kontrolü
        if (($payload['iss'] ?? '') !== 'https://appleid.apple.com') {
            throw new Exception('Apple token geçersiz issuer');
        }

        if (($payload['aud'] ?? '') !== config('services.apple.client_id')) {
            throw new Exception('Apple token geçersiz client ID');
        }

        // Token süresi kontrolü
        if (($payload['exp'] ?? 0) < time()) {
            throw new Exception('Apple token süresi dolmuş');
        }

        return [
            'provider'    => 'apple',
            'provider_id' => $payload['sub'],
            'email'       => $payload['email'] ?? null,
            'name'        => '',
            'surname'     => '',
            'avatar'      => null,
        ];
    }
}
