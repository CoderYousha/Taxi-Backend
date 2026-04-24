<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:10',
            'password' => 'required|string|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Phone number required',
            'number.max' => 'The phone number must not exceed 10 digits.',
            'password.required' => 'Password required',
            'password.min' => 'The password must be at least 6 characters.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'Failed to validate data',
            'errors' => $validator->errors()
        ], 422));
    }
}
