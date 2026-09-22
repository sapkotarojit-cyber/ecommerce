<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'region',
        'address',
        'landmark',
        'address_type',
        'is_default_shipping',
        'is_default_billing',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->name,
            $this->phone,
            $this->address,
            $this->landmark,
            $this->region,
        ]));
    }
}