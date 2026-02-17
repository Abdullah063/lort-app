<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\PackageDefinition;
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
        $request->validate([
            'name'     => 'required|string|max:100',
            'surname'  => 'required|string|max:100',
            'email'    => 'required_without:phone|nullable|email|unique:users,email',
            'phone'    => 'required_without:email|nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

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
                'assigned_by' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 4. ✅ Varsayılan "free" üyelik oluştur
        $this->assignFreePackage($user);

        // 5. Token üret
        $token = auth('api')->login($user);

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

        $user = User::where('provider', $socialUser['provider'])
            ->where('provider_id', $socialUser['provider_id'])
            ->first();

        if (!$user && $socialUser['email']) {
            $user = User::where('email', $socialUser['email'])->first();

            if ($user) {
                $user->update([
                    'provider'    => $socialUser['provider'],
                    'provider_id' => $socialUser['provider_id'],
                    'avatar'      => $socialUser['avatar'] ?? $user->avatar,
                ]);
            }
        }

        $isNewUser = false;

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

            $userRole = Role::where('name', 'user')->first();
            if ($userRole) {
                $user->roles()->attach($userRole->id, [
                    'assigned_by' => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // ✅ Yeni sosyal kullanıcıya da free üyelik ata
            $this->assignFreePackage($user);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Hesabınız askıya alınmıştır',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'E-posta veya şifre hatalı',
            ], 401);
        }

        $user = auth('api')->user();
        if (!$user->is_active) {
            auth('api')->logout();
            return response()->json([
                'message' => 'Hesabınız askıya alınmıştır',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

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
        $user->load(['roles', 'entrepreneurProfile', 'company']);

        return response()->json([
            'user' => $user,
        ]);
    }

    // =============================================
    // YARDIMCI: Free üyelik ata
    // =============================================
    private function assignFreePackage(User $user): void
    {
        $freePackage = PackageDefinition::where('name', 'free')->first();

        if ($freePackage) {
            $user->memberships()->create([
                'package_id' => $freePackage->id,
                'starts_at'  => now(),
                'is_active'  => true,
            ]);
        }
    }

    // =============================================
    // YARDIMCI: Token yanıt formatı
    // =============================================
    private function tokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ];
    }
}