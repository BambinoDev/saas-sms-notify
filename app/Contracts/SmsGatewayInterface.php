<?php

namespace App\Contracts;

interface SmsGatewayInterface
{
    /**
     * Send SMS
     * 
     * @param string $to Phone number (E.164 format: +225XXXXXXXXXX)
     * @param string $message SMS content
     * @return array ['success' => bool, 'message_id' => string|null, 'cost' => float|null, 'error' => string|null]
     */
    public function send(string $to, string $message): array;

    /**
     * Send bulk SMS
     * 
     * @param array $recipients Array of ['phone' => string, 'message' => string]
     * @return array ['success_count' => int, 'failed_count' => int, 'results' => array]
     */
    public function sendBulk(array $recipients): array;

    /**
     * Check balance
     * 
     * @return float|null Balance in FCFA
     */
    public function getBalance(): ?float;

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if provider is available and configured
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}

