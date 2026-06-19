<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->enum('payment_preference', ['cash', 'transfer', 'qris', 'tempo', 'credit'])
                ->default('cash')
                ->after('order_type');
            $blueprint->date('payment_due_date')->nullable()->after('payment_preference');
        });

        Schema::table('tenant_catalog_settings', function (Blueprint $blueprint) {
            $blueprint->text('bank_transfer_info')->nullable()->after('theme');
            $blueprint->text('qris_info')->nullable()->after('bank_transfer_info');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['payment_preference', 'payment_due_date']);
        });

        Schema::table('tenant_catalog_settings', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['bank_transfer_info', 'qris_info']);
        });
    }
};
