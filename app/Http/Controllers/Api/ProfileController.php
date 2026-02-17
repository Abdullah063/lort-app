<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntrepreneurProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // =============================================
    // PROFİL OLUŞTUR 
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