<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_catalog_settings', function (Blueprint $blueprint) {
            $blueprint->string('qris_image_url')->nullable()->after('qris_info');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_catalog_settings', function (Blueprint $blueprint) {
            $blueprint->dropColumn('qris_image_url');
        });
    }
};
