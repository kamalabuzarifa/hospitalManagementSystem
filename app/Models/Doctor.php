<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model


{
    use HasFactory;

    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'dashboard_notifications' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'specialization',
        'start_time',
        'end_time',
        'working_hours',
        'bio',
        'avatar_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_profile_id', 'id');
    }
    public function patients()
    {
        return $this->hasManyThrough(
            Patient::class,
            Appointment::class,
            'doctor_profile_id', // Foreign key on appointments table...
            'id',        // Foreign key on patients table...
            'id',        // Local key on doctors table...
            'patient_id' // Local key on appointments table...
        )->distinct();
    }
}
