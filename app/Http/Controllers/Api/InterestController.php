<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    // =============================================
    // TÜM İLGİ ALANLARINI LİSTELE (Seçim ekranı için)
    // GET /api/interests
    // =============================================
    public function index()
    {
        $interests = Interest::all(['id', 'name', 'description']);

        return response()->json([
            'interests' => $interests,
        ]);
    }

    // =============================================
    // KULLANICININ İLGİ ALANLARINI KAYDET/GÜNCELLE
    // POST /api/interests/select
    // =============================================
    public function select(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'interest_ids'   => 'required|array|min:1',
            'interest_ids.*' => 'exists:interests,id',
        ]);

        $user->interests()->sync($request->interest_ids);

        return response()->json([
            'message'   => 'İlgi alanları güncellendi',
            'interests' => $user->interests()->get(['interests.id', 'interests.name']),
        ]);
    }

    // =============================================
    // KULLANICININ SEÇTİĞİ İLGİ ALANLARINI GETİR
    // GET /api/interests/my
    // =============================================
    public function my()
    {
        $user = auth('api')->user();

        return response()->json([
            'interests' => $user->interests()->get(['interests.id', 'interests.name']),
        ]);
    }
}