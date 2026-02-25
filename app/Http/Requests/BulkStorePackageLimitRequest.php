<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStorePackageLimitRequest extends FormRequest
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
            'limit_code' => 'required|string|max:50',
            'limit_name' => 'required|string|max:100',
            'period'     => 'required|string|in:daily,weekly,monthly,total',
            'values'     => 'required|array',
            'values.*.package_id'  => 'required|exists:package_definitions,id',
            'values.*.limit_value' => 'required|integer|min:-1',
        ];
    }
}
