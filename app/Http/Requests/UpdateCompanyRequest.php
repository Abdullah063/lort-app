<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'business_name' => 'sometimes|string|max:200',
            'position'      => 'sometimes|nullable|string|max:100',
            'sector'        => 'sometimes|nullable|string|max:100',
            'country'       => 'sometimes|nullable|string|max:100',
            'city'          => 'sometimes|nullable|string|max:100',
            'address'       => 'sometimes|nullable|string',
            'latitude'      => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'     => 'sometimes|nullable|numeric|between:-180,180',
        ];
    }
}
