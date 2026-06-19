<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class WhatsAppLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'phone',
        'message',
        'status',
        'provider',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
