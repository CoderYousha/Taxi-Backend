<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Complaint;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requestId' => 'required|integer|exists:requests,id',
            'driverId' => 'required|integer|exists:drivers,id',
            'detail' => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'requestId.required' => 'رقم الرحلة مطلوب',
            'requestId.exists' => 'الرحلة غير موجودة',
            'driverId.required' => 'معرف السائق مطلوب',
            'driverId.exists' => 'السائق غير موجود',
            'detail.required' => 'تفاصيل الشكوى مطلوبة',
            'detail.min' => 'يجب أن لا تقل تفاصيل الشكوى عن 10 أحرف',
            'detail.max' => 'يجب أن لا تزيد تفاصيل الشكوى عن 1000 حرف',
        ];
    }
}
