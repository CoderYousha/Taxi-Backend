<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'You do not have permission to delete this user'
        ], 403));
    }
}
