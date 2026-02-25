<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'category'          => 'sometimes|string|in:bireysel,kurumsal,diger',
            'profile_image_url' => 'nullable|string',
            'birth_date'        => 'nullable|date|before:today',
            'about_me'          => 'nullable|string|max:1000',
        ];
    }
}
