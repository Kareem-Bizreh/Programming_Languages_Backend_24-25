<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
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
            'name_en' => 'required|max:255',
            'name_ar' => 'required|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'description_en' => 'nullable|max:1000',
            'description_ar' => 'nullable|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name_en.required' => 'The product name is required.',
            'name_en.max' => 'The product name must not exceed 255 characters.',

            'name_ar.required' => 'The product name is required.',
            'name_ar.max' => 'The product name must not exceed 255 characters.',

            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 1.',

            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.01.',

            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',

            'description_en.max' => 'The description must not exceed 1000 characters.',
            'description_ar.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}
