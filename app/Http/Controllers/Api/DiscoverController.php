<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Swipe;
use App\Models\UserMatch;
use App\Models\Conversation;
use App\Services\LimitService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    // =============================================
    // KEŞFET - PROFİLLERİ GETİR
    // GET /api/discover
    // =============================================
    public function index()
    {
        $user = auth('api')->user();

        $swipedIds = Swipe::where('swiper_id', $user->id)
                          ->pluck('swiped_id')
                          ->toArray();

        $profiles = User::where('id', '!=', $user->id)
                        ->where('is_active', true)
                        ->whereNotIn('id', $swipedIds)
                        ->whereHas('entrepreneurProfile')
                        ->with(['entrepreneurProfile', 'company', 'goals', 'interests', 'photoGallery'])
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();

        
        return response()->json([
            'profiles'  => $profiles,
            'count'     => $profiles->count(),
            'remaining' => [
                'daily_like'       => LimitService::remaining($user->id, 'daily_like'),
                'daily_super_like' => LimitService::remaining($user->id, 'daily_super_like'),
            ],
        ]);
    }

    // =============================================
    // KAYDIRMA (LIKE / NOPE / SUPER_LIKE)
    // POST /api/discover/swipe
    // =============================================
    public function swipe(Request $request)
    {
        $user = auth('api')->user();

        $request->validate([
            'swiped_id' => 'required|exists:users,id',
            'type'      => 'required|string|in:like,nope,super_like',
        ]);

        if ($request->swiped_id == $user->id) {
            return response()->json([
                'message' => 'Kendinizi kaydıramazsınız',
            ], 422);
        }

        $existing = Swipe::where('swiper_id', $user->id)
                         ->where('swiped_id', $request->swiped_id)
                         ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Bu kullanıcıyı zaten kaydırdınız',
            ], 409);
        }

        // Paket limiti kontrolü
        if (in_array($request->type, ['like', 'super_like'])) {
            $limitCode = $request->type === 'super_like' ? 'daily_super_like' : 'daily_like';
            $limitCheck = LimitService::check($user->id, $limitCode);

            if (!$limitCheck['allowed']) {
                return response()->json([
                    'message'   => $limitCheck['message'],
                    'remaining' => $limitCheck['remaining'],
                    'limit'     => $limitCheck['limit'],
                ], 429);
            }
        }

        $swipe = Swipe::create([
            'swiper_id' => $user->id,
            'swiped_id' => $request->swiped_id,
            'type'      => $request->type,
        ]);

        // ✅ Like/Super Like bildirimi gönder (paket kontrolü servis içinde)
        if (in_array($request->type, ['like', 'super_like'])) {
            $limitCode = $request->type === 'super_like' ? 'daily_super_like' : 'daily_like';
            LimitService::increment($user->id, $limitCode);

            // Beğenilen kişiye bildirim (paket kontrolü yapılır)
            $notifyCheck = LimitService::check($request->swiped_id, 'notify_like');
            if ($notifyCheck['allowed']) {
                NotificationService::sendDirect(
                    $request->swiped_id,
                    $request->type,
                    $request->type === 'super_like' ? 'Süper Beğeni!' : 'Yeni Beğeni!',
                    "Birisi sizi beğendi!"
                );
            }
        }

        $result = [
            'message' => 'Kaydırma kaydedildi',
            'swipe'   => $swipe,
            'matched' => false,
        ];

        if (in_array($request->type, ['like', 'super_like'])) {
            $match = $this->checkMatch($user->id, $request->swiped_id);

            if ($match) {
                $result['matched'] = true;
                $result['match'] = $match;
                $result['message'] = 'Tebrikler! Eşleştiniz!';
            }
        }

        return response()->json($result, 201);
    }

    // =============================================
    // EŞLEŞME KONTROLÜ (Private)
    // =============================================
    private function checkMatch($userId, $swipedId)
    {
        $mutual = Swipe::where('swiper_id', $swipedId)
                       ->where('swiped_id', $userId)
                       ->whereIn('type', ['like', 'super_like'])
                       ->exists();

        if (!$mutual) {
            return null;
        }

        $existingMatch = UserMatch::where(function ($q) use ($userId, $swipedId) {
            $q->where('user1_id', $userId)->where('user2_id', $swipedId);
        })->orWhere(function ($q) use ($userId, $swipedId) {
            $q->where('user1_id', $swipedId)->where('user2_id', $userId);
        })->first();

        if ($existingMatch) {
            return $existingMatch;
        }

        $match = UserMatch::create([
            'user1_id'    => min($userId, $swipedId),
            'user2_id'    => max($userId, $swipedId),
            'matched_at'  => now(),
            'is_ai_match' => false,
        ]);

        Conversation::create([
            'match_id' => $match->id,
        ]);

        //  Her iki tarafa da eşleşme bildirimi gönder
        $user1 = User::find($userId);
        $user2 = User::find($swipedId);

        NotificationService::send($userId, 'match', [
            'matched_name' => $user2->name,
        ]);

        NotificationService::send($swipedId, 'match', [
            'matched_name' => $user1->name,
        ]);

        return $match;
    }

    // =============================================
    // BEĞENENLERİM (Beni beğenenler)
    // GET /api/discover/liked-me
    // =============================================
    public function likedMe()
    {
        $user = auth('api')->user();

        //  Premium özellik kontrolü
        $limitCheck = LimitService::check($user->id, 'see_who_liked');

        if (!$limitCheck['allowed']) {
            return response()->json([
                'message' => 'Bu özellik premium paketlere özeldir.',
                'users'   => [],
                'count'   => 0,
            ], 403);
        }

        $likers = Swipe::where('swiped_id', $user->id)
                       ->whereIn('type', ['like', 'super_like'])
                       ->with('swiper.entrepreneurProfile', 'swiper.company')
                       ->orderBy('created_at', 'desc')
                       ->get()
                       ->pluck('swiper');

        return response()->json([
            'users' => $likers,
            'count' => $likers->count(),
        ]);
    }

    // =============================================
    // BEĞENDİKLERİM (Benim beğendiklerim)
    // GET /api/discover/my-likes
    // =============================================
    public function myLikes()
    {
        $user = auth('api')->user();

        $liked = Swipe::where('swiper_id', $user->id)
                      ->whereIn('type', ['like', 'super_like'])
                      ->with('swiped.entrepreneurProfile', 'swiped.company')
                      ->orderBy('created_at', 'desc')
                      ->get()
                      ->pluck('swiped');

        return response()->json([
            'users' => $liked,
            'count' => $liked->count(),
        ]);
    }
}