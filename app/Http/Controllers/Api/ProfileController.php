<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntrepreneurProfile;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;


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
            'register'    => true,
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
    // KATEGORİ SEÇ (Entrepreneur tipi)
    // POST /api/profile/category
    // =============================================
    public function setCategory(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'category' => 'required|string|in:bireysel,kurumsal,diger',
        ]);

        if ($user->entrepreneurProfile) {
            $user->entrepreneurProfile->update(['category' => $request->category]);
        } else {
            $user->entrepreneurProfile()->create([
                'category' => $request->category,
            ]);
        }

        return response()->json(['message' => 'Kategori seçildi']);
    }

    // =============================================
    // PROFİL OLUŞTUR (Kayıt sonrası ilk adım)
    // POST /api/profile
    // =============================================
    public function store(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'name'              => 'required|string|max:100',
            'surname'           => 'required|string|max:100',
            'profile_image_url' => 'nullable|string',
            'about_me'          => 'nullable|string|max:1000',
            'birth_date'        => 'nullable|date|before:today',
        ]);

        $user->update([
            'name'    => $request->name,
            'surname' => $request->surname,
        ]);

        $profile = $user->entrepreneurProfile;

        if ($profile) {
            $profile->update([
                'profile_image_url' => $request->profile_image_url,
                'about_me'          => $request->about_me,
                'birth_date'        => $request->birth_date,
            ]);
        } else {
            $profile = $user->entrepreneurProfile()->create([
                'category'          => $user->entrepreneurProfile?->category ?? 'bireysel',
                'profile_image_url' => $request->profile_image_url,
                'about_me'          => $request->about_me,
                'birth_date'        => $request->birth_date,
            ]);
        }

        if ($user->email) {
            Mail::to($user->email)->send(new WelcomeMail($user));
        }

        return response()->json([
            'message' => 'Profil tamamlandı',
            'user'    => $user->fresh()->load('entrepreneurProfile'),
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
