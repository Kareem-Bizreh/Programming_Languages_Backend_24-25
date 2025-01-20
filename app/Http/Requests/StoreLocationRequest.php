<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'name' => 'required|string|unique:locations,name|max:255',
            'location' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'notes' => 'max:500',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('messages.name')]),
            'name.unique' => __('validation.unique', ['attribute' => __('messages.name')]),
            'name.string' => __('validation.string', ['attribute' => __('messages.name')]),
            'name.max' => __('validation.max', ['attribute' => __('messages.name')]),

            'location.required' => __('validation.required', ['attribute' => __('messages.location')]),
            'location.string' => __('validation.string', ['attribute' => __('messages.location')]),
            'location.max' => __('validation.max', ['attribute' => __('messages.location')]),

            'street.required' => __('validation.required', ['attribute' => __('messages.street')]),
            'street.string' => __('validation.string', ['attribute' => __('messages.street')]),
            'street.max' => __('validation.max', ['attribute' => __('messages.street')]),

            'notes.max' => __('validation.max', ['attribute' => __('messages.notes')]),
        ];
    }
}