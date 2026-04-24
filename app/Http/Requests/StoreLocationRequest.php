<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Location;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'longitude' => 'required|numeric|between:-180,180',
            'latitude' => 'required|numeric|between:-90,90',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:' . Location::TYPE_PICKUP . ',' . Location::TYPE_DROPOFF . ',' . Location::TYPE_HOTSPOT . ',' . Location::TYPE_LANDMARK,
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'longitude.required' => 'خط الطول مطلوب',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180',
            'latitude.required' => 'خط العرض مطلوب',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90',
            'type.in' => 'نوع الموقع غير صحيح',
        ];
    }
}
