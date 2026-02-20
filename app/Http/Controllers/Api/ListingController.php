<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\LimitService;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    // =============================================
    // İLANLARIMI LİSTELE
    // GET /api/listings
    // =============================================
    public function index()
    {
        $user = auth('api')->user();

        $listings = $user->listings()
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json([
            'listings'  => $listings,
            'count'     => $listings->count(),
            'remaining' => LimitService::remaining($user->id, 'listing_limit'),
        ]);
    }

    // =============================================
    // İLAN OLUŞTUR
    // POST /api/listings
    // =============================================
    public function store(Request $request)
    {
        $user = auth('api')->user();

        //  Paket limiti kontrolü
        $limitCheck = LimitService::check($user->id, 'listing_limit');

        if (!$limitCheck['allowed']) {
            return response()->json([
                'message'   => $limitCheck['message'],
                'remaining' => $limitCheck['remaining'],
                'limit'     => $limitCheck['limit'],
            ], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        $listing = $user->listings()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => 'active',
        ]);

        // ✅ Kullanımı artır
        LimitService::increment($user->id, 'listing_limit');

        return response()->json([
            'message'   => 'İlan oluşturuldu',
            'listing'   => $listing,
            'remaining' => LimitService::remaining($user->id, 'listing_limit'),
        ], 201);
    }

    // =============================================
    // İLAN DETAYI
    // GET /api/listings/{id}
    // =============================================
    public function show($id)
    {
        $listing = Listing::with('user.company', 'user.entrepreneurProfile')->find($id);

        if (!$listing) {
            return response()->json([
                'message' => 'İlan bulunamadı',
            ], 404);
        }

        return response()->json([
            'listing' => $listing,
        ]);
    }

    // =============================================
    // İLAN GÜNCELLE
    // PUT /api/listings/{id}
    // =============================================
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        $listing = $user->listings()->find($id);

        if (!$listing) {
            return response()->json([
                'message' => 'İlan bulunamadı veya size ait değil',
            ], 404);
        }

        $request->validate([
            'title'       => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'status'      => 'sometimes|string|in:active,inactive',
        ]);

        $listing->update($request->only(['title', 'description', 'status']));

        return response()->json([
            'message' => 'İlan güncellendi',
            'listing' => $listing->fresh(),
        ]);
    }

    // =============================================
    // İLAN SİL
    // DELETE /api/listings/{id}
    // =============================================
    public function destroy($id)
    {
        $user = auth('api')->user();
        $listing = $user->listings()->find($id);

        if (!$listing) {
            return response()->json([
                'message' => 'İlan bulunamadı veya size ait değil',
            ], 404);
        }

        $listing->delete();

        return response()->json([
            'message'   => 'İlan silindi',
            'remaining' => LimitService::remaining($user->id, 'listing_limit'),
        ]);
    }
}