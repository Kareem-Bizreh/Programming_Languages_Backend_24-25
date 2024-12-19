<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $lang = $this->header('Accept-Language', 'en');
        app()->setLocale($lang);
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
            'number' => 'required|exists:users,number|numeric|digits:10',
            'password' => 'required|min:8',
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'number.required' => __('validation.required', ['attribute' => __('messages.number')]),
            'number.exists' => __('validation.exists', ['attribute' => __('messages.number')]),
            'number.numeric' => __('validation.numeric', ['attribute' => __('messages.number')]),
            'number.digits' => __('validation.digits', ['attribute' => __('messages.number')]),

            'password.required' => __('validation.required', ['attribute' => __('messages.password')]),
            'password.min' => __('validation.min', ['attribute' => __('messages.password')]),
        ];
    }
}