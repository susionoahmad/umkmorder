<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'company_name',
        'support_email',
        'support_whatsapp',
        'logo_url',
        'favicon_url',
        'default_trial_duration',
        'maintenance_mode',
    ];

    protected $casts = [
        'default_trial_duration' => 'integer',
        'maintenance_mode' => 'boolean',
    ];
}
