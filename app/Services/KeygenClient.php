<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeygenClient
{
    protected $apiUrl;
    protected $accountId;
    protected $productId;
    protected $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('keygen.api_url');
        $this->accountId = config('keygen.account_id');
        $this->productId = config('keygen.product_id');
        $this->apiToken = config('keygen.api_token');
    }

    /**
     * Check if Keygen.sh is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountId) && !empty($this->productId);
    }

    /**
     * Validate a license key with Keygen.sh API.
     */
    public function validateLicense(string $licenseKey): array
    {
        if (!$this->isConfigured()) {
            return [
                'valid' => false,
                'error' => 'Keygen.sh is not configured. Please add KEYGEN_ACCOUNT_ID and KEYGEN_PRODUCT_ID to your .env file.',
                'code' => 'NOT_CONFIGURED',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/actions/validate-key";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                ])
                ->post($url, [
                    'meta' => [
                        'key' => $licenseKey,
                        'scope' => [
                            'product' => $this->productId,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $this->parseValidationResponse($response->json());
            }

            $error = $response->json();
            return [
                'valid' => false,
                'error' => $error['errors'][0]['detail'] ?? 'License validation failed',
                'code' => $error['errors'][0]['code'] ?? 'VALIDATION_FAILED',
                'raw_response' => $error,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Keygen.sh connection error: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => 'Unable to connect to license server. Please try again later.',
                'code' => 'CONNECTION_ERROR',
                'is_connection_error' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Keygen.sh validation error: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => 'An error occurred while validating the license.',
                'code' => 'UNKNOWN_ERROR',
            ];
        }
    }

    /**
     * Parse the validation response from Keygen.sh.
     */
    protected function parseValidationResponse(array $response): array
    {
        $meta = $response['meta'] ?? [];
        $data = $response['data'] ?? [];
        $attributes = $data['attributes'] ?? [];

        $isValid = ($meta['valid'] ?? false) === true;
        $validationCode = $meta['code'] ?? 'UNKNOWN';

        // Determine status based on validation code
        $status = 'invalid';
        if ($isValid) {
            $status = 'active';
        } elseif (in_array($validationCode, ['EXPIRED', 'LICENSE_EXPIRED'])) {
            $status = 'expired';
        } elseif (in_array($validationCode, ['REVOKED', 'LICENSE_REVOKED', 'SUSPENDED', 'LICENSE_SUSPENDED'])) {
            $status = 'revoked';
        }

        return [
            'valid' => $isValid,
            'status' => $status,
            'code' => $validationCode,
            'license_id' => $data['id'] ?? null,
            'license_type' => $attributes['metadata']['type'] ?? $attributes['name'] ?? 'standard',
            'licensed_to' => $attributes['metadata']['company'] ?? $attributes['metadata']['name'] ?? $attributes['name'] ?? null,
            'licensee_email' => $attributes['metadata']['email'] ?? null,
            'max_users' => $attributes['maxUsers'] ?? $attributes['metadata']['maxUsers'] ?? $attributes['metadata']['max_users'] ?? null,
            'expiry_date' => $attributes['expiry'] ?? null,
            'support_valid_until' => $attributes['metadata']['supportUntil'] ?? $attributes['metadata']['support_until'] ?? null,
            'metadata' => $attributes['metadata'] ?? [],
            'raw_response' => $response,
        ];
    }

    /**
     * Get license details by ID.
     */
    public function getLicense(string $licenseId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch license details.',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh get license error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }
}