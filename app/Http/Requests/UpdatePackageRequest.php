<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
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
        $userId = auth('api')->id();
        return [
            'name'          => "sometimes|string|max:50|unique:package_definitions,name,{$userId}",
            'display_name'  => 'sometimes|string|max:100',
            'description'   => 'nullable|string',
            'monthly_price' => 'sometimes|numeric|min:0',
            'yearly_price'  => 'sometimes|numeric|min:0',
            'currency'      => 'sometimes|string|max:10',
            'is_active'     => 'sometimes|boolean',
            'sort_order'    => 'sometimes|integer',
        ];
    }
}
