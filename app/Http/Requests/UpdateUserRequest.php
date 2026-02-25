<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name'    => 'sometimes|string|max:100',
            'surname' => 'sometimes|string|max:100',
            'phone'   => 'sometimes|nullable|string|max:20|unique:users,phone,' . $userId,
            'email'   => 'sometimes|email|unique:users,email,' . $userId,
        ];
    }
}
