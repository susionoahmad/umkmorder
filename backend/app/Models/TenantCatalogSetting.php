<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class TenantCatalogSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'catalog_title',
        'catalog_description',
        'catalog_banner',
        'catalog_enabled',
        'auto_whatsapp_redirect',
        'show_price',
        'theme',
        'bank_transfer_info',
        'qris_info',
        'qris_image_url',
        // Shipping fields
        'shipping_mode',
        'store_latitude',
        'store_longitude',
        'store_location_label',
        'shipping_zones',
        'shipping_distances',
    ];

    protected $casts = [
        'catalog_enabled'        => 'boolean',
        'auto_whatsapp_redirect' => 'boolean',
        'show_price'             => 'boolean',
        'store_latitude'         => 'float',
        'store_longitude'        => 'float',
        'shipping_zones'         => 'array',
        'shipping_distances'     => 'array',
    ];
}
