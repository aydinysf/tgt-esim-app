<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'phone',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function hasBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    public function deductBalance(float $amount): bool
    {
        if ($this->hasBalance($amount)) {
            $this->balance = (float) $this->balance - $amount;
            return $this->save();
        }
        return false;
    }

    public function addBalance(float $amount): bool
    {
        $this->balance = (float) $this->balance + $amount;
        return $this->save();
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class, 'user_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
