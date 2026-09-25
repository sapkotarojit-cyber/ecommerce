<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokan extends Authenticatable implements HasName, FilamentUser
{
    use Notifiable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'company_name',
        'name',
        'email',
        'password',
        'reg_no',
        'contact_number',
        'business_location',
        'business_address',
        'business_reg_no',
        'pan_no',
        'business_document',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'bank_document',
        'logo',
        'status',
        'rejection_comment',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getFilamentName(): string
    {
        return $this->company_name ?: $this->email;
    }
    
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

    public function products()
    {
        return $this->hasMany(Product::class, 'dokan_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}