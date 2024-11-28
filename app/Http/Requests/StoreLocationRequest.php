<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'building_number' => 'required|string|max:10',
            'floor_number' => 'required|string|max:10',
            'notes' => 'string|max:500',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'city.required' => 'The city field is required.',
            'city.string' => 'The city must be a valid string.',
            'city.max' => 'The city must not exceed 255 characters.',

            'address.required' => 'The address field is required.',
            'address.string' => 'The address must be a valid string.',
            'address.max' => 'The address must not exceed 255 characters.',

            'building_number.required' => 'The building number is required.',
            'building_number.string' => 'The building number must be a valid string.',
            'building_number.max' => 'The building number must not exceed 10 characters.',

            'floor_number.required' => 'The floor number is required.',
            'floor_number.string' => 'The floor number must be a valid string.',
            'floor_number.max' => 'The floor number must not exceed 10 characters.',

            'notes.string' => 'The notes must be a valid string.',
            'notes.max' => 'The notes must not exceed 500 characters.',
        ];
    }
}
