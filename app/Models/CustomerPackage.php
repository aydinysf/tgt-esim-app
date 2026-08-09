<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tgt_product_id',
        'sale_price',
        'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(TgtProduct::class, 'tgt_product_id');
    }

    public function getProfitAttribute()
    {
        return $this->sale_price - ($this->product ? $this->product->net_price : 0);
    }
}
