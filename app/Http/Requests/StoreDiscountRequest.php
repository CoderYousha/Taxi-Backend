<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Discount;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:discounts,code',
            'amount' => 'required|numeric|min:0|max:99999999.99',
            'type' => 'required|string|in:' . Discount::TYPE_PERCENTAGE . ',' . Discount::TYPE_FIXED,
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الخصم مطلوب',
            'code.unique' => 'كود الخصم موجود مسبقاً',
            'code.max' => 'كود الخصم يجب ألا يزيد عن 255 حرف',
            'amount.required' => 'قيمة الخصم مطلوبة',
            'amount.numeric' => 'قيمة الخصم يجب أن تكون رقماً',
            'amount.min' => 'قيمة الخصم يجب أن تكون أكبر من أو تساوي 0',
            'type.required' => 'نوع الخصم مطلوب',
            'type.in' => 'نوع الخصم يجب أن يكون Percentage أو Fixed',
        ];
    }
}
