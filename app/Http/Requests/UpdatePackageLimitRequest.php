<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageLimitRequest extends FormRequest
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
            'limit_name'  => 'sometimes|string|max:100',
            'limit_value' => 'sometimes|integer|min:-1',
            'period'      => 'sometimes|string|in:daily,weekly,monthly,total',
            'is_active'   => 'sometimes|boolean',
        ];
    }
}
