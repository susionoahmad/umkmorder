<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'phone',
        'address',
        'subscription_plan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function catalogSetting()
    {
        return $this->hasOne(TenantCatalogSetting::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function catalogVisits()
    {
        return $this->hasMany(CatalogVisit::class);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsAppLog::class);
    }
}
