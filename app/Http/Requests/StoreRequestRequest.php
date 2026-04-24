<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RequestModel;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => 'required|integer|exists:users,id',
            'carTypeId' => 'required|integer|exists:carTypes,id',
            'type' => 'required|in:' . RequestModel::TYPE_SCHEDULE . ',' . RequestModel::TYPE_IMMEDIATE,
            'startLocationId' => 'required|integer|exists:locations,id',
            'destLocationId' => 'required|integer|exists:locations,id',
            'requestDate' => 'required|date|after:now',
            'locationDesc' => 'nullable|string',
            'predectedCost' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'userId.required' => 'معرف المستخدم مطلوب',
            'carTypeId.required' => 'نوع السيارة مطلوب',
            'type.required' => 'نوع الطلب مطلوب',
            'type.in' => 'نوع الطلب غير صحيح',
            'startLocationId.required' => 'موقع البداية مطلوب',
            'destLocationId.required' => 'موقع الوصول مطلوب',
            'requestDate.required' => 'تاريخ الطلب مطلوب',
            'requestDate.after' => 'تاريخ الطلب يجب أن يكون بعد الوقت الحالي',
        ];
    }
}
