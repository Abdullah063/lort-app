<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderGalleryRequest;
use App\Http\Requests\StoreGalleryRequest;
use App\Models\PhotoGallery;
use App\Services\LimitService;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    // =============================================
    // FOTOĞRAFLARIMI LİSTELE
    // GET /api/gallery
    // =============================================
    public function index()
    {
        $user = auth('api')->user();

        $photos = $user->photoGallery()
                       ->orderBy('sort_order')
                       ->get();

        return response()->json([
            'photos'    => $photos,
            'count'     => $photos->count(),
            'remaining' => LimitService::remaining($user->id, 'gallery_limit'),
        ]);
    }

    // =============================================
    // FOTOĞRAF YÜKLE
    // POST /api/gallery
    // =============================================
    public function store(StoreGalleryRequest $request)
    {
        $user = auth('api')->user();

        // Paket limitini kontrol et
        $limitCheck = LimitService::check($user->id, 'gallery_limit');

        if (!$limitCheck['allowed']) {
            return response()->json([
                'message'   => $limitCheck['message'],
                'remaining' => $limitCheck['remaining'],
                'limit'     => $limitCheck['limit'],
            ], 403);
        }

        $request->validated();

        $lastOrder = $user->photoGallery()->max('sort_order') ?? 0;

        $photo = $user->photoGallery()->create([
            'image_url'  => $request->image_url,
            'sort_order' => $lastOrder + 1,
        ]);

        // ✅ Kullanımı artır
        LimitService::increment($user->id, 'gallery_limit');

        return response()->json([
            'message'   => 'Fotoğraf eklendi',
            'photo'     => $photo,
            'remaining' => LimitService::remaining($user->id, 'gallery_limit'),
        ], 201);
    }

    // =============================================
    // FOTOĞRAF SİRALAMASINI GÜNCELLE
    // POST /api/gallery/reorder
    // =============================================
    public function reorder(ReorderGalleryRequest $request)
    {
        $user = auth('api')->user();

        $request->validated();

        foreach ($request->photo_ids as $index => $photoId) {
            $user->photoGallery()
                 ->where('id', $photoId)
                 ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Sıralama güncellendi',
            'photos'  => $user->photoGallery()->orderBy('sort_order')->get(),
        ]);
    }

    // =============================================
    // FOTOĞRAF SİL
    // DELETE /api/gallery/{id}
    // =============================================
    public function destroy($id)
    {
        $user = auth('api')->user();

        $photo = $user->photoGallery()->find($id);

        if (!$photo) {
            return response()->json([
                'message' => 'Fotoğraf bulunamadı',
            ], 404);
        }

        $photo->delete();

        return response()->json([
            'message'   => 'Fotoğraf silindi',
            'remaining' => LimitService::remaining($user->id, 'gallery_limit'),
        ]);
    }
}