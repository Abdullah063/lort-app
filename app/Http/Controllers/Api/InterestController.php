<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    // =============================================
    // TÜM İLGİ ALANLARINI LİSTELE
    // GET /api/interests
    // =============================================
    public function index()
    {
        $interests = Interest::all(['id', 'name', 'description']);

        TranslationService::translateMany('interests', $interests, ['name', 'description']);

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

        $interests = $user->interests()->get(['interests.id', 'interests.name']);
        TranslationService::translateMany('interests', $interests, ['name']);

        return response()->json([
            'message'   => 'İlgi alanları güncellendi',
            'interests' => $interests,
        ]);
    }

    // =============================================
    // KULLANICININ SEÇTİĞİ İLGİ ALANLARINI GETİR
    // GET /api/interests/my
    // =============================================
    public function my()
    {
        $user = auth('api')->user();

        $interests = $user->interests()->get(['interests.id', 'interests.name']);
        TranslationService::translateMany('interests', $interests, ['name']);

        return response()->json([
            'interests' => $interests,
        ]);
    }
}