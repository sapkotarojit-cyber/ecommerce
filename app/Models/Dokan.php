<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Dokan extends Authenticatable implements HasName, FilamentUser
{
    use Notifiable;

    // Define status constants expected by canAccessPanel()
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function canAccessPanel(Panel $panel): bool
    {
        // Allows login only if approved
        return $this->status === self::STATUS_APPROVED;
    }

    public function getFilamentName(): string
    {
        return $this->company_name ?: $this->email;
    }

    protected $fillable = [
        'user_id',
        'company_name',
        'name',
        'email',
        'password',
        'reg_no',
        'contact_number',
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

    public function products()
    {
        return $this->hasMany(Product::class);
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