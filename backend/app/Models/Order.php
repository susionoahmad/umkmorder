<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'invoice_number',
        'order_date',
        'order_type',
        'payment_preference',
        'payment_due_date',
        'status',
        'source',
        'subtotal',
        'discount',
        'shipping_cost',
        'shipping_distance_km',
        'shipping_zone',
        'grand_total',
        'notes',
        'location_link',
        'expires_at',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'discount'             => 'decimal:2',
        'shipping_cost'        => 'decimal:2',
        'shipping_distance_km' => 'decimal:2',
        'grand_total'          => 'decimal:2',
        'order_date'           => 'date',
        'payment_due_date'     => 'date',
        'expires_at'           => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receivable()
    {
        return $this->hasOne(Receivable::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}
