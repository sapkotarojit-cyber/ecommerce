<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function user()
    {
         return $this->belongsTo(User::class);
    }

    public function dokan()
    {
        return $this->belongsTo(Dokan::class);
    }

    public function shipping_address()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }


}
