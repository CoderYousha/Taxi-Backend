<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Discount;
use Illuminate\Validation\Rule;

class UpdateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountId = $this->route('id');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('discounts', 'code')->ignore($discountId)
            ],
            'amount' => 'sometimes|numeric|min:0|max:99999999.99',
            'type' => 'sometimes|string|in:' . Discount::TYPE_PERCENTAGE . ',' . Discount::TYPE_FIXED,
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'كود الخصم موجود مسبقاً',
            'code.max' => 'كود الخصم يجب ألا يزيد عن 255 حرف',
            'amount.numeric' => 'قيمة الخصم يجب أن تكون رقماً',
            'amount.min' => 'قيمة الخصم يجب أن تكون أكبر من أو تساوي 0',
            'type.in' => 'نوع الخصم يجب أن يكون Percentage أو Fixed',
        ];
    }
}
