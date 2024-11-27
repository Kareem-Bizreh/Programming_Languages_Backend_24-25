<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'number' => 'required|numeric|digits:10',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name field is required.',
            'first_name.string' => 'The first name must be a valid string.',
            'first_name.max' => 'The first name cannot exceed 20 characters.',

            'last_name.required' => 'The last name field is required.',
            'last_name.string' => 'The last name must be a valid string.',
            'last_name.max' => 'The last name cannot exceed 20 characters.',

            'number.required' => 'The number field is required.',
            'number.numeric' => 'The input must contain only digits.',
            'number.regex' => 'The input must be exactly 10 digits.',
        ];
    }
}
