<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name', 150);
            $blueprint->string('slug', 150)->unique();
            $blueprint->string('logo', 255)->nullable();
            $blueprint->string('phone', 20);
            $blueprint->text('address')->nullable();
            $blueprint->string('subscription_plan', 50)->default('free');
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
