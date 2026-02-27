<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectInterestRequest;
use App\Models\Interest;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InterestController extends Controller
{
    // =============================================
    // TÜM İLGİ ALANLARINI LİSTELE
    // GET /api/interests
    // =============================================
    public function index()
    {
        $lang = app()->getLocale();

        $interests = Cache::remember("interests_{$lang}", 3600, function () {
            $interests = Interest::all(['id', 'name', 'description']);
            TranslationService::translateMany('interests', $interests, ['name', 'description']);
            return $interests;
        });

        return response()->json([
            'interests' => $interests,
        ]);
    }

    // =============================================
    // KULLANICININ İLGİ ALANLARINI KAYDET/GÜNCELLE
    // POST /api/interests/select
    // =============================================
    public function select(SelectInterestRequest $request)
    {
        $user = auth('api')->user();

        $request->validated();

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
