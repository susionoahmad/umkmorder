<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\TenantCatalogSetting;
use App\Models\Product;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kurnia Telur
        $tenant1 = Tenant::create([
            'name' => 'Kurnia Telur',
            'slug' => 'kurnia-telur',
            'logo' => null,
            'phone' => '6281234567890',
            'address' => 'Jl. Raya Telur No. 5, Blitar',
            'subscription_plan' => 'premium',
            'is_active' => true,
        ]);

        TenantCatalogSetting::create([
            'tenant_id' => $tenant1->id,
            'catalog_title' => 'Katalog Telur Asin Kurnia',
            'catalog_description' => 'Produsen telur asin premium gurih dan masir asli Blitar.',
            'catalog_banner' => null,
            'catalog_enabled' => true,
            'show_price' => true,
            'theme' => 'emerald',
        ]);

        Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Asin Original',
            'sku' => 'TA-ORI',
            'price' => 3000.00,
            'description' => 'Telur asin original gurih masir kualitas super.',
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Asin Bakar',
            'sku' => 'TA-BKR',
            'price' => 3500.00,
            'description' => 'Telur asin asap bakar dengan aroma khas panggang.',
            'is_active' => true,
        ]);

        // 2. Sambal Bu Sari
        $tenant2 = Tenant::create([
            'name' => 'Sambal Bu Sari',
            'slug' => 'sambal-bu-sari',
            'logo' => null,
            'phone' => '6289876543210',
            'address' => 'Jl. Cabai Pedas No. 12, Sukabumi',
            'subscription_plan' => 'free',
            'is_active' => true,
        ]);

        TenantCatalogSetting::create([
            'tenant_id' => $tenant2->id,
            'catalog_title' => 'Sambal Bu Sari Online',
            'catalog_description' => 'Aneka sambal rumahan pedas nikmat tanpa pengawet.',
            'catalog_banner' => null,
            'catalog_enabled' => true,
            'show_price' => true,
            'theme' => 'red',
        ]);

        Product::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Sambal Bawang Pedas',
            'sku' => 'SB-BWG',
            'price' => 15000.00,
            'description' => 'Sambal bawang pedas nampol botol 150ml.',
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Sambal Cumi Asin',
            'sku' => 'SB-CMI',
            'price' => 18000.00,
            'description' => 'Sambal cabai rawit dengan cumi asin melimpah.',
            'is_active' => true,
        ]);
    }
}
