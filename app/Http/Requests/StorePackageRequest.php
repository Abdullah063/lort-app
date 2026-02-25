<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:50|unique:package_definitions,name',
            'display_name'  => 'required|string|max:100',
            'description'   => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price'  => 'required|numeric|min:0',
            'currency'      => 'sometimes|string|max:10',
            'is_active'     => 'sometimes|boolean',
            'sort_order'    => 'sometimes|integer',
        ];
    }
}
