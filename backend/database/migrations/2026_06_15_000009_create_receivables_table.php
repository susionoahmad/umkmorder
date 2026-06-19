<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $blueprint->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $blueprint->decimal('total_amount', 15, 2);
            $blueprint->decimal('paid_amount', 15, 2)->default(0.00);
            $blueprint->decimal('remaining_amount', 15, 2);
            $blueprint->date('due_date');
            $blueprint->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $blueprint->timestamps();

            $blueprint->index(['tenant_id', 'customer_id']);
            $blueprint->index(['tenant_id', 'status']);
            $blueprint->index(['tenant_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
