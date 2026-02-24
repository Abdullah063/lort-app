<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\PackageDefinition;
use App\Services\NotificationService;
use App\Services\SocialLoginService;
use App\Services\SmsService;
use App\Models\SmsVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\EmailVerificationCode;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;


use App\Models\PasswordResetCode;
use App\Mail\PasswordResetMail;

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

        // 4.  Varsayılan "free" üyelik oluştur
        $this->assignFreePackage($user);

        // 5.  Hoşgeldin bildirimi
        NotificationService::send($user->id, 'welcome');

        // Doğrulama kodu gönder (dd)
        EmailVerificationCode::where('user_id', $user->id)->delete();
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        // 6. Token üret
        $token = auth('api')->login($user);

        return response()->json([
            'message' => 'Kayıt başarılı',
            'user'    => $user,
            'token'   => $this->tokenResponse($token),
        ], 201);
    }
    public function init(Request $request)
    {
        $request->validate([
            'email' => 'required_without:phone|nullable|email',
            'phone' => 'required_without:email|nullable|string|max:20',
        ]);

        // ── EMAIL ile gelen kullanıcı ──────────────────────────
        if ($request->email) {
            $existing = User::where('email', $request->email)->first();

            if ($existing) {
                // Şifresi var → login ekranına yönlendir
                if ($existing->password) {
                    return response()->json([
                        'message' => 'Lütfen şifrenizle giriş yapın',
                        'status'  => 'login_required',
                    ], 409);
                }

                // Şifresi yok → yarım kalmış, kod gönder
                EmailVerificationCode::where('user_id', $existing->id)->delete();
                $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                EmailVerificationCode::create([
                    'user_id'    => $existing->id,
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(5),
                ]);
                Mail::to($existing->email)->send(new VerificationCodeMail($code));

                $token = auth('api')->login($existing);

                return response()->json([
                    'message' => 'Doğrulama kodu gönderildi',
                    'token'   => $this->tokenResponse($token),
                ]);
            }
        }

        // ── TELEFON ile gelen kullanıcı ───────────────────────
        if ($request->phone) {
            $existing = User::where('phone', $request->phone)->first();

            if ($existing) {
                // Şifresi var → login ekranına yönlendir
                if ($existing->password) {
                    return response()->json([
                        'message' => 'Lütfen şifrenizle giriş yapın',
                        'status'  => 'login_required',
                    ], 409);
                }

                // Şifresi yok → yarım kalmış, kod gönder
                SmsVerificationCode::where('user_id', $existing->id)->delete();
                $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                SmsVerificationCode::create([
                    'user_id'    => $existing->id,
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(5),
                ]);
                SmsService::send($existing->phone, "Doğrulama kodunuz: {$code}");

                $token = auth('api')->login($existing);

                return response()->json([
                    'message' => 'Doğrulama kodu gönderildi',
                    'token'   => $this->tokenResponse($token),
                ]);
            }
        }

        // ── YENİ kullanıcı oluştur ────────────────────────────
        $user = User::create([
            'name'    => 'Geçici',
            'surname' => 'Kullanıcı',
            'email'   => $request->email,
            'phone'   => $request->phone,
        ]);

        $this->assignFreePackage($user);

        if ($request->email) {
            $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            EmailVerificationCode::create([
                'user_id'    => $user->id,
                'code'       => $code,
                'expires_at' => now()->addMinutes(5),
            ]);
            Mail::to($user->email)->send(new VerificationCodeMail($code));
        }

        if ($request->phone) {
            $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            SmsVerificationCode::create([
                'user_id'    => $user->id,
                'code'       => $code,
                'expires_at' => now()->addMinutes(10),
            ]);
            SmsService::send($user->phone, "Doğrulama kodunuz: {$code}");
        }

        $token = auth('api')->login($user);

        return response()->json([
            'message' => 'Doğrulama kodu gönderildi',
            'token'   => $this->tokenResponse($token),
        ], 201);
    }
    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth('api')->user();
        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Şifre belirlendi']);
    }

    // =============================================
    // SOSYAL MEDYA İLE GİRİŞ / KAYIT(teyit edilmeli...)
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

            // Yeni sosyal kullanıcıya da free üyelik ata
            $this->assignFreePackage($user);

            // Hoşgeldin bildirimi
            NotificationService::send($user->id, 'welcome');
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

        // Profil durumu
        $user->load(['entrepreneurProfile', 'goals', 'interests', 'company']);

        $steps = [
            'register'  => true,
            'password'  => !is_null($user->password),
            'profile'   => $user->entrepreneurProfile !== null,
            'goals'     => $user->goals->count() >= 1,
            'interests' => $user->interests->count() >= 1,
            'company'   => $user->company !== null,
        ];

        $nextStep = collect($steps)->filter(fn($v) => !$v)->keys()->first();

        return response()->json([
            'message'   => 'Giriş başarılı',
            'user'      => $user,
            'token'     => $this->tokenResponse($token),
            'next_step' => $nextStep, // null ise profil tamamlanmış
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

    // =============================================
    // DOĞRULAMA KODU GÖNDER
    // POST /api/auth/send-verification
    // =============================================
    public function sendVerification()
    {
        $user = auth('api')->user();

        if ($user->email_verified) {
            return response()->json(['message' => 'E-posta zaten doğrulanmış'], 409);
        }

        // Önceki kodları sil
        EmailVerificationCode::where('user_id', $user->id)->delete();

        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json(['message' => 'Doğrulama kodu gönderildi']);
    }

    // =============================================
    // DOĞRULAMA KODUNU ONAYLA
    // POST /api/auth/verify-email
    // =============================================
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $user = auth('api')->user();

        if ($user->email_verified) {
            return response()->json(['message' => 'E-posta zaten doğrulanmış'], 409);
        }

        $record = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Kod hatalı veya süresi dolmuş'], 422);
        }

        $user->update(['email_verified' => true]);
        $record->delete();

        return response()->json(['message' => 'E-posta başarıyla doğrulandı']);
    }

    // =============================================
    // ŞİFREMİ UNUTTUM - KOD GÖNDER
    // POST /api/auth/forgot-password
    // =============================================
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Önceki kodları sil
        PasswordResetCode::where('user_id', $user->id)->delete();

        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        PasswordResetCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new PasswordResetMail($code));

        return response()->json(['message' => 'Şifre sıfırlama kodu gönderildi']);
    }

    // =============================================
    // ŞİFREMİ UNUTTUM - SIFIRLA
    // POST /api/auth/reset-password
    // =============================================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'code'                  => 'required|string|size:4',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        $record = PasswordResetCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Kod hatalı veya süresi dolmuş'], 422);
        }

        $user->update(['password' => $request->password]);
        $record->delete();

        return response()->json(['message' => 'Şifre başarıyla sıfırlandı']);
    }
    // =============================================
    // SMS DOĞRULAMA KODU GÖNDER
    // POST /api/auth/send-sms-verification
    // =============================================
    public function sendSmsVerification()
    {
        $user = auth('api')->user();

        if (!$user->phone) {
            return response()->json(['message' => 'Telefon numarası bulunamadı'], 422);
        }

        // Önceki kodları sil
        SmsVerificationCode::where('user_id', $user->id)->delete();

        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        SmsVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        $sent = SmsService::send($user->phone, " LORT dogrulama kodunuz: {$code}. 5 dakika geçerlidir.");

        if (!$sent) {
            return response()->json(['message' => 'SMS gönderilemedi'], 500);
        }

        return response()->json(['message' => 'SMS doğrulama kodu gönderildi']);
    }

    // =============================================
    // SMS DOĞRULAMA KODUNU ONAYLA
    // POST /api/auth/verify-phone
    // =============================================
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $user = auth('api')->user();

        $record = SmsVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Kod hatalı veya süresi dolmuş'], 422);
        }

        $user->update(['phone_verified' => true]);
        $record->delete();

        return response()->json(['message' => 'Telefon numarası başarıyla doğrulandı']);
    }
}
