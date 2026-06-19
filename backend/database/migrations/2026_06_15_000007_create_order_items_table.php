<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $blueprint->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $blueprint->integer('quantity');
            $blueprint->decimal('price', 15, 2);
            $blueprint->decimal('total', 15, 2);

            $blueprint->index(['tenant_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
