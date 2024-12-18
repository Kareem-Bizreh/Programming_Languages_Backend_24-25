<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMarketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'message' => $error,
        ], 400));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:20|unique:managers,name',
            'market_name_en' => 'required|string|max:20',
            'market_name_ar' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.unique' => 'the name already exist',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name cannot exceed 20 characters.',

            'market_name_en.required' => 'The name field is required.',
            'market_name_en.string' => 'The name must be a valid string.',
            'market_name_en.max' => 'The name cannot exceed 20 characters.',

            'market_name_ar.required' => 'The name field is required.',
            'market_name_ar.string' => 'The name must be a valid string.',
            'market_name_ar.max' => 'The name cannot exceed 20 characters.',

            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
