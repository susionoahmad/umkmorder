<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'price',
        'description',
        'image_url',
        'show_image',
        'is_active',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'show_image' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Active price tiers ordered by min_qty ascending.
     */
    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)
            ->where('is_active', true)
            ->orderBy('min_qty');
    }

    /**
     * All price tiers (including inactive), for admin management.
     */
    public function allPriceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_qty');
    }
}

