<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =============================================
    // KAYIT OL
    // =============================================
    public function register(Request $request)
    {
        // 1. Gelen veriyi doğrula
        $request->validate([
            'name'     => 'required|string|max:100',
            'surname'  => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // password_confirmation da gönderilmeli
            'phone'    => 'nullable|string|max:20',
        ]);

        // 2. Kullanıcı oluştur
        $user = User::create([
            'name'     => $request->name,
            'surname'  => $request->surname,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        // 3. Varsayılan "user" rolünü ata
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id, [
                'assigned_by' => null,  // sistem tarafından atandı
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 4. Varsayılan "free" üyelik oluştur (ileride aktif edilecek)
        // $user->memberships()->create([...]);

        // 5. Token üret
        $token = auth('api')->login($user);

        // 6. Yanıt döndür
        return response()->json([
            'message' => 'Kayıt başarılı',
            'user'    => $user,
            'token'   => $this->tokenResponse($token),
        ], 201);
    }

    // =============================================
    // GİRİŞ YAP
    // =============================================
    public function login(Request $request)
    {
        // 1. Gelen veriyi doğrula
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Giriş bilgilerini kontrol et
        $credentials = $request->only('email', 'password');
        $token = auth('api')->attempt($credentials);

        // 3. Başarısızsa hata döndür
        if (!$token) {
            return response()->json([
                'message' => 'E-posta veya şifre hatalı',
            ], 401);
        }

        // 4. Hesap aktif mi kontrol et
        $user = auth('api')->user();
        if (!$user->is_active) {
            auth('api')->logout();
            return response()->json([
                'message' => 'Hesabınız askıya alınmıştır',
            ], 403);
        }

        // 5. Son giriş tarihini güncelle
        $user->update(['last_login_at' => now()]);

        // 6. Token döndür
        return response()->json([
            'message' => 'Giriş başarılı',
            'user'    => $user,
            'token'   => $this->tokenResponse($token),
        ]);
    }

    // =============================================
    // ÇIKIŞ YAP
    // =============================================
    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Çıkış başarılı',
        ]);
    }

    // =============================================
    // TOKEN YENİLE
    // =============================================
    public function refresh()
    {
        $token = auth('api')->refresh();

        return response()->json([
            'message' => 'Token yenilendi',
            'token'   => $this->tokenResponse($token),
        ]);
    }

    // =============================================
    // KULLANICI BİLGİLERİ
    // =============================================
    public function me()
    {
        $user = auth('api')->user();

        // Kullanıcının rollerini ve profilini de getir
        $user->load(['roles', 'entrepreneurProfile', 'company']);

        return response()->json([
            'user' => $user,
        ]);
    }

    // =============================================
    // YARDIMCI: Token yanıt formatı
    // =============================================
    private function tokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // saniye cinsinden
        ];
    }
}