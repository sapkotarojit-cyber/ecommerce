<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'discount' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'varient_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'varient_id');
    }
}
