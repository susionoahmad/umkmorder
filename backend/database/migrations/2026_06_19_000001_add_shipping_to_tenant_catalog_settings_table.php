<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_catalog_settings', function (Blueprint $table) {
            // Shipping mode: none | zone | distance | api
            $table->enum('shipping_mode', ['none', 'zone', 'distance', 'api'])
                  ->default('none')
                  ->after('auto_whatsapp_redirect');

            // Store location coordinates
            $table->decimal('store_latitude', 10, 8)->nullable()->after('shipping_mode');
            $table->decimal('store_longitude', 11, 8)->nullable()->after('store_latitude');
            $table->string('store_location_label', 255)->nullable()->after('store_longitude');

            // Zone-based shipping config: [{name: "Solo", cost: 5000}, ...]
            $table->json('shipping_zones')->nullable()->after('store_location_label');

            // Distance-based shipping config: [{max_km: 10, cost: 0}, {max_km: 20, cost: 10000}, ...]
            $table->json('shipping_distances')->nullable()->after('shipping_zones');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_catalog_settings', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_mode',
                'store_latitude',
                'store_longitude',
                'store_location_label',
                'shipping_zones',
                'shipping_distances',
            ]);
        });
    }
};
