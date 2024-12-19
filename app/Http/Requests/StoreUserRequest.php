<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'number' => 'required|numeric|digits:10',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('validation.required', ['attribute' => __('messages.first_name')]),
            'first_name.string' => __('validation.string', ['attribute' => __('messages.first_name')]),
            'first_name.max' => __('validation.max', ['attribute' => __('messages.first_name')]),

            'last_name.required' => __('validation.required', ['attribute' => __('messages.last_name')]),
            'last_name.string' => __('validation.string', ['attribute' => __('messages.last_name')]),
            'last_name.max' => __('validation.max', ['attribute' => __('messages.last_name')]),

            'number.required' => __('validation.required', ['attribute' => __('messages.number')]),
            'number.numeric' => __('validation.numeric', ['attribute' => __('messages.number')]),
            'number.digits' => __('validation.digits', ['attribute' => __('messages.number')]),

            'email.required' => __('validation.required', ['attribute' => __('messages.email')]),
            'email.email' => __('validation.email', ['attribute' => __('messages.email')]),

            'password.required' => __('validation.required', ['attribute' => __('messages.password')]),
            'password.min' => __('validation.min', ['attribute' => __('messages.password')]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('messages.password')]),
        ];
    }
}