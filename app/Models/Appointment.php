<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'doctor_profile_id',
        'date',
        'time',
        'scheduled_at',
        'status',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'string',
        'scheduled_at' => 'datetime',
    ];

    public function patient()
    {
        // Returns the related patient, including gender field
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_profile_id');
    }

    public function doctorUser()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function isOverdue()
    {
        $appointmentDateTime = $this->scheduled_at
            ? $this->scheduled_at
            : ($this->date && $this->time ? Carbon::parse($this->date . ' ' . $this->time) : null);

        return $appointmentDateTime && $appointmentDateTime < now() && in_array($this->status, ['scheduled', 'pending', 'checked_in']);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'scheduled' => 'في انتظار التأكيد',
            'checked_in' => 'حاضر',
            'cancelled' => 'ملغي',
            'completed' => 'مكتمل',
            default => $this->status,
        };
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }
}
