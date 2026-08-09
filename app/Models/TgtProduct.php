<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgtProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'product_name',
        'product_type',
        'country_code_list',
        'mcc_list',
        'net_price',
        'data_total',
        'data_unit',
        'usage_period',
        'validity_period',
        'card_type',
        'raw_data',
    ];

    protected $casts = [
        'country_code_list' => 'array',
        'mcc_list' => 'array',
        'raw_data' => 'array',
        'net_price' => 'decimal:2',
    ];

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class, 'tgt_product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'tgt_product_id');
    }
}
