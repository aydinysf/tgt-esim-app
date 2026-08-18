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
        'parent_id',
        'branch_id',
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

    public function isBranchUser(): bool
    {
        return $this->role === 'branch' || !empty($this->branch_id);
    }

    public function getEffectiveCustomer(): User
    {
        if ($this->isBranchUser() && $this->parent) {
            return $this->parent;
        }
        return $this;
    }

    public function hasBalance(float $amount): bool
    {
        $owner = $this->getEffectiveCustomer();
        return (float) $owner->balance >= $amount;
    }

    public function deductBalance(float $amount): bool
    {
        $owner = $this->getEffectiveCustomer();
        if ($owner->hasBalance($amount)) {
            $owner->balance = (float) $owner->balance - $amount;
            return $owner->save();
        }
        return false;
    }

    public function addBalance(float $amount): bool
    {
        $owner = $this->getEffectiveCustomer();
        $owner->balance = (float) $owner->balance + $amount;
        return $owner->save();
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class, 'user_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
