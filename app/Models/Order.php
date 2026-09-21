<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'dokan_id',
        'shipping_address_id',
        'total_amount',
        'status',
        'order_status',
        'payment_method',
        'payment_status',
        'tracking_number',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->tracking_number)) {
                $order->tracking_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokan()
    {
        return $this->belongsTo(Dokan::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address_id');
    }

    public function shipping_address()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Alias for orderItems to prevent relation not found exceptions
    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }
}