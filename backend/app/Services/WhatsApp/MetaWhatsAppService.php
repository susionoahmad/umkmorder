<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppService implements WhatsAppServiceInterface
{
    public function sendMessage(string $phone, string $message, array $config): array
    {
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (!$phoneNumberId || !$accessToken) {
            Log::info("Meta WhatsApp Mock Message to {$phone}: {$message}");
            return ['success' => true, 'response' => 'mocked'];
        }

        try {
            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
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
