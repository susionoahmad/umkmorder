<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->string('image_url')->nullable()->after('description');
            $blueprint->boolean('show_image')->default(true)->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['image_url', 'show_image']);
        });
    }
};
