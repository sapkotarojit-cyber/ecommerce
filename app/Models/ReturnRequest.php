<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'dokan_id',
        'reason',
        'status',
        'refund_status',
        'refund_amount',
        'admin_note',
    ];

    protected $casts = [
        'refund_amount' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dokan(): BelongsTo
    {
        return $this->belongsTo(Dokan::class);
    }
}