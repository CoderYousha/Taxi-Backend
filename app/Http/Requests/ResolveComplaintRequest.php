<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Complaint;

class ResolveComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:' . Complaint::STATUS_RESOLVED,
            'cause' => 'required|string|in:warning,temporary_ban,permanent_ban,dismissed',
            'action_note' => 'nullable|string|max:500',
            'penalty_days' => 'required_if:cause,temporary_ban|integer|min:1|max:30'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'الحالة مطلوبة',
            'status.in' => 'الحالة غير صحيحة',
            'cause.required' => 'سبب الإجراء مطلوب',
            'cause.in' => 'سبب الإجراء غير صحيح',
            'penalty_days.required_if' => 'عدد أيام الحظر مطلوب',
        ];
    }
}
