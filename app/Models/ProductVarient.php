<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVarient extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'price',
        'discount',
        'qty',
        'images',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'varient_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'varient_id');
    }
}