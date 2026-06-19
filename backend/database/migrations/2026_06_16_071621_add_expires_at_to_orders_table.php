<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'expired', 'new', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'draft'");
            $table->dateTime('expires_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('expires_at');
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'new', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'draft'");
        });
    }
};
