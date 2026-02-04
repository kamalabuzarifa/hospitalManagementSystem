<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:30|regex:/^[0-9\-\+\s()]*$/',
            'address' => 'nullable|string|max:1000',
            'has_allergies' => 'nullable|boolean',
            'allergy_details' => 'nullable|string|max:2000',
            'has_chronic' => 'nullable|boolean',
            'chronic_details' => 'nullable|string|max:2000',
            'last_visit' => 'nullable|date',
            'medical_history' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('messages.name_required'),
            'dob.before' => __('messages.dob_must_be_in_past'),
            'gender.in' => __('messages.gender_invalid'),
            'phone.regex' => __('messages.phone_invalid'),
        ];
    }
}
