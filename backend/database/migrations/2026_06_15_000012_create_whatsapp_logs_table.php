<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $blueprint->string('phone', 20);
            $blueprint->text('message');
            $blueprint->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $blueprint->string('provider', 50);
            $blueprint->text('response')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['tenant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
