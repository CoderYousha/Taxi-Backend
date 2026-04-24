<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverUpdateLocation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user() == null) return false;
        $roll = $this->user()->roll;
        return $roll == 'Driver';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'longitude' => 'required|numeric|between:-180,180',
            'latitude'  => 'required|numeric|between:-90,90',
        ];
    }

    public function messages(): array
    {
        return [
            'longitude.required' => 'Longitude is required',
            'longitude.numeric'  => 'Longitude must be a number',
            'longitude.between'  => 'Longitude must be between -180 and 180',

            'latitude.required'  => 'Latitude is required',
            'latitude.numeric'   => 'Latitude must be a number',
            'latitude.between'   => 'Latitude must be between -90 and 90',
        ];
    }

    protected function failedAuthorization()
    {
        abort(403, 'You are not allowed to do this.');
    }

    protected function failedValidation($validator)
    {
        $errors = implode(', ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'state' => false,
            'message' => 'Failed to validate data',
            'errors' => $errors
        ], 422));
    }
}
