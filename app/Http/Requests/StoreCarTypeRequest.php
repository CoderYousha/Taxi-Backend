<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\CarType;

class StoreCarTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // يمكنك تعديل حسب صلاحيات المستخدم
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:carTypes,name',
            'type' => 'required|string|in:' . CarType::TYPE_KM . ',' . CarType::TYPE_TIME,
            'price' => 'required|numeric|min:0|max:99999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم نوع السيارة مطلوب',
            'name.unique' => 'اسم نوع السيارة موجود مسبقاً',
            'type.required' => 'نوع التسعير مطلوب',
            'type.in' => 'نوع التسعير يجب أن يكون KM أو Time',
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقماً',
            'price.min' => 'السعر يجب أن يكون أكبر من أو يساوي 0',
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
