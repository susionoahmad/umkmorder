<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService implements WhatsAppServiceInterface
{
    public function sendMessage(string $phone, string $message, array $config): array
    {
        $token = $config['token'] ?? null;

        if (!$token) {
            Log::info("Fonnte Mock Message to {$phone}: {$message}");
            return ['success' => true, 'response' => 'mocked'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
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
