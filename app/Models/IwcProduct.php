<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IwcProduct extends Model
{
    use HasFactory;

    protected $table = 'iwc_products';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'type',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'regular_price',
        'currency',
        'status',
        'availability_type',
        'variants_data',
        'contents_data',
        'required_order_data',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'variants_data' => 'array',
        'contents_data' => 'array',
        'required_order_data' => 'array',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
        'price' => 'decimal:2',
        'regular_price' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(IwcOrder::class, 'iwc_product_id', 'id');
    }

    /**
     * Helper to get localized name
     */
    public function getLocalizedName(string $locale = 'tr'): string
    {
        if (is_array($this->raw_data) && isset($this->raw_data['name'])) {
            $names = $this->raw_data['name'];
            if (is_array($names)) {
                return $names[$locale] ?? $names['en'] ?? $this->name;
            }
        }
        return $this->name;
    }

    /**
     * Helper to get localized description
     */
    public function getLocalizedDescription(string $locale = 'tr'): ?string
    {
        if (is_array($this->raw_data) && isset($this->raw_data['description'])) {
            $desc = $this->raw_data['description'];
            if (is_array($desc)) {
                return $desc[$locale] ?? $desc['en'] ?? $this->description;
            }
        }
        return $this->description;
    }
}
