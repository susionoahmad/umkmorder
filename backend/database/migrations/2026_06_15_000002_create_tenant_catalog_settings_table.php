<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_catalog_settings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->string('catalog_title', 150);
            $blueprint->text('catalog_description')->nullable();
            $blueprint->string('catalog_banner', 255)->nullable();
            $blueprint->boolean('catalog_enabled')->default(true);
            $blueprint->boolean('show_price')->default(true);
            $blueprint->string('theme', 50)->default('default');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_catalog_settings');
    }
};
