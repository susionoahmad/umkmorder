<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Quantity range: min_qty <= qty <= max_qty
            // max_qty = NULL means "unlimited" (e.g. 10+)
            $table->unsignedInteger('min_qty');
            $table->unsignedInteger('max_qty')->nullable();

            $table->decimal('unit_price', 15, 2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index for fast lookup during checkout
            $table->index(['product_id', 'is_active', 'min_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
