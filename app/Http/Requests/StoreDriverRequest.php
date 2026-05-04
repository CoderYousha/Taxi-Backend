<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CarType;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // المستخدم
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'number' => 'required|string|unique:users,number|max:20',

            // CarType
            'CarTypeId'=>'required|numeric',

            // Driver
            'image' => 'required|max:255',
            'IDImage' => 'required|max:255',
            'carNumber' => 'required|string|max:255|unique:drivers,carNumber',
            'insurance' => 'required|string|max:255',
            'mechanics' => 'required|string|max:255',
            'typeCar' => 'required|string',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors()
        ], 422));
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'الاسم الأول مطلوب',
            'lastName.required' => 'الاسم الأخير مطلوب',
            'number.required' => 'رقم الهاتف مطلوب',
            'number.unique' => 'رقم الهاتف موجود مسبقاً',
            'carNumber.unique' => 'رقم السيارة موجود مسبقاً',
            'insurance.required' => 'التأمين مطلوب',
            'mechanics.required' => 'الميكانيك مطلوب',
            'typeCar.required' => 'نوغ السيارة مطلوب',
            'CarTypeId.required'=>'نوع التسعير مطلوب'
        ];
    }
}
