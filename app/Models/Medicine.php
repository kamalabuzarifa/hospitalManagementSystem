<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit_price',
        'expiry_date',
        'prescription_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function isLowStock()
    {
        if (!$this->inventory) {
            return false;
        }
        return $this->inventory->quantity <= $this->inventory->minimum_threshold;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }
}
