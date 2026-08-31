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

    /**
     * Clean product name by removing all references to days, day, gün, günlük and validity periods
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product_name ?? 'eSIM Package';
        // 1. Remove validity in parens: (Valid for 60 days), (180-Day Validity), (Valid for 120 days)
        $name = preg_replace('/\s*\(\s*(?:valid\s*(?:for)?\s*\d+\s*(?:days?|gün)|[0-9]+[\s-]*(?:day|days|gün)\s*validity)\s*\)/i', '', $name);
        // 2. Remove /Xdays, /X days, /X gün
        $name = preg_replace('/\s*\/\s*\d+\s*(?:days?|gün)\b/i', '', $name);
        // 3. Remove standalone X Days, X Day, Xdays, X gün
        $name = preg_replace('/\b\d+\s*(?:days?|gün)\b/i', '', $name);
        // 4. Remove /day or / day or /gün
        $name = preg_replace('/\s*\/\s*(?:days?|gün)\b/i', '', $name);
        // 5. Remove any leftover day, days, gün, günlük words
        $name = preg_replace('/\b(?:days?|gün|günlük)\b/i', '', $name);
        // 6. Clean empty parens and double spaces
        $name = preg_replace('/\(\s*\)/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
}
