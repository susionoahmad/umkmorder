<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\TenantCatalogSetting;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\CatalogVisit;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\BillingInvoice;
use Carbon\Carbon;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kurnia Telur
        $tenant1 = Tenant::create([
            'name' => 'Kurnia Telur',
            'slug' => 'kurnia-telur',
            'logo' => '/kurnia-telur-logo.png',
            'phone' => '6281234567890',
            'address' => 'Jl. Raya Telur No. 5, Blitar',
            'subscription_plan' => 'pro',
            'subscription_status' => 'active',
            'trial_ends_at' => null,
            'is_active' => true,
        ]);

        TenantCatalogSetting::create([
            'tenant_id' => $tenant1->id,
            'catalog_title' => 'Katalog Telur Asin Kurnia',
            'catalog_description' => 'Produsen telur asin premium gurih dan masir asli Blitar.',
            'catalog_banner' => '/kurnia-telur-banner.png',
            'catalog_enabled' => true,
            'show_price' => true,
            'theme' => 'emerald',
            'bank_transfer_info' => 'BCA 123456789 a/n Ahmad Owner',
            'qris_info' => 'Scan kode QRIS Kurnia Telur untuk pembayaran cepat.',
            'shipping_mode' => 'distance',
            'store_latitude' => -8.098224,
            'store_longitude' => 112.168123,
            'store_location_label' => 'Jl. Raya Telur No. 5, Blitar',
            'shipping_distances' => [
                ['max_km' => 5, 'cost' => 5000],
                ['max_km' => 10, 'cost' => 10000],
                ['max_km' => 20, 'cost' => 15000]
            ],
            'shipping_zones' => null,
        ]);

        $p1 = Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Asin Original',
            'sku' => 'TA-ORI',
            'price' => 3000.00,
            'description' => 'Telur asin original gurih masir kualitas super.',
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Asin Bakar',
            'sku' => 'TA-BKR',
            'price' => 3500.00,
            'description' => 'Telur asin asap bakar dengan aroma khas panggang.',
            'is_active' => true,
        ]);

        $p3 = Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Asin Pedas',
            'sku' => 'TA-PDS',
            'price' => 4000.00,
            'description' => 'Telur asin bumbu pedas rempah meresap.',
            'is_active' => true,
        ]);

        $p4 = Product::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Telur Bebek Mentah',
            'sku' => 'TA-MTH',
            'price' => 2500.00,
            'description' => 'Telur bebek mentah segar langsung dari peternakan.',
            'is_active' => true,
        ]);

        // Price Tiers for Kurnia Telur (Telur Asin Original)
        ProductPriceTier::create([
            'tenant_id' => $tenant1->id,
            'product_id' => $p1->id,
            'min_qty' => 50,
            'max_qty' => 99,
            'unit_price' => 2800.00,
            'is_active' => true,
        ]);

        ProductPriceTier::create([
            'tenant_id' => $tenant1->id,
            'product_id' => $p1->id,
            'min_qty' => 100,
            'max_qty' => 999999,
            'unit_price' => 2600.00,
            'is_active' => true,
        ]);

        // Kurnia Telur Customers
        $customers1 = [
            Customer::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Toko Berkah',
                'whatsapp' => '6281111111111',
                'address' => 'Jl. Raya Kediri No. 1, Kediri',
                'notes' => 'Langganan grosir telur original',
                'source' => 'manual',
            ]),
            Customer::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Siti Aminah',
                'whatsapp' => '6282222222222',
                'address' => 'Perum Permata Indah B-12, Blitar',
                'notes' => 'Beli eceran lewat katalog',
                'source' => 'catalog',
            ]),
            Customer::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Resto Selera Rakyat',
                'whatsapp' => '6283333333333',
                'address' => 'Jl. Pemuda No. 45, Malang',
                'notes' => 'Langganan grosir telur bakar',
                'source' => 'manual',
            ]),
            Customer::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Budi Santoso',
                'whatsapp' => '6284444444444',
                'address' => 'Kost Asri Kamar 3, Blitar',
                'notes' => 'Beli lewat WhatsApp',
                'source' => 'whatsapp',
            ]),
            Customer::create([
                'tenant_id' => $tenant1->id,
                'name' => 'Warung Bu Marni',
                'whatsapp' => '6285555555555',
                'address' => 'Jl. Pasar Baru No. 8, Blitar',
                'notes' => 'Sering ambil tempo',
                'source' => 'manual',
            ]),
        ];

        // 2. Sambal Bu Sari
        $tenant2 = Tenant::create([
            'name' => 'Sambal Bu Sari',
            'slug' => 'sambal-bu-sari',
            'logo' => null,
            'phone' => '6289876543210',
            'address' => 'Jl. Cabai Pedas No. 12, Sukabumi',
            'subscription_plan' => 'free',
            'subscription_status' => 'active',
            'trial_ends_at' => null,
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
            'bank_transfer_info' => 'Mandiri 987654321 a/n Sari Owner',
            'qris_info' => null,
            'shipping_mode' => 'none',
        ]);

        $s1 = Product::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Sambal Bawang Pedas',
            'sku' => 'SB-BWG',
            'price' => 15000.00,
            'description' => 'Sambal bawang pedas nampol botol 150ml.',
            'is_active' => true,
        ]);

        $s2 = Product::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Sambal Cumi Asin',
            'sku' => 'SB-CMI',
            'price' => 18000.00,
            'description' => 'Sambal cabai rawit dengan cumi asin melimpah.',
            'is_active' => true,
        ]);

        $s3 = Product::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Sambal Teri Hijau',
            'sku' => 'SB-TRH',
            'price' => 16000.00,
            'description' => 'Sambal teri medan cabai hijau nikmat.',
            'is_active' => true,
        ]);

        // Sambal Bu Sari Customers
        $customers2 = [
            Customer::create([
                'tenant_id' => $tenant2->id,
                'name' => 'Dewi Lestari',
                'whatsapp' => '6286666666666',
                'address' => 'Jl. Kebon Jeruk No. 2, Jakarta',
                'notes' => 'Langganan catalog',
                'source' => 'catalog',
            ]),
            Customer::create([
                'tenant_id' => $tenant2->id,
                'name' => 'Hendra Wijaya',
                'whatsapp' => '6287777777777',
                'address' => 'Perum Damai Sejahtera C-4, Sukabumi',
                'notes' => 'Teman sekolah Bu Sari',
                'source' => 'catalog',
            ]),
            Customer::create([
                'tenant_id' => $tenant2->id,
                'name' => 'Warung Makan Padang Sederhana',
                'whatsapp' => '6288888888888',
                'address' => 'Jl. Sukabumi No. 100, Sukabumi',
                'notes' => 'Untuk sajian warung makan',
                'source' => 'whatsapp',
            ]),
        ];

        // 3. Create Subscriptions & Invoices (requires Plan to exist)
        $proPlan = Plan::where('slug', 'pro')->first();
        if ($proPlan) {
            $sub1 = Subscription::create([
                'tenant_id' => $tenant1->id,
                'plan_id' => $proPlan->id,
                'started_at' => Carbon::now()->subDays(15),
                'expired_at' => Carbon::now()->addDays(15),
                'status' => 'active',
            ]);

            BillingInvoice::create([
                'tenant_id' => $tenant1->id,
                'subscription_id' => $sub1->id,
                'invoice_number' => 'INV-SUB-KT-001',
                'amount' => 49000.00,
                'status' => 'paid',
                'due_date' => Carbon::now()->subDays(15)->toDateString(),
                'paid_at' => Carbon::now()->subDays(15),
            ]);
        }

        $freePlan = Plan::where('slug', 'free')->first();
        if ($freePlan) {
            Subscription::create([
                'tenant_id' => $tenant2->id,
                'plan_id' => $freePlan->id,
                'started_at' => Carbon::now()->subDays(30),
                'expired_at' => null,
                'status' => 'active',
            ]);
        }

        // 4. Seeding Catalog Visits over the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Kurnia Telur: 5-20 visits per day
            $visitsKT = rand(5, 20);
            for ($j = 0; $j < $visitsKT; $j++) {
                CatalogVisit::create([
                    'tenant_id' => $tenant1->id,
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . rand(100, 120) . '.0.0.0 Safari/537.36',
                    'visited_at' => $date->copy()->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0, 59))->addSeconds(rand(0, 59)),
                ]);
            }

            // Sambal Bu Sari: 3-12 visits per day
            $visitsSari = rand(3, 12);
            for ($j = 0; $j < $visitsSari; $j++) {
                CatalogVisit::create([
                    'tenant_id' => $tenant2->id,
                    'ip_address' => '192.168.2.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_' . rand(1, 6) . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
                    'visited_at' => $date->copy()->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0, 59))->addSeconds(rand(0, 59)),
                ]);
            }
        }

        // 5. Seeding Orders for Kurnia Telur
        $pList1 = [$p1, $p2, $p3, $p4];
        $ordersData1 = [
            [
                'customer_idx' => 0, // Toko Berkah
                'days_ago' => 28,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 0, 'qty' => 100], // TA-ORI at tier price 2600 = 260000
                ],
                'shipping' => 15000,
                'discount' => 0,
                'payment' => [
                    'amount' => 275000,
                    'method' => 'transfer',
                    'date_offset' => 28,
                ]
            ],
            [
                'customer_idx' => 1, // Siti Aminah
                'days_ago' => 25,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'cash',
                'items' => [
                    ['product_idx' => 0, 'qty' => 20], // TA-ORI at 3000 = 60000
                ],
                'shipping' => 10000,
                'discount' => 0,
                'payment' => [
                    'amount' => 70000,
                    'method' => 'cash',
                    'date_offset' => 25,
                ]
            ],
            [
                'customer_idx' => 2, // Resto Selera Rakyat
                'days_ago' => 22,
                'type' => 'pickup',
                'status' => 'completed',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 1, 'qty' => 100], // TA-BKR at 3500 = 350000
                ],
                'shipping' => 0,
                'discount' => 0,
                'payment' => [
                    'amount' => 350000,
                    'method' => 'transfer',
                    'date_offset' => 22,
                ]
            ],
            [
                'customer_idx' => 3, // Budi Santoso
                'days_ago' => 20,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'qris',
                'items' => [
                    ['product_idx' => 0, 'qty' => 10], // TA-ORI at 3000 = 30000
                ],
                'shipping' => 5000,
                'discount' => 0,
                'payment' => [
                    'amount' => 35000,
                    'method' => 'qris',
                    'date_offset' => 20,
                ]
            ],
            [
                'customer_idx' => 0, // Toko Berkah
                'days_ago' => 18,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'tempo', // Paid Receivable
                'items' => [
                    ['product_idx' => 0, 'qty' => 100], // TA-ORI at 2600 = 260000
                ],
                'shipping' => 15000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 275000,
                    'remaining' => 0,
                    'status' => 'paid',
                    'due_offset' => 11, // due 11 days ago
                    'payments' => [
                        ['amount' => 275000, 'method' => 'transfer', 'date_offset' => 12] // paid 12 days ago
                    ]
                ]
            ],
            [
                'customer_idx' => 4, // Warung Bu Marni
                'days_ago' => 15,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'tempo', // Partially Paid Receivable
                'items' => [
                    ['product_idx' => 1, 'qty' => 30], // TA-BKR at 3500 = 105000
                ],
                'shipping' => 10000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 50000,
                    'remaining' => 65000,
                    'status' => 'partial',
                    'due_offset' => 8, // due 8 days ago
                    'payments' => [
                        ['amount' => 50000, 'method' => 'transfer', 'date_offset' => 12] // paid 12 days ago
                    ]
                ]
            ],
            [
                'customer_idx' => 0, // Toko Berkah
                'days_ago' => 12,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'tempo', // Overdue Receivable
                'items' => [
                    ['product_idx' => 0, 'qty' => 200], // TA-ORI at 2600 = 520000
                ],
                'shipping' => 15000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 0,
                    'remaining' => 535000,
                    'status' => 'overdue',
                    'due_offset' => 5, // due 5 days ago (overdue!)
                    'payments' => []
                ]
            ],
            [
                'customer_idx' => 1, // Siti Aminah
                'days_ago' => 10,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'qris',
                'items' => [
                    ['product_idx' => 2, 'qty' => 10], // TA-PDS at 4000 = 40000
                ],
                'shipping' => 10000,
                'discount' => 0,
                'payment' => [
                    'amount' => 50000,
                    'method' => 'qris',
                    'date_offset' => 10,
                ]
            ],
            [
                'customer_idx' => 2, // Resto Selera Rakyat
                'days_ago' => 8,
                'type' => 'pickup',
                'status' => 'completed',
                'payment_pref' => 'tempo', // Active unpaid receivable
                'items' => [
                    ['product_idx' => 1, 'qty' => 100], // TA-BKR at 3500 = 350000
                ],
                'shipping' => 0,
                'discount' => 0,
                'receivable' => [
                    'paid' => 0,
                    'remaining' => 350000,
                    'status' => 'unpaid',
                    'due_offset' => -2, // due in 2 days
                    'payments' => []
                ]
            ],
            [
                'customer_idx' => 3, // Budi Santoso
                'days_ago' => 6,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'cash',
                'items' => [
                    ['product_idx' => 1, 'qty' => 10], // TA-BKR at 3500 = 35000
                ],
                'shipping' => 5000,
                'discount' => 0,
                'payment' => [
                    'amount' => 40000,
                    'method' => 'cash',
                    'date_offset' => 6,
                ]
            ],
            [
                'customer_idx' => 4, // Warung Bu Marni
                'days_ago' => 4,
                'type' => 'delivery',
                'status' => 'processing',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 1, 'qty' => 20], // TA-BKR at 3500 = 70000
                ],
                'shipping' => 10000,
                'discount' => 0,
            ],
            [
                'customer_idx' => 0, // Toko Berkah
                'days_ago' => 3,
                'type' => 'delivery',
                'status' => 'processing',
                'payment_pref' => 'tempo', // unpaid receivable
                'items' => [
                    ['product_idx' => 0, 'qty' => 100], // TA-ORI at 2800 = 280000
                ],
                'shipping' => 15000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 0,
                    'remaining' => 295000,
                    'status' => 'unpaid',
                    'due_offset' => -4, // due in 4 days
                    'payments' => []
                ]
            ],
            [
                'customer_idx' => 1, // Siti Aminah
                'days_ago' => 2,
                'type' => 'delivery',
                'status' => 'new',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 2, 'qty' => 5], // TA-PDS at 4000 = 20000
                ],
                'shipping' => 10000,
                'discount' => 0,
            ],
            [
                'customer_idx' => 2, // Resto Selera Rakyat
                'days_ago' => 1,
                'type' => 'pickup',
                'status' => 'draft',
                'payment_pref' => 'cash',
                'items' => [
                    ['product_idx' => 0, 'qty' => 50], // TA-ORI at 2800 = 140000
                ],
                'shipping' => 0,
                'discount' => 0,
            ],
            [
                'customer_idx' => 3, // Budi Santoso
                'days_ago' => 0,
                'type' => 'delivery',
                'status' => 'new',
                'payment_pref' => 'qris',
                'items' => [
                    ['product_idx' => 1, 'qty' => 8], // TA-BKR at 3500 = 28000
                ],
                'shipping' => 5000,
                'discount' => 0,
            ]
        ];

        $this->seedTenantOrders($tenant1->id, $customers1, $pList1, $ordersData1, 'KT');

        // 6. Seeding Orders for Sambal Bu Sari
        $pList2 = [$s1, $s2, $s3];
        $ordersData2 = [
            [
                'customer_idx' => 0, // Dewi Lestari
                'days_ago' => 20,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 0, 'qty' => 2], // Sambal Bawang Pedas at 15000 = 30000
                ],
                'shipping' => 10000,
                'discount' => 0,
                'payment' => [
                    'amount' => 40000,
                    'method' => 'transfer',
                    'date_offset' => 20,
                ]
            ],
            [
                'customer_idx' => 1, // Hendra Wijaya
                'days_ago' => 17,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'qris',
                'items' => [
                    ['product_idx' => 1, 'qty' => 5], // Sambal Cumi Asin at 18000 = 90000
                ],
                'shipping' => 15000,
                'discount' => 5000,
                'payment' => [
                    'amount' => 100000,
                    'method' => 'qris',
                    'date_offset' => 17,
                ]
            ],
            [
                'customer_idx' => 2, // Warung Makan Padang Sederhana
                'days_ago' => 14,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'tempo', // paid receivable
                'items' => [
                    ['product_idx' => 2, 'qty' => 10], // Sambal Teri Hijau at 16000 = 160000
                ],
                'shipping' => 12000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 172000,
                    'remaining' => 0,
                    'status' => 'paid',
                    'due_offset' => 7,
                    'payments' => [
                        ['amount' => 172000, 'method' => 'transfer', 'date_offset' => 8]
                    ]
                ]
            ],
            [
                'customer_idx' => 0, // Dewi Lestari
                'days_ago' => 10,
                'type' => 'pickup',
                'status' => 'completed',
                'payment_pref' => 'cash',
                'items' => [
                    ['product_idx' => 0, 'qty' => 1], // SB-BWG = 15000
                ],
                'shipping' => 0,
                'discount' => 0,
                'payment' => [
                    'amount' => 15000,
                    'method' => 'cash',
                    'date_offset' => 10,
                ]
            ],
            [
                'customer_idx' => 1, // Hendra Wijaya
                'days_ago' => 8,
                'type' => 'delivery',
                'status' => 'completed',
                'payment_pref' => 'tempo', // unpaid overdue receivable
                'items' => [
                    ['product_idx' => 1, 'qty' => 4], // SB-CMI at 18000 = 72000
                ],
                'shipping' => 10000,
                'discount' => 0,
                'receivable' => [
                    'paid' => 0,
                    'remaining' => 82000,
                    'status' => 'overdue',
                    'due_offset' => 1, // due 1 day ago (overdue!)
                    'payments' => []
                ]
            ],
            [
                'customer_idx' => 2, // Warung Makan Padang Sederhana
                'days_ago' => 5,
                'type' => 'delivery',
                'status' => 'processing',
                'payment_pref' => 'transfer',
                'items' => [
                    ['product_idx' => 2, 'qty' => 15], // SB-TRH at 16000 = 240000
                ],
                'shipping' => 15000,
                'discount' => 10000,
            ],
            [
                'customer_idx' => 0, // Dewi Lestari
                'days_ago' => 3,
                'type' => 'delivery',
                'status' => 'new',
                'payment_pref' => 'qris',
                'items' => [
                    ['product_idx' => 0, 'qty' => 2], // 30000
                ],
                'shipping' => 10000,
                'discount' => 0,
            ],
            [
                'customer_idx' => 1, // Hendra Wijaya
                'days_ago' => 1,
                'type' => 'pickup',
                'status' => 'draft',
                'payment_pref' => 'cash',
                'items' => [
                    ['product_idx' => 1, 'qty' => 1], // 18000
                ],
                'shipping' => 0,
                'discount' => 0,
            ]
        ];

        $this->seedTenantOrders($tenant2->id, $customers2, $pList2, $ordersData2, 'BS');
    }

    private function seedTenantOrders($tenantId, $customers, $products, $ordersData, $invoicePrefix): void
    {
        foreach ($ordersData as $idx => $oData) {
            $customer = $customers[$oData['customer_idx']];
            $orderDate = Carbon::now()->subDays($oData['days_ago']);
            
            // Generate invoice number
            $dateStr = $orderDate->format('Ymd');
            $invoiceNumber = "INV/{$invoicePrefix}/{$dateStr}/" . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);

            // Determine payment due date if tempo
            $paymentDueDate = null;
            if ($oData['payment_pref'] === 'tempo') {
                if (isset($oData['receivable'])) {
                    $paymentDueDate = Carbon::now()->subDays($oData['receivable']['due_offset']);
                } else {
                    $paymentDueDate = $orderDate->copy()->addDays(7);
                }
            }

            // Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'order_date' => $orderDate->toDateString(),
                'order_type' => $oData['type'],
                'payment_preference' => $oData['payment_pref'],
                'payment_due_date' => $paymentDueDate ? $paymentDueDate->toDateString() : null,
                'status' => $oData['status'],
                'source' => $oData['type'] === 'pickup' ? 'whatsapp' : 'catalog',
                'subtotal' => 0, // Will be updated
                'discount' => $oData['discount'],
                'shipping_cost' => $oData['shipping'],
                'grand_total' => 0, // Will be updated
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Create Order Items and calculate Subtotal
            $subtotal = 0;
            foreach ($oData['items'] as $itemData) {
                $product = $products[$itemData['product_idx']];
                $qty = $itemData['qty'];
                
                // Calculate unit price based on tiers if any
                $unitPrice = $product->price;
                
                // Kurnia Telur original product tiers
                if ($product->sku === 'TA-ORI') {
                    if ($qty >= 100) {
                        $unitPrice = 2600.00;
                    } elseif ($qty >= 50) {
                        $unitPrice = 2800.00;
                    }
                }

                $totalItem = $unitPrice * $qty;
                $subtotal += $totalItem;

                OrderItem::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $unitPrice,
                    'total' => $totalItem,
                ]);
            }

            $grandTotal = max(0, $subtotal + $oData['shipping'] - $oData['discount']);
            $order->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            // Create status log
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => $oData['status'],
                'created_by' => null,
                'created_at' => $orderDate,
            ]);

            // Create Payments
            if (isset($oData['payment'])) {
                Payment::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'payment_date' => Carbon::now()->subDays($oData['payment']['date_offset'])->toDateString(),
                    'amount' => $oData['payment']['amount'],
                    'payment_method' => $oData['payment']['method'],
                    'notes' => 'Pembayaran otomatis seeder',
                    'created_at' => Carbon::now()->subDays($oData['payment']['date_offset']),
                    'updated_at' => Carbon::now()->subDays($oData['payment']['date_offset']),
                ]);
            }

            // Create Receivables
            if (isset($oData['receivable'])) {
                $rData = $oData['receivable'];
                $dueOffset = $rData['due_offset'];
                
                $receivable = Receivable::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $rData['paid'],
                    'remaining_amount' => $rData['remaining'],
                    'due_date' => Carbon::now()->subDays($dueOffset)->toDateString(),
                    'status' => $rData['status'],
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create payments for this receivable if any
                foreach ($rData['payments'] as $pRec) {
                    Payment::create([
                        'tenant_id' => $tenantId,
                        'order_id' => $order->id,
                        'payment_date' => Carbon::now()->subDays($pRec['date_offset'])->toDateString(),
                        'amount' => $pRec['amount'],
                        'payment_method' => $pRec['method'],
                        'notes' => 'Pembayaran cicilan/tempo seeder',
                        'created_at' => Carbon::now()->subDays($pRec['date_offset']),
                        'updated_at' => Carbon::now()->subDays($pRec['date_offset']),
                    ]);
                }
            }
        }
    }
}
