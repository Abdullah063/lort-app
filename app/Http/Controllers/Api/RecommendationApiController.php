<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Jobs\SendRecommendationJob;
use App\Mail\RecommendationMail;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RecommendationApiController extends Controller
{
    public function sendBulk(Request $request): JsonResponse
    {
        $request->validate([
            'users'            => 'required|array|min:1|max:100',
            'users.*.name'     => 'required|string|max:100',
            'users.*.email'    => 'required|email',
            'users.*.website'  => 'nullable|url|max:500',
        ]);

        $delay = 60;

        foreach ($request->users as $userData) {
            $profile = [
                'company'   => $userData['company'] ?? '',
                'position'  => $userData['position'] ?? '',
                'sector'    => $userData['sector'] ?? '',
                'city'      => $userData['city'] ?? '',
                'goals'     => is_string($userData['goals'] ?? '') ? json_decode($userData['goals'], true) ?? [] : ($userData['goals'] ?? []),
                'interests' => is_string($userData['interests'] ?? '') ? json_decode($userData['interests'], true) ?? [] : ($userData['interests'] ?? []),
                'website'   => $userData['website'] ?? '',
            ];

            // Kullanıcının kayıtlı dilini bul
            $user = User::where('email', $userData['email'])->first();
            $lang = $user?->entrepreneurProfile?->preferred_language ?? 'en';

            SendRecommendationJob::dispatch($profile, $userData['email'], $userData['name'], $lang)
                ->delay(now()->addSeconds($delay));

            $delay += 10;
        }

        return response()->json([
            'success' => true,
            'message' => 'Öneri mailleri kuyruğa eklendi.',
            'count'   => count($request->users),
        ]);
    }

    public function generate(StoreProfileRequest $request): JsonResponse
    {
        $request->validated();

        $lang = app()->getLocale();

        $profile = [
            'company'   => $request->company ?? '',
            'position'  => $request->position ?? '',
            'sector'    => $request->sector ?? '',
            'city'      => $request->city ?? '',
            'goals'     => $request->goals ?? [],
            'interests' => $request->interests ?? [],
            'website'   => $request->website ?? '',
        ];

        $recommendation = RecommendationService::generate($profile, $lang);

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Öneri oluşturulamadı.',
            ], 503);
        }

        return response()->json([
            'success'        => true,
            'recommendation' => $recommendation,
        ]);
    }

    public function send(StoreProfileRequest $request): JsonResponse
    {
        $request->validated();

        // Kullanıcının kayıtlı dilini bul
        $user = User::where('email', $request->email)->first();
        $lang = $user?->entrepreneurProfile?->preferred_language ?? 'en';

        $profile = [
            'company'   => $request->company ?? '',
            'position'  => $request->position ?? '',
            'sector'    => $request->sector ?? '',
            'city'      => $request->city ?? '',
            'goals'     => $request->goals ?? [],
            'interests' => $request->interests ?? [],
            'website'   => $request->website ?? '',
        ];

        SendRecommendationJob::dispatch($profile, $request->email, $request->name, $lang);

        return response()->json([
            'success' => true,
            'message' => 'Öneri maili kuyruğa eklendi.',
        ]);
    }
}
