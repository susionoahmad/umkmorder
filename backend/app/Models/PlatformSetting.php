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
        'admin_bank_transfer_info',
        'admin_qris_image_url',
        'admin_qris_info',
    ];

    protected $casts = [
        'default_trial_duration' => 'integer',
        'maintenance_mode' => 'boolean',
    ];
}
