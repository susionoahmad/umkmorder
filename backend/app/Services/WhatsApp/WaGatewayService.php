<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaGatewayService implements WhatsAppServiceInterface
{
    public function sendMessage(string $phone, string $message, array $config): array
    {
        $url = $config['url'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        if (!$url) {
            Log::info("WAGateway Mock Message to {$phone}: {$message}");
            return ['success' => true, 'response' => 'mocked'];
        }

        try {
            $response = Http::post($url, [
                'api_key' => $apiKey,
                'phone' => $phone,
                'message' => $message,
            ]);

            return [
                'success' => $response->successful(),
                'response' => $response->body()
            ];
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'response' => $exception->getMessage()
            ];
        }
    }
}
