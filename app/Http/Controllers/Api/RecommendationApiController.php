<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RecommendationMail;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RecommendationApiController extends Controller
{
    /**
     * Öneri metni üret
     * POST /api/v1/recommendation/generate
     *
     * Body:
     * {
     *   "name": "Abdullah",
     *   "email": "test@email.com",       (opsiyonel, mail gönderilecekse)
     *   "company": "Altun Teknoloji",
     *   "position": "CEO",
     *   "sector": "Yazılım",
     *   "city": "Antep",
     *   "goals": ["Ürün Satmak", "Network Genişletmek"],
     *   "interests": ["Ekonomi Grupları", "Müzeler"]
     * }
     *
     * Headers:
     *   X-API-Key: your_secret_key
     *   Accept-Language: tr
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'company'   => 'nullable|string|max:200',
            'position'  => 'nullable|string|max:100',
            'sector'    => 'nullable|string|max:100',
            'city'      => 'nullable|string|max:100',
            'goals'     => 'nullable|array',
            'interests' => 'nullable|array',
        ]);

        $lang = $request->header('Accept-Language', 'tr');

        $profile = [
            'company'   => $request->company ?? '',
            'position'  => $request->position ?? '',
            'sector'    => $request->sector ?? '',
            'city'      => $request->city ?? '',
            'goals'     => $request->goals ?? [],
            'interests' => $request->interests ?? [],
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

    /**
     * Öneri üret + mail gönder
     * POST /api/v1/recommendation/send
     *
     * Body: (generate ile aynı + email zorunlu)
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email',
            'company'   => 'nullable|string|max:200',
            'position'  => 'nullable|string|max:100',
            'sector'    => 'nullable|string|max:100',
            'city'      => 'nullable|string|max:100',
            'goals'     => 'nullable|array',
            'interests' => 'nullable|array',
        ]);

        $lang = $request->header('Accept-Language', 'tr');

        $profile = [
            'company'   => $request->company ?? '',
            'position'  => $request->position ?? '',
            'sector'    => $request->sector ?? '',
            'city'      => $request->city ?? '',
            'goals'     => $request->goals ?? [],
            'interests' => $request->interests ?? [],
        ];

        $recommendation = RecommendationService::generate($profile, $lang);

        if (!$recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Öneri oluşturulamadı. Lütfen daha sonra tekrar deneyin.',
            ], 503);
        }

        // Mail göndermek için geçici user objesi
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;

        Mail::to($request->email)->send(new RecommendationMail($user, $recommendation, $lang));

        $msg = match ($lang) {
            'en' => "Recommendation email sent to {$request->name}.",
            'ar' => ".تم إرسال بريد التوصية إلى {$request->name}",
            'de' => "Empfehlungs-E-Mail an {$request->name} gesendet.",
            default => "{$request->name} adresine öneri maili gönderildi.",
        };

        return response()->json([
            'success'        => true,
            'message'        => $msg,
            'recommendation' => $recommendation,
        ]);
    }
}