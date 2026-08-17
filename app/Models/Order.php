<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'channel_order_no',
        'user_id',
        'branch_id',
        'branch_name',
        'tgt_product_id',
        'net_price',
        'sale_price',
        'profit',
        'iccid',
        'qr_code',
        'order_status',
        'profile_status',
        'idempotency_key',
        'raw_response',
    ];

    protected $casts = [
        'net_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function product()
    {
        return $this->belongsTo(TgtProduct::class, 'tgt_product_id');
    }

    public function getAppleInstallUrlAttribute(): ?string
    {
        if (!$this->qr_code) {
            return null;
        }
        return 'https://esimsetup.apple.com/esim_qrcode_provisioning?carddata=' . urlencode($this->qr_code);
    }
}
