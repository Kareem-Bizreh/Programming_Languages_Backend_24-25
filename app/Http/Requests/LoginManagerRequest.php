<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginManagerRequest extends FormRequest
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
            'name' => 'required|exists:managers,name|max:40|string',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.exists' => 'name does not exist in our records.',
            'name.max' => 'name must be at most 20 characters long.',
            'name.string' => 'name must be a valid string.',

            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least 8 characters long.',
        ];
    }
}