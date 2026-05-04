<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RequestModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carTypeId' => 'required|integer|exists:carTypes,id',
            'type' => 'required|in:' . RequestModel::TYPE_SCHEDULE . ',' . RequestModel::TYPE_IMMEDIATE,
            'startLocationLongitude' => 'required|numeric|between:-180,180',
            'startLocationLatitude' => 'required|numeric|between:-90,90',
            'destLocationLongitude' => 'required|numeric|between:-180,180',
            'destLocationLatitude' => 'required|numeric|between:-90,90',
            'requestDate' => 'nullable|date|after:now',
            'locationDesc' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'carTypeId.required' => 'نوع السيارة مطلوب',

            'type.required' => 'نوع الطلب مطلوب',
            'type.in' => 'نوع الطلب غير صحيح',

            'startLocationLongitude.required' => 'موقع البداية مطلوب',
            'startLocationLongitude.between' => 'قيمة الموقع غير صحيحة',
            'startLocationLongitude.numeric' => 'قيمة الموقع يجب أن تكون رقم',
    
            'startLocationLatitude.required' => 'موقع البداية مطلوب',
            'startLocationLatitude.between' => 'قيمة الموقع غير صحيحة',
            'startLocationLatitude.numeric' => 'قيمة الموقع يجب أن تكون رقم',
                   
            'destLocationLongitude.required' => 'موقع الوجهة مطلوب',
            'destLocationLongitude.between' => 'قيمة الموقع غير صحيحة',
            'destLocationLongitude.numeric' => 'قيمة الموقع يجب أن تكون رقم',
      
            'destLocationLatitude.required' => 'موقع الوجهة مطلوب',
            'destLocationLatitude.between' => 'قيمة الموقع غير صحيحة',
            'destLocationLatitude.numeric' => 'قيمة الموقع يجب أن تكون رقم',

            'requestDate.after' => 'تاريخ الطلب يجب أن يكون بعد الوقت الحالي',
            'requestDate.date' => 'تاريخ الطلب يجب أن يكون تاريخ'
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

    protected function failedAuthorization()
    {
        abort(403, 'You are not allowed to do this.');
    }
}
