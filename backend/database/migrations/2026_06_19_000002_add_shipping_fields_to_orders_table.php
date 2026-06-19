<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Actual distance calculated during checkout (km)
            $table->decimal('shipping_distance_km', 8, 2)->nullable()->after('shipping_cost');
            // Zone name selected during checkout (for zone mode)
            $table->string('shipping_zone', 100)->nullable()->after('shipping_distance_km');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_distance_km', 'shipping_zone']);
        });
    }
};
