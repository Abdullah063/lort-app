<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartPaymentRequest extends FormRequest
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
            'package_id'  => 'required|exists:package_definitions,id',
            'period'      => 'required|in:monthly,yearly',
            'card_number' => 'required|string|size:16',
            'card_month'  => 'required|string|size:2',
            'card_year'   => 'required|string|size:2',
            'card_cvv'    => 'required|string|min:3|max:4',
            'card_holder' => 'required|string',
            'installment' => 'nullable|integer|min:0|max:12',
        ];
    }
}
