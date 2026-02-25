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

        $lang = $request->header('Accept-Language', 'en');
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

            SendRecommendationJob::dispatch($profile, $userData['email'], $userData['name'], $lang)
                ->delay(now()->addSeconds($delay));

            $delay += 10;
        }

        $count = count($request->users);

        $msg = match ($lang) {
            'en' => "{$count} recommendation emails queued.",
            'ar' => ".تم وضع {$count} رسائل توصية في قائمة الانتظار",
            'de' => "{$count} Empfehlungs-E-Mails in Warteschlange.",
            default => "{$count} öneri maili kuyruğa eklendi.",
        };

        return response()->json([
            'success' => true,
            'message' => $msg,
            'count'   => $count,
        ]);
    }

    public function generate(StoreProfileRequest $request): JsonResponse
    {
        $request->validated();

        $lang = $request->header('Accept-Language', 'en');

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
                'message' => 'Öneri oluşturulamadı. Lütfen daha sonra tekrar deneyin.',
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

        $lang = $request->header('Accept-Language', 'en');

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

        $msg = match ($lang) {
            'tr' => "{$request->name} adresine öneri maili kısa süre içinde gönderilecek.",
            'en' => "Recommendation email will be sent to {$request->name} shortly.",
            'ar' => ".سيتم إرسال بريد التوصية إلى {$request->name} قريبا",
            'de' => "Empfehlungs-E-Mail wird in Kürze an {$request->name} gesendet.",
            default => "Recommendation email will be sent to {$request->name} shortly.",
        };

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }
}
