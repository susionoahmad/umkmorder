<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CatalogVisit extends Model
{
    use BelongsToTenant;

    public $timestamps = false; // Custom visited_at column acts as timestamp

    protected $fillable = [
        'tenant_id',
        'ip_address',
        'user_agent',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];
}
