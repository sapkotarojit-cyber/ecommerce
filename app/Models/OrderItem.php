<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

protected $fillable = [
        'order_id',
        'product_id',
        'varient_id',
        'qty',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function varient()
    {
        return $this->belongsTo(ProductVarient::class, 'varient_id');
    }
}
