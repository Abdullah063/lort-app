<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntrepreneurProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // =============================================
    // KULLANICI BİLGİLERİ GÜNCELLE (isim, soyisim, telefon)
    // POST /api/profile/user-update
    // =============================================
    public function updateUser(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'name'    => 'sometimes|string|max:100',
            'surname' => 'sometimes|string|max:100',
            'phone'   => 'sometimes|nullable|string|max:20|unique:users,phone,' . $user->id,
            'email'   => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only(['name', 'surname', 'phone', 'email']));

        return response()->json([
            'message' => 'Kullanıcı bilgileri güncellendi',
            'user'    => $user->fresh(),
        ]);
    }

    // =============================================
    // PROFİL TAMAMLANMA DURUMU
    // GET /api/profile/status
    // =============================================
    public function status()
    {
        $user = auth('api')->user();
        $user->load(['entrepreneurProfile', 'company', 'goals', 'interests']);

        $steps = [
            'register'    => true, // buraya geldiyse zaten kayıt olmuş
            'profile'     => $user->entrepreneurProfile !== null,
            'goals'       => $user->goals->count() >= 1,
            'interests'   => $user->interests->count() >= 1,
            'company'     => $user->company !== null,
        ];

        $completed = collect($steps)->filter()->count();
        $total = count($steps);
        $is_complete = $completed === $total;

        return response()->json([
            'is_complete'      => $is_complete,
            'completed_steps'  => $completed,
            'total_steps'      => $total,
            'percentage'       => round(($completed / $total) * 100),
            'steps'            => $steps,
            'next_step'        => $is_complete ? null : collect($steps)->filter(fn($v) => !$v)->keys()->first(),
        ]);
    }

    // =============================================
    // PROFİL OLUŞTUR (Kayıt sonrası ilk adım)
    // POST /api/profile
    // =============================================
    public function store(Request $request)
    {
        $user = auth('api')->user();

        // Zaten profili varsa hata döndür
        if ($user->entrepreneurProfile) {
            return response()->json([
                'message' => 'Profiliniz zaten mevcut',
            ], 409);
        }

        // Veri doğrulama
        $request->validate([
            'category'          => 'required|string|in:bireysel,kurumsal,diger',
            'profile_image_url' => 'nullable|string',
            'birth_date'        => 'nullable|date|before:today',
            'about_me'          => 'nullable|string|max:1000',
        ]);

        // Profil oluştur
        $profile = $user->entrepreneurProfile()->create([
            'category'          => $request->category,
            'profile_image_url' => $request->profile_image_url,
            'birth_date'        => $request->birth_date,
            'about_me'          => $request->about_me,
        ]);

        return response()->json([
            'message' => 'Profil oluşturuldu',
            'profile' => $profile,
        ], 201);
    }

    // =============================================
    // PROFİLİ GÖRÜNTÜLE
    // GET /api/profile
    // =============================================
    public function show()
    {
        $user = auth('api')->user();

        // Kullanıcının tüm bilgilerini yükle
        $user->load([
            'entrepreneurProfile',
            'company',
            'goals',
            'interests',
            'photoGallery',
            'roles',
            'activeMembership.packageDefinition',
        ]);

        return response()->json([
            'user' => $user,
        ]);
    }

    // =============================================
    // PROFİL GÜNCELLE
    // PUT /api/profile
    // =============================================
    public function update(Request $request)
    {
        $user = auth('api')->user();
        $profile = $user->entrepreneurProfile;

        // Profil yoksa hata döndür
        if (!$profile) {
            return response()->json([
                'message' => 'Önce profil oluşturmalısınız',
            ], 404);
        }

        // Veri doğrulama
        $request->validate([
            'category'          => 'sometimes|string|in:bireysel,kurumsal,diger',
            'profile_image_url' => 'nullable|string',
            'birth_date'        => 'nullable|date|before:today',
            'about_me'          => 'nullable|string|max:1000',
        ]);

        // Güncelle (sadece gönderilen alanları günceller)
        $profile->update($request->only([
            'category',
            'profile_image_url',
            'birth_date',
            'about_me',
        ]));

        return response()->json([
            'message' => 'Profil güncellendi',
            'profile' => $profile->fresh(),
        ]);
    }
}