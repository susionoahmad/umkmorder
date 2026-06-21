<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->text('admin_bank_transfer_info')->nullable()->after('support_whatsapp');
            $table->string('admin_qris_image_url', 255)->nullable()->after('admin_bank_transfer_info');
            $table->string('admin_qris_info', 255)->nullable()->after('admin_qris_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['admin_bank_transfer_info', 'admin_qris_image_url', 'admin_qris_info']);
        });
    }
};
