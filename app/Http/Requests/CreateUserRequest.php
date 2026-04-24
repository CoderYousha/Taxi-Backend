<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // فقط الأدمن يمكنه إنشاء مستخدمين
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|unique:users|max:10',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'roll' => 'required|in:Admin,Driver,Customer',
            'banned' => 'sometimes',
            'expireDate' => 'nullable|date',
            'transTypeId' => 'nullable|required_if:roll,Driver|exists:carTypes,id'
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Phone number is required',
            'number.unique' => 'Phone number is already registered',
            'firstName.required' => 'First name is required',
            'lastName.required' => 'Last name is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'roll.required' => 'Role is required',
            'roll.in' => 'Role must be either Admin, Driver or Customer',
            'transTypeId.required_if' => 'Car type is required for drivers'
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
