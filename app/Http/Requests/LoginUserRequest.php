<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            'number' => 'required|exists:users,number|numeric|digits:10',
            'password' => 'required|string|min:8',
            'fcm_token' => 'nullable|string',
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'number.required' => 'The phone number is required.',
            'number.exists' => 'The phone number does not exist in our records.',
            'number.numeric' => 'The phone number must be a valid number.',
            'number.digits' => 'The phone number must be exactly 10 digits.',

            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a valid string.',
            'password.min' => 'The password must be at least 8 characters long.',

            'fcm_token.string' => 'The FCM token must be a valid string.',
        ];
    }
}
