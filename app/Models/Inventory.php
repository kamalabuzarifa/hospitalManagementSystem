<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'quantity',
        'minimum_threshold',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->minimum_threshold;
    }
}
