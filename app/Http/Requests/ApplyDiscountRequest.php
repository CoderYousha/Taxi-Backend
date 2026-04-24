<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:discounts,code',
            'userId' => 'required|integer|exists:users,id',
            'requestId' => 'required|integer|exists:requests,id',
            'originalPrice' => 'required|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الخصم مطلوب',
            'code.exists' => 'كود الخصم غير صالح',
            'userId.required' => 'معرف المستخدم مطلوب',
            'requestId.required' => 'معرف الطلب مطلوب',
            'originalPrice.required' => 'السعر الأصلي مطلوب'
        ];
    }
}
