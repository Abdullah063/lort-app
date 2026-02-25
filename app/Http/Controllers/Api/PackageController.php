<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\PackageDefinition;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // =============================================
    // TÜM PAKETLERİ LİSTELE
    // GET /api/admin/packages
    // =============================================
    public function index()
    {
        $packages = PackageDefinition::with(['limits', 'features'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'packages' => $packages,
            'count'    => $packages->count(),
        ]);
    }

    // =============================================
    // PAKET DETAYI
    // GET /api/admin/packages/{id}
    // =============================================
    public function show($id)
    {
        $package = PackageDefinition::with(['limits', 'features'])->find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Paket bulunamadı',
            ], 404);
        }

        return response()->json([
            'package' => $package,
        ]);
    }

    // =============================================
    // YENİ PAKET OLUŞTUR
    // POST /api/admin/packages
    // =============================================
    public function store(StorePackageRequest $request)
    {
        $request->validated();

        $package = PackageDefinition::create($request->only([
            'name', 'display_name', 'description',
            'monthly_price', 'yearly_price', 'currency',
            'is_active', 'sort_order',
        ]));

        return response()->json([
            'message' => 'Paket oluşturuldu',
            'package' => $package,
        ], 201);
    }

    // =============================================
    // PAKET GÜNCELLE
    // PUT /api/admin/packages/{id}
    // =============================================
    public function update(UpdatePackageRequest $request, $id)
    {
        $package = PackageDefinition::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Paket bulunamadı',
            ], 404);
        }

        $request->validated();

        $package->update($request->only([
            'name', 'display_name', 'description',
            'monthly_price', 'yearly_price', 'currency',
            'is_active', 'sort_order',
        ]));

        return response()->json([
            'message' => 'Paket güncellendi',
            'package' => $package->fresh(),
        ]);
    }

    // =============================================
    // PAKET SİL
    // DELETE /api/admin/packages/{id}
    // =============================================
    public function destroy($id)
    {
        $package = PackageDefinition::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Paket bulunamadı',
            ], 404);
        }

        // Free paket silinemez
        if ($package->name === 'free') {
            return response()->json([
                'message' => 'Varsayılan free paket silinemez',
            ], 403);
        }

        // Aktif üyeliği olan paket silinemez
        if ($package->memberships()->where('is_active', true)->exists()) {
            return response()->json([
                'message' => 'Bu pakete aktif üyelikleri olan kullanıcılar var. Önce üyelikleri değiştirin.',
            ], 409);
        }

        $package->delete();

        return response()->json([
            'message' => 'Paket silindi',
        ]);
    }
}