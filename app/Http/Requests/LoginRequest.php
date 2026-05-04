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
            'number' => ['required', 'regex:/^((00|\+)963|0)9[345689][0-9]{7}$/'],
            'password' => 'required|string|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Phone number required',
            'number.regex' => 'Invalid Phone number',
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
