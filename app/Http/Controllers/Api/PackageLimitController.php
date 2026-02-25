<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkStorePackageLimitRequest;
use App\Http\Requests\StorePackageLimitRequest;
use App\Http\Requests\UpdatePackageLimitRequest;
use App\Models\PackageLimit;
use App\Models\PackageDefinition;
use Illuminate\Http\Request;

class PackageLimitController extends Controller
{
    // =============================================
    // BİR PAKETİN TÜM LİMİTLERİNİ GETİR
    // GET /api/admin/packages/{packageId}/limits
    // =============================================
    public function index($packageId)
    {
        $package = PackageDefinition::find($packageId);

        if (!$package) {
            return response()->json([
                'message' => 'Paket bulunamadı',
            ], 404);
        }

        $limits = PackageLimit::where('package_id', $packageId)
            ->orderBy('limit_code')
            ->get();

        return response()->json([
            'package' => $package->display_name,
            'limits'  => $limits,
            'count'   => $limits->count(),
        ]);
    }

    // =============================================
    // TÜM PAKETLERİN LİMİTLERİNİ KARŞILAŞTIRMALI GETİR
    // GET /api/admin/limits/compare
    // =============================================
    public function compare()
    {
        $packages = PackageDefinition::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $result = [];

        foreach ($packages as $package) {
            $limits = PackageLimit::where('package_id', $package->id)
                ->get()
                ->keyBy('limit_code');

            $result[] = [
                'package_id'   => $package->id,
                'package_name' => $package->display_name,
                'limits'       => $limits,
            ];
        }

        return response()->json([
            'comparison' => $result,
        ]);
    }

    // =============================================
    // LİMİT EKLE
    // POST /api/admin/packages/{packageId}/limits
    // =============================================
    public function store(StorePackageLimitRequest $request, $packageId)
    {
        $package = PackageDefinition::find($packageId);

        if (!$package) {
            return response()->json([
                'message' => 'Paket bulunamadı',
            ], 404);
        }

        $request->validated();

        // Aynı pakette aynı limit kodu var mı
        $exists = PackageLimit::where('package_id', $packageId)
            ->where('limit_code', $request->limit_code)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Bu limit kodu zaten bu pakette tanımlı',
            ], 409);
        }

        $limit = PackageLimit::create([
            'package_id'  => $packageId,
            'limit_code'  => $request->limit_code,
            'limit_name'  => $request->limit_name,
            'limit_value' => $request->limit_value,
            'period'      => $request->period,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Limit eklendi',
            'limit'   => $limit,
        ], 201);
    }

    // =============================================
    // LİMİT GÜNCELLE
    // PUT /api/admin/limits/{id}
    // =============================================
    public function update(UpdatePackageLimitRequest $request, $id)
    {
        $limit = PackageLimit::find($id);

        if (!$limit) {
            return response()->json([
                'message' => 'Limit bulunamadı',
            ], 404);
        }

        $request->validated();

        $limit->update($request->only([
            'limit_name', 'limit_value', 'period', 'is_active',
        ]));

        return response()->json([
            'message' => 'Limit güncellendi',
            'limit'   => $limit->fresh(),
        ]);
    }

    // =============================================
    // LİMİT SİL
    // DELETE /api/admin/limits/{id}
    // =============================================
    public function destroy($id)
    {
        $limit = PackageLimit::find($id);

        if (!$limit) {
            return response()->json([
                'message' => 'Limit bulunamadı',
            ], 404);
        }

        $limit->delete();

        return response()->json([
            'message' => 'Limit silindi',
        ]);
    }

    // =============================================
    // BİR LİMİTİ TÜM PAKETLERE TOPLU EKLE
    // POST /api/admin/limits/bulk
    // =============================================
    public function bulkStore(BulkStorePackageLimitRequest $request)
    {
        $request->validated();

        $created = [];

        foreach ($request->values as $item) {
            $exists = PackageLimit::where('package_id', $item['package_id'])
                ->where('limit_code', $request->limit_code)
                ->exists();

            if ($exists) {
                continue;
            }

            $created[] = PackageLimit::create([
                'package_id'  => $item['package_id'],
                'limit_code'  => $request->limit_code,
                'limit_name'  => $request->limit_name,
                'limit_value' => $item['limit_value'],
                'period'      => $request->period,
                'is_active'   => true,
            ]);
        }

        return response()->json([
            'message' => count($created) . ' limit eklendi',
            'limits'  => $created,
        ], 201);
    }
}