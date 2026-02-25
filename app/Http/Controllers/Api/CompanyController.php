<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // =============================================
    // ŞİRKET BİLGİLERİ OLUŞTUR
    // POST /api/company
    // =============================================
    public function store(StoreCompanyRequest $request)
    {
        $user = auth('api')->user();

        if ($user->company) {
            return response()->json([
                'message' => 'Şirket bilgileriniz zaten mevcut',
            ], 409);
        }

        $request->validated();

        $company = $user->company()->create($request->validated());

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
    public function update(UpdateCompanyRequest $request)
    {
        $user = auth('api')->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'Önce şirket bilgilerini oluşturmalısınız',
            ], 404);
        }

        $validated = $request->validated();

        $company->update($validated);

        return response()->json([
            'message' => 'Şirket bilgileri güncellendi',
            'company' => $company->fresh(),
            'debug_validated' => $validated,
        ]);
    }
}
