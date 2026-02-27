<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $lang = app()->getLocale();

    $categories = Cache::remember("categories_{$lang}", 3600, function () use ($lang) {
        return Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) use ($lang) {
                $translation = Translation::where('table_name', 'categories')
                    ->where('record_id', $category->id)
                    ->where('field_name', 'name')
                    ->where('language_code', $lang)
                    ->first();

                return [
                    'id'   => $category->id,
                    'code' => $category->code,
                    'name' => $translation?->value ?? $category->name,
                ];
            });
    });

    return response()->json([
        'categories' => $categories,
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
