<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|unique:users|max:10',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'roll' => 'sometimes|in:Customer,Driver',
            'transTypeId' => 'required_if:roll,Driver|exists:carTypes,id|nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Phone number required',
            'number.unique' => 'Phone number already registered',
            'number.max' => 'The phone number must not exceed 10 digits',
            'firstName.required' => 'First name required',
            'lastName.required' => 'Last name required',
            'password.required' => 'Password required',
            'password.min' => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'roll.in' => 'The role must be either Customer or Driver.',
            'transTypeId.required_if' => 'The car type is required for drivers.',
            'transTypeId.exists' => 'The selected car type does not exist.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'Data verification failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
