<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IwcOrder extends Model
{
    use HasFactory;

    protected $table = 'iwc_orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'branch_id',
        'iwc_product_id',
        'product_name',
        'product_variant_id',
        'variant_label',
        'quantity',
        'service_date',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'hotel_name',
        'required_data',
        'total_amount',
        'currency',
        'payment_method',
        'payment_status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'iwc_order_id',
        'iwc_order_status',
        'iwc_response_data',
        'error_message',
    ];

    protected $casts = [
        'required_data' => 'array',
        'iwc_response_data' => 'array',
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(IwcProduct::class, 'iwc_product_id', 'id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isIwcConfirmed(): bool
    {
        return !empty($this->iwc_order_id);
    }
}
