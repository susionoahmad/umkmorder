<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ProductPriceTier extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'min_qty',
        'max_qty',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'min_qty'    => 'integer',
        'max_qty'    => 'integer',
        'unit_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
