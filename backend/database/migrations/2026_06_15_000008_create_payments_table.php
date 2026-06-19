<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $blueprint->date('payment_date');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->enum('payment_method', ['cash', 'transfer', 'qris', 'credit']);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['tenant_id', 'order_id']);
            $blueprint->index(['tenant_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
