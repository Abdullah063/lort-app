<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membership;
use App\Models\MembershipHistory;
use App\Models\PackageDefinition;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    // =============================================
    // BİR KULLANICININ ÜYELİK BİLGİSİ
    // GET /api/admin/users/{userId}/membership
    // =============================================
    public function show($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        $membership = Membership::where('user_id', $userId)
            ->where('is_active', true)
            ->with('package')
            ->first();

        return response()->json([
            'user_id'    => $userId,
            'membership' => $membership,
        ]);
    }

    // =============================================
    // KULLANICININ PAKETİNİ DEĞİŞTİR
    // PUT /api/admin/users/{userId}/membership
    // =============================================
    public function update(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        $request->validate([
            'package_id' => 'required|exists:package_definitions,id',
            'reason'     => 'required|string|in:upgrade,downgrade,admin_action,refund',
            'note'       => 'nullable|string',
        ]);

        $admin = auth('api')->user();
        $newPackage = PackageDefinition::find($request->package_id);

        // Mevcut aktif üyeliği bul
        $currentMembership = Membership::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        $oldPackageId = $currentMembership?->package_id;

        // Aynı pakete geçiş yapılamaz
        if ($oldPackageId == $request->package_id) {
            return response()->json([
                'message' => 'Kullanıcı zaten bu pakette',
            ], 409);
        }

        // Eski üyeliği pasif yap
        if ($currentMembership) {
            $currentMembership->update([
                'is_active'  => false,
                'expires_at' => now(),
            ]);
        }

        // Yeni üyelik oluştur
        $newMembership = Membership::create([
            'user_id'    => $userId,
            'package_id' => $request->package_id,
            'starts_at'  => now(),
            'is_active'  => true,
        ]);

        // Geçmişe kaydet
        MembershipHistory::create([
            'membership_id'      => $newMembership->id,
            'user_id'            => $userId,
            'previous_package_id' => $oldPackageId,
            'new_package_id'     => $request->package_id,
            'change_reason'      => $request->reason,
            'processed_by'       => $admin->id,
            'description'        => $request->note,
        ]);

        return response()->json([
            'message'    => "Paket '{$newPackage->display_name}' olarak değiştirildi",
            'membership' => $newMembership->load('package'),
        ]);
    }

    // =============================================
    // KULLANICININ ÜYELİK GEÇMİŞİ
    // GET /api/admin/users/{userId}/membership-history
    // =============================================
    public function history($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'Kullanıcı bulunamadı',
            ], 404);
        }

        $history = MembershipHistory::where('user_id', $userId)
            ->with(['previousPackage', 'newPackage', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user_id' => $userId,
            'history' => $history,
            'count'   => $history->count(),
        ]);
    }
}