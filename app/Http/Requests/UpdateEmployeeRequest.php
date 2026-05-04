<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Override;

class UpdateEmployeeRequest extends FormRequest
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
            'id'=>'required',
            'firstName' => 'required|string',
            'lastName' => 'required|string'
        ];
    }
    #[Override]
    protected function failedAuthorization()
    {
        abort(403, 'You Do Not Have Permissions For This');
    }

    #[Override]
    public function messages()
    {
        return [
            'id.required'=>'معرف الموظف مطلوب',
            'firstName.required' => 'الاسم الأول مطلوب',
            'lastName.required' => 'الاسم الأخير مطلوب',
        ];
    }

    #[Override]
    protected function failedValidation(Validator $validator)
    {
        $errors = implode(', ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'Failed to validate data',
            'errors' => $errors
        ], 422));
    }
}
