<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,checked_in,cancelled,completed,pending',
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient is required',
            'patient_id.exists' => 'Patient not found',
            'date.required' => 'Date is required',
            'date.after_or_equal' => 'Date must be today or later',
            'time.required' => 'Time is required',
            'time.date_format' => 'Time format must be HH:mm',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status value',
        ];
    }
}
