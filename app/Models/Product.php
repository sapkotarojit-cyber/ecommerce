<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function dokan()
    {
        return $this->belongsTo(Dokan::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function varients()
    {
        return $this->hasMany(ProductVarient::class);
    }
}
