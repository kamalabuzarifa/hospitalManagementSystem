<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model

{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'prescription_code',
        'p_date',
        'medicine',
        'dosage',
        'duration',
        'notes',
        'dispensed_at',
        'dispensed_by_user_id',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // علاقة الأدوية مع الوصفة
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'prescription_medicine', 'prescription_id', 'medicine_id')
            ->withPivot(['quantity', 'dosage', 'duration', 'notes'])
            ->withTimestamps();
    }
}
