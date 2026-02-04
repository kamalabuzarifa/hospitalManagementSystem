<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'has_allergies',
        'allergy_details',
        'has_chronic',
        'chronic_details',
        'last_visit',
        'medical_history',
    ];

    protected $casts = [
        'dob' => 'date',
        'last_visit' => 'date',
        'has_allergies' => 'boolean',
        'has_chronic' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (self $patient): void {
            if (!empty($patient->patient_code)) {
                return;
            }

            $patient->patient_code = 'P' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
            $patient->saveQuietly();
        });
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getAgeAttribute()
    {
        return $this->dob ? $this->dob->age : null;
    }
}
