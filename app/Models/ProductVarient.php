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

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'qty' => 'integer',
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