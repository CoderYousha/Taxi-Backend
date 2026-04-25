<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driverId = $this->route('id');

        return [
            'userId' => 'sometimes|integer|exists:users,id',
            'transTypeId' => 'sometimes|integer|exists:transTypes,id',
            'image' => 'sometimes|string|max:255',
            'IDImage' => 'sometimes|string|max:255',
            'carNumber' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('drivers', 'carNumber')->ignore($driverId)
            ],
            'insurance' => 'sometimes|string|max:255',
            'mechanics' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:car,motorcycle,bus,truck',
        ];
    }

    public function messages(): array
    {
        return [
            'userId.exists' => 'المستخدم غير موجود',
            'transTypeId.exists' => 'نوع التحويل غير موجود',
            'carNumber.unique' => 'رقم السيارة موجود مسبقاً',
            'type.in' => 'نوع المركبة غير صحيح',
        ];
    }
}
