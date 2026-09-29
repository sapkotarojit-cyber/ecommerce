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
        'payment_receipt',
        'payment_transaction_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->tracking_number)) {
                $order->tracking_number =
                    'ORD-' . strtoupper(
                        uniqid()
                    );
            }

            if (empty($order->order_status)) {
                $order->order_status = 'pending';
            }

            if (empty($order->status)) {
                $order->status = 'pending';
            }

            if (empty($order->payment_status)) {
                $order->payment_status = 'pending';
            }
        });

        static::updating(function ($order) {
            if ($order->isDirty('order_status')) {
                $order->status =
                    $order->order_status;
            } elseif ($order->isDirty('status')) {
                $order->order_status =
                    $order->status;
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
        return $this->belongsTo(
            ShippingAddress::class
        );
    }

    public function shipping_address()
    {
        return $this->shippingAddress();
    }

    public function orderItems()
    {
        return $this->hasMany(
            OrderItem::class
        );
    }

    public function order_items()
    {
        return $this->orderItems();
    }

    public function returnRequests()
    {
        return $this->hasMany(
            ReturnRequest::class
        );
    }
}