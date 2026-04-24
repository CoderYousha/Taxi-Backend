<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
       return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? 'NULL';

        return [
            'firstName' => 'sometimes|string|max:255',
            'lastName' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:6',
            'banned' => 'sometimes|boolean',
            'roll' => 'sometimes|in:Admin,Driver,Customer',
            'expireDate' => 'nullable|date'
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.max' => 'The first name is too long',
            'lastName.max' => 'The last name is too long',
            'password.min' => 'The password must be at least 6 characters',
            'roll.in' => 'The role must be either Admin, Driver or Customer'
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'You do not have permission to update this user'
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
