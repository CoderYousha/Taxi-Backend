<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->roll == 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'unique:users', 'regex:/^((00|\+)963|0)9[345689][0-9]{7}$/'],
            'firstName' => 'required',
            'lastName' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'number.required' => 'الرقم مطلوب',
            'number.regex'=>'الرقم غير صحيح',
            'firstName' => 'الاسم الأول مطلوب',
            'lastName' => 'الاسم الأخير مطلوب'
        ];
    }

    protected function failedValidation($validator)
    {
        $errors = implode(', ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'Failed to validate data',
            'errors' => $errors
        ], 422));
    }

    protected function failedAuthorization()
    {
        abort(403, 'ليس لديك صلاحيات لإضافة موظف جديد');
    }
}
