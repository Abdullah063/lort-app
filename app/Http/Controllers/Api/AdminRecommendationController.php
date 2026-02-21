<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RecommendationMail;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminRecommendationController extends Controller
{
    /**
     * Tek bir kullanıcıya öneri maili gönder
     * POST /api/admin/recommendation/send/{user}
     */
    public function sendToUser(User $user): JsonResponse
    {
        $user->load(['company', 'goals', 'interests']);

        $profile = [
            'sector'    => $user->company->sector ?? '',
            'city'      => $user->company->city ?? '',
            'company'   => $user->company->business_name ?? '',
            'position'  => $user->company->position ?? '',
            'goals'     => $user->goals->pluck('name')->toArray(),
            'interests' => $user->interests->pluck('name')->toArray(),
        ];

        $lang = request()->header('Accept-Language', 'tr');

        $recommendation = RecommendationService::generate($profile, $lang);

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Öneri oluşturulamadı. API limiti dolmuş olabilir.',
            ], 503);
        }

        Mail::to($user->email)->send(new RecommendationMail($user, $recommendation, $lang));

        return response()->json([
            'success' => true,
            'message' => "{$user->name} adlı kullanıcıya öneri maili gönderildi.",
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Birden fazla kullanıcıya öneri maili gönder
     * POST /api/admin/recommendation/send-bulk
     * Body: { "user_ids": [1, 2, 3] }
     */
    public function sendBulk(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array|max:20',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $results = [];

        foreach ($users as $user) {
            $user->load(['company', 'goals', 'interests']);

            $profile = [
                'sector'    => $user->company->sector ?? '',
                'city'      => $user->company->city ?? '',
                'company'   => $user->company->business_name ?? '',
                'position'  => $user->company->position ?? '',
                'goals'     => $user->goals->pluck('name')->toArray(),
                'interests' => $user->interests->pluck('name')->toArray(),
            ];

            $lang = request()->header('Accept-Language', 'tr');
            $recommendation = RecommendationService::generate($profile, $lang);

            if ($recommendation) {
                Mail::to($user->email)->send(new RecommendationMail($user, $recommendation, $lang));
                $results[] = ['user_id' => $user->id, 'name' => $user->name, 'status' => 'sent'];
            } else {
                $results[] = ['user_id' => $user->id, 'name' => $user->name, 'status' => 'failed'];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' kullanıcıya işlem yapıldı.',
            'results' => $results,
        ]);
    }
}