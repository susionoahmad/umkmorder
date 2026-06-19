<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->string('name', 150);
            $blueprint->string('whatsapp', 20);
            $blueprint->text('address')->nullable();
            $blueprint->text('notes')->nullable();
            $blueprint->enum('source', ['catalog', 'whatsapp', 'manual', 'import'])->default('manual');
            $blueprint->timestamps();

            $blueprint->index(['tenant_id', 'whatsapp']);
            $blueprint->index(['tenant_id', 'name']);
            $blueprint->index(['tenant_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
