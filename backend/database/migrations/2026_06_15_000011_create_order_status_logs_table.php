<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $blueprint->string('old_status', 50)->nullable();
            $blueprint->string('new_status', 50);
            $blueprint->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->timestamp('created_at')->nullable();

            $blueprint->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
