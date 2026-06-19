<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\User;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Plans
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'monthly_price' => 0.00,
            'yearly_price' => 0.00,
            'max_products' => 20,
            'max_orders' => 100,
            'trial_days' => 0,
            'features_json' => [
                'catalog_online' => true,
                'whatsapp_order' => true,
                'tier_pricing' => false,
                'receivables' => false,
                'payment_tracking' => false,
                'distance_shipping' => false,
                'qr_code_catalog' => false,
                'export_data' => false,
                'reminder_whatsapp' => false,
                'automatic_reminder' => false,
                'multi_user' => false,
                'api_access' => false,
            ],
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'monthly_price' => 49000.00,
            'yearly_price' => 490000.00,
            'max_products' => -1, // Unlimited
            'max_orders' => -1,   // Unlimited
            'trial_days' => 30,
            'features_json' => [
                'catalog_online' => true,
                'whatsapp_order' => true,
                'tier_pricing' => true,
                'receivables' => true,
                'payment_tracking' => true,
                'distance_shipping' => true,
                'qr_code_catalog' => true,
                'export_data' => true,
                'reminder_whatsapp' => true,
                'automatic_reminder' => false,
                'multi_user' => false,
                'api_access' => false,
            ],
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Business',
            'slug' => 'business',
            'monthly_price' => 149000.00,
            'yearly_price' => 1490000.00,
            'max_products' => -1, // Unlimited
            'max_orders' => -1,   // Unlimited
            'trial_days' => 30,
            'features_json' => [
                'catalog_online' => true,
                'whatsapp_order' => true,
                'tier_pricing' => true,
                'receivables' => true,
                'payment_tracking' => true,
                'distance_shipping' => true,
                'qr_code_catalog' => true,
                'export_data' => true,
                'reminder_whatsapp' => true,
                'automatic_reminder' => true,
                'multi_user' => true,
                'api_access' => true,
            ],
            'is_active' => true,
        ]);

        // 2. Create Super Admin User
        User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'admin@umkmorder.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // 3. Create Default Platform Settings
        PlatformSetting::create([
            'app_name' => 'UMKMOrder',
            'company_name' => 'UMKMOrder Corp',
            'support_email' => 'support@umkmorder.id',
            'support_whatsapp' => '6281234567890',
            'logo_url' => null,
            'favicon_url' => null,
            'default_trial_duration' => 30,
            'maintenance_mode' => false,
        ]);
    }
}
