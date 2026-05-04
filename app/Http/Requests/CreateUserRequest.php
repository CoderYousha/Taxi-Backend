<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'unique:users', 'regex:/^((00|\+)963|0)9[345689][0-9]{7}$/'],
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Phone number is required',
            'number.regex' => 'Invalid Phone number',
            'number.unique' => 'Phone number is already registered',
            'firstName.required' => 'First name is required',
            'lastName.required' => 'Last name is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters'
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'You do not have permission to create users'
        ], 403));
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
