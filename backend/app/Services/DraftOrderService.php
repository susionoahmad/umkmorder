<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DraftOrderService
{
    public function __construct(
        private ShippingService $shippingService,
        private PricingService $pricingService,
    ) {}
    public function createDraftOrder(Tenant $tenant, array $data): Order
    {
        return DB::transaction(function () use ($tenant, $data) {
            // 1. Resolve Customer by WhatsApp number
            $whatsapp = preg_replace('/[^0-9]/', '', $data['customer_whatsapp']);
            
            // Format to Indonesian standard: remove leading 0 or 62
            if (str_starts_with($whatsapp, '0')) {
                $whatsapp = '62' . substr($whatsapp, 1);
            }
            
            $customer = Customer::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('whatsapp', $whatsapp)
                ->first();

            if (!$customer) {
                $customer = Customer::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $data['customer_name'],
                    'whatsapp' => $whatsapp,
                    'address' => $data['customer_address'] ?? null,
                    'source' => 'catalog',
                ]);
            }

            // 2. Generate Invoice Number: INV/YYYYMMDD/{RANDOM_4_DIGIT}
            $datePrefix = Carbon::now()->format('Ymd');
            $randomSuffix = rand(1000, 9999);
            $invoiceNumber = "INV/{$datePrefix}/{$randomSuffix}";

            // 3. Process Items & calculate totals
            $subtotal = 0.00;
            $orderItemsData = [];

            foreach ($data['items'] as $item) {
                $product = Product::withoutGlobalScopes()
                    ->with('priceTiers')
                    ->where('tenant_id', $tenant->id)
                    ->where('id', $item['product_id'])
                    ->firstOrFail();

                // Resolve tier price based on quantity
                $pricing = $this->pricingService->calculatePrice($product, (int) $item['quantity']);
                $unitPrice = $pricing['unit_price'];
                $total     = $pricing['subtotal'];
                $subtotal += $total;

                $orderItemsData[] = [
                    'tenant_id'  => $tenant->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $unitPrice,
                    'total'      => $total,
                ];
            }

            $discount = $data['discount'] ?? 0.00;

            // Calculate shipping cost via ShippingService
            $customerLat  = isset($data['customer_lat'])  ? (float) $data['customer_lat']  : null;
            $customerLng  = isset($data['customer_lng'])  ? (float) $data['customer_lng']  : null;
            $zoneName     = $data['shipping_zone'] ?? null;
            $orderType    = $data['order_type'] ?? 'delivery';
            $paymentPreference = $data['payment_preference'] ?? 'cash';

            $shipping = $this->shippingService->calculate(
                $tenant,
                $orderType,
                $customerLat,
                $customerLng,
                $zoneName
            );

            $shippingCost = $shipping['cost'];
            $grandTotal   = $subtotal - $discount + $shippingCost;

            // 4. Create Order Header
            $order = Order::withoutGlobalScopes()->create([
                'tenant_id'            => $tenant->id,
                'customer_id'          => $customer->id,
                'invoice_number'       => $invoiceNumber,
                'order_date'           => Carbon::now()->toDateString(),
                'order_type'           => $orderType,
                'payment_preference'   => $paymentPreference,
                'status'               => 'draft',
                'source'               => 'catalog',
                'subtotal'             => $subtotal,
                'discount'             => $discount,
                'shipping_cost'        => $shippingCost,
                'shipping_distance_km' => $shipping['distance_km'],
                'shipping_zone'        => $shipping['zone'],
                'grand_total'          => $grandTotal,
                'notes'                => $data['notes'] ?? null,
                'location_link'        => $data['location_link'] ?? null,
                'expires_at'           => Carbon::now()->addHours(24),
            ]);

            // 5. Create Order Items relations
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::withoutGlobalScopes()->create($itemData);
            }

            // 6. Write status log
            OrderStatusLog::create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'draft',
                'created_by' => null,
                'created_at' => Carbon::now(),
            ]);

            return $order;
        });
    }

    public function generateWhatsAppRedirectUrl(Tenant $tenant, Order $order): string
    {
        $order->load(['customer', 'items.product']);

        $message = "Halo {$tenant->name}\n\n";
        $message .= "Saya ingin memesan:\n";
        $message .= "No Invoice: {$order->invoice_number}\n\n";
        $message .= "Rincian Pesanan:\n";

        foreach ($order->items as $item) {
            $subtotalFormatted = number_format($item->total, 0, ',', '.');
            $message .= "- {$item->product->name} x {$item->quantity} (Rp {$subtotalFormatted})\n";
        }

        $subtotalFormatted  = number_format($order->subtotal, 0, ',', '.');
        $shippingFormatted  = number_format($order->shipping_cost, 0, ',', '.');
        $totalFormatted     = number_format($order->grand_total, 0, ',', '.');

        $message .= "\nSubtotal      : Rp {$subtotalFormatted}\n";
        if ((float)$order->shipping_cost > 0) {
            if ($order->shipping_distance_km) {
                $message .= "Ongkir ({$order->shipping_distance_km} km): Rp {$shippingFormatted}\n";
            } elseif ($order->shipping_zone) {
                $message .= "Ongkir ({$order->shipping_zone}): Rp {$shippingFormatted}\n";
            } else {
                $message .= "Ongkir        : Rp {$shippingFormatted}\n";
            }
        } else {
            $message .= "Ongkir        : Gratis\n";
        }
        $message .= "Total Bayar   : Rp {$totalFormatted}\n";
        $message .= "Preferensi Pembayaran: " . $this->formatPaymentPreference($order->payment_preference) . "\n";

        $message .= "\nNama:\n" . $order->customer->name . "\n";
        $message .= "\nAlamat:\n" . ($order->customer->address ?? '-') . "\n";

        if ($order->order_type === 'delivery' && $order->location_link) {
            $loc = trim($order->location_link);
            if (!str_starts_with($loc, 'http')) {
                if (preg_match('/^(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)$/', $loc, $matches)) {
                    $locUrl = "https://maps.google.com/?q={$matches[1]},{$matches[2]}";
                } else {
                    $locUrl = "https://maps.google.com/?q=" . urlencode($loc);
                }
            } else {
                $locUrl = $loc;
            }
            $message .= "\nPin Lokasi:\n" . $locUrl . "\n";
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $dashboardLink = "{$frontendUrl}/dashboard/orders";
        $message .= "\nManajemen Pesanan:\nBuka link berikut untuk mengelola pesanan ini. Jika belum login, Anda akan diarahkan untuk login terlebih dahulu:\n{$dashboardLink}\n";

        $encodedMessage = urlencode($message);
        
        // Clean tenant phone (make sure it has country code prefix e.g. 62)
        $phone = preg_replace('/[^0-9]/', '', $tenant->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    private function formatPaymentPreference(?string $preference): string
    {
        return match ($preference) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'tempo' => 'Tempo',
            'credit' => 'Kredit',
            default => 'Tunai',
        };
    }
}
