<?php

namespace App\Services\SmsGateways;

use App\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingGateway implements SmsGatewayInterface
{
    protected $username;
    protected $apiKey;
    protected $senderId;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->username = config('services.africas_talking.username');
        $this->apiKey = config('services.africas_talking.api_key');
        $this->senderId = config('services.africas_talking.sender_id', 'S-REMIND');
        $this->environment = config('services.africas_talking.environment', 'sandbox');
        
        // Sandbox vs Production URLs
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://api.sandbox.africastalking.com/version1'
            : 'https://api.africastalking.com/version1';
    }

    /**
     * Send single SMS
     */
    public function send(string $to, string $message): array
    {
        try {
            $url = "{$this->baseUrl}/messaging";

            Log::info('Africa\'s Talking SMS Request', [
                'to' => $to,
                'message_length' => strlen($message),
                'environment' => $this->environment,
            ]);

            // Préparer les données de la requête
            $postData = [
                'username' => $this->username,
                'to' => $to,
                'message' => $message,
            ];
            
            // En production, ajouter le Sender ID
            // En sandbox, ne pas spécifier de Sender ID (Africa's Talking l'ignore ou génère une erreur)
            if ($this->environment !== 'sandbox' && !empty($this->senderId)) {
                $postData['from'] = $this->senderId;
            }
            
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->asForm()->post($url, $postData);

            Log::info('Africa\'s Talking Raw Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Africa's Talking response structure
                $smsData = $data['SMSMessageData'] ?? [];
                $recipients = $smsData['Recipients'] ?? [];
                
                if (!empty($recipients) && count($recipients) > 0) {
                    $recipient = $recipients[0];
                    $statusCode = $recipient['statusCode'] ?? 0;
                    $status = $recipient['status'] ?? 'Unknown';
                    
                    // Status codes: 101 = Success, 102 = Queued
                    if (in_array($statusCode, [101, 102])) {
                        Log::info('Africa\'s Talking SMS sent successfully', [
                            'to' => $to,
                            'messageId' => $recipient['messageId'] ?? null,
                            'status' => $status,
                            'statusCode' => $statusCode,
                            'cost' => $recipient['cost'] ?? null,
                        ]);

                        return [
                            'success' => true,
                            'message_id' => $recipient['messageId'] ?? null,
                            'cost' => $this->parseCost($recipient['cost'] ?? null),
                            'error' => null,
                        ];
                    }
                }

                // Failed
                $errorMessage = $recipients[0]['status'] ?? 'Unknown error';
                
                Log::warning('Africa\'s Talking SMS failed', [
                    'to' => $to,
                    'response' => $data,
                    'error' => $errorMessage,
                ]);

                return [
                    'success' => false,
                    'message_id' => null,
                    'cost' => null,
                    'error' => $errorMessage,
                ];
            }

            // HTTP error
            Log::error('Africa\'s Talking API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'cost' => null,
                'error' => 'API request failed: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Africa\'s Talking exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'cost' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk SMS (batch processing)
     */
    public function sendBulk(array $recipients): array
    {
        $successCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($recipients as $recipient) {
            $result = $this->send($recipient['phone'], $recipient['message']);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }

            $results[] = array_merge($recipient, $result);
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * Check account balance
     */
    public function getBalance(): ?float
    {
        try {
            $url = "{$this->baseUrl}/user?username={$this->username}";

            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $balance = $data['UserData']['balance'] ?? null;

                Log::info('Africa\'s Talking balance', [
                    'balance' => $balance,
                ]);

                if ($balance) {
                    // Parse balance (format: "KES XXX" or "XOF XXX")
                    preg_match('/[\d.]+/', $balance, $matches);
                    return $matches[0] ? (float) $matches[0] : null;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Africa\'s Talking balance check failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'Africa\'s Talking' . ($this->environment === 'sandbox' ? ' (Sandbox)' : '');
    }

    /**
     * Check if provider is configured and available
     */
    public function isAvailable(): bool
    {
        $available = !empty($this->username) && !empty($this->apiKey);
        
        Log::info('Africa\'s Talking availability check', [
            'available' => $available,
            'username' => $this->username,
            'has_api_key' => !empty($this->apiKey),
        ]);
        
        return $available;
    }

    /**
     * Parse cost from Africa's Talking format
     */
    private function parseCost(?string $cost): ?float
    {
        if (!$cost) {
            return 10.0; // Default 10 FCFA for Côte d'Ivoire
        }

        // Cost format: "KES 0.8000" or "XOF 10"
        preg_match('/[\d.]+/', $cost, $matches);
        
        if (isset($matches[0])) {
            $amount = (float) $matches[0];
            
            // Convert to FCFA if needed
            if (stripos($cost, 'KES') !== false) {
                // 1 KES ≈ 5 FCFA
                return $amount * 5;
            }
            
            if (stripos($cost, 'USD') !== false) {
                // 1 USD ≈ 600 FCFA
                return $amount * 600;
            }
            
            return $amount;
        }

        return 10.0; // Default
    }
}

