<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'note',
        'created_at',
    ];
    
        public function doctor()
        {
            return $this->belongsTo(Doctor::class);
        }
    
        public function patient()
        {
            return $this->belongsTo(Patient::class);
        }
}
