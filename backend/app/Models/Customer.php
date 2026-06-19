<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, BelongsToTenant, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'whatsapp',
        'address',
        'notes',
        'source',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }
}
