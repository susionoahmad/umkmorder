<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modify users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->string('role', 50)->default('staff')->change();
        });

        // 2. Modify tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_status', 50)->default('trial');
            $table->text('support_notes')->nullable();
        });

        // 3. Create plans table
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->decimal('monthly_price', 15, 2);
            $table->decimal('yearly_price', 15, 2);
            $table->integer('max_products')->default(-1); // -1 = unlimited
            $table->integer('max_orders')->default(-1);    // -1 = unlimited
            $table->integer('trial_days')->default(30);
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Create subscriptions table
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('expired_at')->nullable();
            $table->string('status', 50)->default('trial'); // 'trial', 'active', 'expired', 'suspended'
            $table->timestamps();
        });

        // 5. Create billing_invoices table
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('status', 50)->default('pending'); // 'paid', 'pending', 'overdue', 'cancelled'
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 6. Create platform_settings table
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 100)->default('UMKMOrder');
            $table->string('company_name', 150)->default('UMKMOrder Corp');
            $table->string('support_email', 100)->default('support@umkmorder.id');
            $table->string('support_whatsapp', 20)->default('6281234567890');
            $table->string('logo_url', 255)->nullable();
            $table->string('favicon_url', 255)->nullable();
            $table->integer('default_trial_duration')->default(30);
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trial_ends_at', 'subscription_status', 'support_notes']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->unsignedBigInteger('tenant_id')->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            // Downgrading enum change back is complex and unnecessary for clean rollbacks since we drop tables
        });
    }
};
