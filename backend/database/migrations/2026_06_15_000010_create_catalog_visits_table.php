<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_visits', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $blueprint->string('ip_address', 45);
            $blueprint->text('user_agent')->nullable();
            $blueprint->timestamp('visited_at')->useCurrent();

            $blueprint->index(['tenant_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_visits');
    }
};
