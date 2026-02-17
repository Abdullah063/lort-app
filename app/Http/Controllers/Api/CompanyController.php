<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // =============================================
    // ŞİRKET BİLGİLERİ OLUŞTUR
    // POST /api/company
    // =============================================
    public function store(Request $request)
    {
        $user = auth('api')->user();

        if ($user->company) {
            return response()->json([
                'message' => 'Şirket bilgileriniz zaten mevcut',
            ], 409);
        }

        $request->validate([
            'business_name' => 'required|string|max:200',
            'position'      => 'nullable|string|max:100',
            'sector'        => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ]);

        $company = $user->company()->create($request->only([
            'business_name',
            'position',
            'sector',
            'country',
            'city',
            'address',
            'latitude',
            'longitude',
        ]));

        return response()->json([
            'message' => 'Şirket bilgileri oluşturuldu',
            'company' => $company,
        ], 201);
    }

    // =============================================
    // ŞİRKET BİLGİLERİNİ GÖRÜNTÜLE
    // GET /api/company
    // =============================================
    public function show()
    {
        $user = auth('api')->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'Şirket bilgisi bulunamadı',
            ], 404);
        }

        return response()->json([
            'company' => $company,
        ]);
    }

    // =============================================
    // ŞİRKET BİLGİLERİNİ GÜNCELLE
    // PUT /api/company
    // =============================================
    public function update(Request $request)
    {
        $user = auth('api')->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'Önce şirket bilgilerini oluşturmalısınız',
            ], 404);
        }

        $validated = $request->validate([
            'business_name' => 'sometimes|string|max:200',
            'position'      => 'sometimes|nullable|string|max:100',
            'sector'        => 'sometimes|nullable|string|max:100',
            'country'       => 'sometimes|nullable|string|max:100',
            'city'          => 'sometimes|nullable|string|max:100',
            'address'       => 'sometimes|nullable|string',
            'latitude'      => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'     => 'sometimes|nullable|numeric|between:-180,180',
        ]);

        $company->update($validated);

        return response()->json([
            'message' => 'Şirket bilgileri güncellendi',
            'company' => $company->fresh(),
            'debug_validated' => $validated,
        ]);
    }
}
