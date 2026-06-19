<?php

namespace App\Services\WhatsApp;

interface WhatsAppServiceInterface
{
    /**
     * Send WhatsApp Message.
     *
     * @param string $phone
     * @param string $message
     * @param array $config
     * @return array ['success' => bool, 'response' => string]
     */
    public function sendMessage(string $phone, string $message, array $config): array;
}
