<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\SocialLoginService;
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
            'email'    => 'required_without:phone|nullable|email|unique:users,email',
            'phone'    => 'required_without:email|nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
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

        // 4. Varsayılan "free" üyelik oluştur 
        
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
    // SOSYAL MEDYA İLE GİRİŞ / KAYIT
    // POST /api/auth/social
    // =============================================
    public function socialLogin(Request $request, SocialLoginService $socialLoginService)
    {
        $request->validate([
            'provider' => 'required|string|in:google,apple',
            'token'    => 'required|string',
        ]);

        try {
            $socialUser = $socialLoginService->verify(
                $request->provider,
                $request->token
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }

        // Kullanıcı var mı kontrol et (provider_id veya email ile)
        $user = User::where('provider', $socialUser['provider'])
            ->where('provider_id', $socialUser['provider_id'])
            ->first();

        if (!$user && $socialUser['email']) {
            $user = User::where('email', $socialUser['email'])->first();

            // Email ile kayıtlı ama farklı provider — hesabı bağla
            if ($user) {
                $user->update([
                    'provider'    => $socialUser['provider'],
                    'provider_id' => $socialUser['provider_id'],
                    'avatar'      => $socialUser['avatar'] ?? $user->avatar,
                ]);
            }
        }

        $isNewUser = false;

        // Kullanıcı yoksa yeni kayıt oluştur
        if (!$user) {
            $isNewUser = true;

            $user = User::create([
                'name'        => $socialUser['name'] ?: 'İsimsiz',
                'surname'     => $socialUser['surname'] ?: '',
                'email'       => $socialUser['email'],
                'provider'    => $socialUser['provider'],
                'provider_id' => $socialUser['provider_id'],
                'avatar'      => $socialUser['avatar'],
            ]);

            // Varsayılan "user" rolünü ata
            $userRole = Role::where('name', 'user')->first();
            if ($userRole) {
                $user->roles()->attach($userRole->id, [
                    'assigned_by' => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Hesap aktif mi?
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Hesabınız askıya alınmıştır',
            ], 403);
        }

        // Son giriş tarihini güncelle
        $user->update(['last_login_at' => now()]);

        // JWT token üret
        $token = auth('api')->login($user);

        return response()->json([
            'message'     => $isNewUser ? 'Kayıt ve giriş başarılı' : 'Giriş başarılı',
            'is_new_user' => $isNewUser,
            'user'        => $user,
            'token'       => $this->tokenResponse($token),
        ], $isNewUser ? 201 : 200);
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