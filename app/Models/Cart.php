<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
   public function product()
   {
       return $this->belongsTo(Product::class);
   }

    public function user()
    {
         return $this->belongsTo(User::class);
    }

    public function varient()
    {
        return $this->belongsTo(ProductVarient::class, 'varient_id');
    }

    public function dokan()
    {
        return $this->belongsTo(Dokan::class);
    }
}
