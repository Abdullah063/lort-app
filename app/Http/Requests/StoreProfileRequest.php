<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
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

    protected function prepareForValidation(): void
    {
        $fields = ['goals', 'interests'];

        foreach ($fields as $field) {
            if (is_string($this->$field)) {
                $this->merge([
                    $field => json_decode($this->$field, true) ?? [],
                ]);
            }
        }
    }
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email'],
            'company'   => ['nullable', 'string', 'max:200'],
            'position'  => ['nullable', 'string', 'max:100'],
            'sector'    => ['nullable', 'string', 'max:100'],
            'city'      => ['nullable', 'string', 'max:100'],
            'goals'     => ['nullable', 'array'],
            'interests' => ['nullable', 'array'],
            'website'   => ['nullable', 'url', 'max:500'],
        ];
    }
}
