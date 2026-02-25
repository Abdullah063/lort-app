<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectGoalRequest;
use App\Models\Goal;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    // =============================================
    // TÜM HEDEFLERİ LİSTELE
    // GET /api/goals
    // =============================================
    public function index()
    {
        $goals = Goal::all(['id', 'name', 'description']);

        // Çoklu dil desteği
        TranslationService::translateMany('goals', $goals, ['name', 'description']);

        return response()->json([
            'goals' => $goals,
        ]);
    }

    // =============================================
    // KULLANICININ HEDEFLERİNİ KAYDET/GÜNCELLE
    // POST /api/goals/select
    // =============================================
    public function select(SelectGoalRequest $request)
    {
        $user = auth('api')->user();

        $request->validated();

        $user->goals()->sync($request->goal_ids);

        $goals = $user->goals()->get(['goals.id', 'goals.name']);
        TranslationService::translateMany('goals', $goals, ['name']);

        return response()->json([
            'message' => 'Hedefler güncellendi',
            'goals'   => $goals,
        ]);
    }

    // =============================================
    // KULLANICININ SEÇTİĞİ HEDEFLERİ GETİR
    // GET /api/goals/my
    // =============================================
    public function my()
    {
        $user = auth('api')->user();

        $goals = $user->goals()->get(['goals.id', 'goals.name']);
        TranslationService::translateMany('goals', $goals, ['name']);

        return response()->json([
            'goals' => $goals,
        ]);
    }
}