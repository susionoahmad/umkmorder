<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'yearly_price',
        'max_products',
        'max_orders',
        'trial_days',
        'features_json',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'float',
        'yearly_price' => 'float',
        'max_products' => 'integer',
        'max_orders' => 'integer',
        'trial_days' => 'integer',
        'features_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
