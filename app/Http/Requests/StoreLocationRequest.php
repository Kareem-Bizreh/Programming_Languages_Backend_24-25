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
            'name' => 'required|string|unique:locations,name|max:255',
            'location' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'notes' => 'string|max:500',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.unique' => 'The name has already been taken. Please choose a different name.',
            'name.max' => 'The name cannot exceed 255 characters.',

            'location.required' => 'The location field is required.',
            'location.string' => 'The location must be a valid string.',
            'location.max' => 'The location cannot exceed 255 characters.',

            'street.required' => 'The street field is required.',
            'street.string' => 'The street must be a valid string.',
            'street.max' => 'The street cannot exceed 255 characters.',

            'notes.string' => 'The notes must be a valid string.',
            'notes.max' => 'The notes cannot exceed 500 characters.',
        ];
    }
}
