<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $blueprint->string('invoice_number', 50);
            $blueprint->date('order_date');
            $blueprint->enum('order_type', ['delivery', 'pickup']);
            $blueprint->enum('status', ['draft', 'new', 'processing', 'shipped', 'completed', 'cancelled'])->default('draft');
            $blueprint->enum('source', ['catalog', 'whatsapp', 'manual', 'api'])->default('manual');
            $blueprint->decimal('subtotal', 15, 2);
            $blueprint->decimal('discount', 15, 2)->default(0.00);
            $blueprint->decimal('shipping_cost', 15, 2)->default(0.00);
            $blueprint->decimal('grand_total', 15, 2);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['tenant_id', 'invoice_number']);
            $blueprint->index(['tenant_id', 'status']);
            $blueprint->index(['tenant_id', 'source']);
            $blueprint->index(['tenant_id', 'order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
