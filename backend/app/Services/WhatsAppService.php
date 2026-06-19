<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\WhatsAppLog;
use App\Services\WhatsApp\FonnteService;
use App\Services\WhatsApp\WaGatewayService;
use App\Services\WhatsApp\MetaWhatsAppService;
use Exception;

class WhatsAppService
{
    public function send(Tenant $tenant, string $phone, string $message, ?int $customerId = null): bool
    {
        // 1. Resolve configuration from tenant (Mock default configurations for MVP)
        // In real execution, these settings would be retrieved from tenant catalog settings or credentials tables
        $provider = env('WHATSAPP_PROVIDER', 'fonnte'); 
        $config = [
            'token' => env('WHATSAPP_TOKEN'),
            'url' => env('WHATSAPP_GATEWAY_URL'),
            'api_key' => env('WHATSAPP_API_KEY'),
            'phone_number_id' => env('WHATSAPP_META_PHONE_NUMBER_ID'),
            'access_token' => env('WHATSAPP_META_ACCESS_TOKEN'),
        ];

        // 2. Instantiate correct driver
        $driver = match ($provider) {
            'fonnte' => new FonnteService(),
            'gateway' => new WaGatewayService(),
            'meta' => new MetaWhatsAppService(),
            default => throw new Exception("Unsupported WhatsApp provider: {$provider}"),
        };

        // 3. Deliver message
        $result = $driver->sendMessage($phone, $message, $config);

        // 4. Record WhatsApp Log
        WhatsAppLog::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerId,
            'phone' => $phone,
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'provider' => $provider,
            'response' => json_decode($result['response'], true) ?: ['raw' => $result['response']],
        ]);

        return $result['success'];
    }
}
