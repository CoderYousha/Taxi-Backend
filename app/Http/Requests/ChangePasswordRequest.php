<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'oldPassword'=>'required',
            'newPassword'=>'required|min:6',
            'confirmNewPassword' => 'required|same:newPassword'
        ];
    }

    
    protected function failedAuthorization()
    {
        abort(403, 'ليس لديك صلاحيات لإضافة موظف جديد');
    }

    
    public function messages()
    {
        return [
            'oldPassword.required'=>'كلمة السر القديمة مطلوبة',
            'newPassword.required'=>'كلمة السر الجديدة مطلوبة',
            'newPassword.min'=>'يجب أن تتكون كلمة السر من 6 محارف على الأقل',
            'confirmNewPassword.required'=>'تأكيد كلمة السر مطلوب',
            'confirmNewPassword.same'=>'كلمتا السر غير متطابقتان'
        ];
    }

    
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
