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
     * Check if API token is configured for authenticated requests.
     */
    public function hasApiToken(): bool
    {
        return !empty($this->apiToken);
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

    /**
     * Increment license usage (when a user is created).
     */
    public function incrementUsage(string $licenseId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            Log::warning('Keygen.sh: Cannot increment usage - not configured or no API token');
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/actions/increment-usage";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Keygen.sh: Usage incremented successfully', [
                    'license_id' => $licenseId,
                    'uses' => $data['data']['attributes']['uses'] ?? null,
                ]);
                return [
                    'success' => true,
                    'uses' => $data['data']['attributes']['uses'] ?? null,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            Log::error('Keygen.sh: Failed to increment usage', ['response' => $error]);
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to increment usage',
                'code' => $error['errors'][0]['code'] ?? 'INCREMENT_FAILED',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh increment usage error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Decrement license usage (when a user is deleted/deactivated).
     */
    public function decrementUsage(string $licenseId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            Log::warning('Keygen.sh: Cannot decrement usage - not configured or no API token');
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/actions/decrement-usage";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Keygen.sh: Usage decremented successfully', [
                    'license_id' => $licenseId,
                    'uses' => $data['data']['attributes']['uses'] ?? null,
                ]);
                return [
                    'success' => true,
                    'uses' => $data['data']['attributes']['uses'] ?? null,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            Log::error('Keygen.sh: Failed to decrement usage', ['response' => $error]);
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to decrement usage',
                'code' => $error['errors'][0]['code'] ?? 'DECREMENT_FAILED',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh decrement usage error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Reset license usage to zero.
     */
    public function resetUsage(string $licenseId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/actions/reset-usage";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'uses' => $data['data']['attributes']['uses'] ?? 0,
                ];
            }

            $error = $response->json();
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to reset usage',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh reset usage error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Set license usage to a specific count (to sync with actual user count).
     * Uses reset + increment approach since direct PATCH is not allowed.
     */
    public function setUsage(string $licenseId, int $count): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            Log::warning('Keygen.sh: Cannot set usage - not configured or no API token');
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            Log::info('Keygen.sh: Attempting to set usage', [
                'license_id' => $licenseId,
                'target_count' => $count,
            ]);

            // First, reset usage to 0
            $resetResult = $this->resetUsage($licenseId);
            if (!$resetResult['success']) {
                Log::error('Keygen.sh: Failed to reset usage', $resetResult);
                return $resetResult;
            }

            // If count is 0, we're done
            if ($count === 0) {
                return [
                    'success' => true,
                    'uses' => 0,
                ];
            }

            // Now increment to reach the target count
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/actions/increment-usage";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url, [
                    'meta' => [
                        'increment' => $count,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $finalUses = $data['data']['attributes']['uses'] ?? $count;
                Log::info('Keygen.sh: Usage set successfully', [
                    'license_id' => $licenseId,
                    'uses' => $finalUses,
                ]);
                return [
                    'success' => true,
                    'uses' => $finalUses,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            Log::error('Keygen.sh: Failed to increment usage', [
                'response' => $error,
                'status' => $response->status(),
            ]);
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to set usage',
                'code' => $error['errors'][0]['code'] ?? 'SET_USAGE_FAILED',
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Keygen.sh set usage connection error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server: ' . $e->getMessage(),
                'is_connection_error' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Keygen.sh set usage error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Get current usage count from license.
     */
    public function getCurrentUsage(string $licenseId): array
    {
        $result = $this->getLicense($licenseId);

        if (!$result['success']) {
            return $result;
        }

        $uses = $result['data']['data']['attributes']['uses'] ?? 0;
        $maxUses = $result['data']['data']['attributes']['maxUses'] ?? null;

        return [
            'success' => true,
            'uses' => $uses,
            'max_uses' => $maxUses,
            'remaining' => $maxUses ? max(0, $maxUses - $uses) : null,
        ];
    }

    /**
     * Get all users attached to a license.
     */
    public function getLicenseUsers(string $licenseId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/users";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'users' => $data['data'] ?? [],
                ];
            }

            $error = $response->json();
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to get license users',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh get license users error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Detach a user from a license.
     */
    public function detachUserFromLicense(string $licenseId, string $userId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/users";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->delete($url, [
                    'data' => [
                        [
                            'type' => 'users',
                            'id' => $userId,
                        ],
                    ],
                ]);

            if ($response->successful() || $response->status() === 204) {
                return ['success' => true];
            }

            $error = $response->json();
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to detach user',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh detach user error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Remove all users from a license.
     */
    public function removeAllUsersFromLicense(string $licenseId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            Log::warning('Keygen.sh: Cannot remove users - not configured or no API token');
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            // First, get all users attached to this license
            $usersResult = $this->getLicenseUsers($licenseId);

            if (!$usersResult['success']) {
                return $usersResult;
            }

            $users = $usersResult['users'];

            if (empty($users)) {
                Log::info('Keygen.sh: No users to remove from license', ['license_id' => $licenseId]);
                return [
                    'success' => true,
                    'removed' => 0,
                ];
            }

            // Detach all users at once
            $userIds = array_map(function ($user) {
                return [
                    'type' => 'users',
                    'id' => $user['id'],
                ];
            }, $users);

            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/users";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->delete($url, [
                    'data' => $userIds,
                ]);

            if ($response->successful() || $response->status() === 204) {
                Log::info('Keygen.sh: All users removed from license', [
                    'license_id' => $licenseId,
                    'removed' => count($users),
                ]);
                return [
                    'success' => true,
                    'removed' => count($users),
                ];
            }

            $error = $response->json();
            Log::error('Keygen.sh: Failed to remove users from license', ['response' => $error]);
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to remove users from license',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh remove all users error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Create a user in Keygen.sh.
     */
    public function createUser(string $email, string $name): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/users";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url, [
                    'data' => [
                        'type' => 'users',
                        'attributes' => [
                            'email' => $email,
                            'firstName' => explode(' ', $name)[0] ?? $name,
                            'lastName' => explode(' ', $name, 2)[1] ?? '',
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'user_id' => $data['data']['id'] ?? null,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            $errorCode = $error['errors'][0]['code'] ?? '';

            // If user already exists, try to find them
            if ($errorCode === 'EMAIL_TAKEN' || str_contains($error['errors'][0]['detail'] ?? '', 'already')) {
                $existingUser = $this->findUserByEmail($email);
                if ($existingUser['success']) {
                    return [
                        'success' => true,
                        'user_id' => $existingUser['user_id'],
                        'already_exists' => true,
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to create user',
                'code' => $errorCode,
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh create user error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Find a user by email.
     */
    public function findUserByEmail(string $email): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            // Fetch all users and find exact email match
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/users";
            $page = 1;
            $perPage = 100;

            while (true) {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/vnd.api+json',
                        'Authorization' => 'Bearer ' . $this->apiToken,
                    ])
                    ->get($url, [
                        'page[number]' => $page,
                        'page[size]' => $perPage,
                    ]);

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'error' => 'Failed to search for user',
                    ];
                }

                $data = $response->json();
                $users = $data['data'] ?? [];

                // Search for exact email match
                foreach ($users as $user) {
                    if (isset($user['attributes']['email']) &&
                        strtolower($user['attributes']['email']) === strtolower($email)) {
                        Log::debug("Keygen.sh: Found user by email", ['email' => $email, 'id' => $user['id']]);
                        return [
                            'success' => true,
                            'user_id' => $user['id'],
                            'data' => $user,
                        ];
                    }
                }

                // Check if there are more pages
                if (count($users) < $perPage) {
                    break;
                }
                $page++;
            }

            Log::debug("Keygen.sh: User not found by email", ['email' => $email]);
            return [
                'success' => false,
                'error' => 'User not found',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh find user error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Attach a user to a license.
     */
    public function attachUserToLicense(string $licenseId, string $userId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/licenses/{$licenseId}/users";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->post($url, [
                    'data' => [
                        [
                            'type' => 'users',
                            'id' => $userId,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                Log::debug("Keygen.sh: Successfully attached user {$userId} to license {$licenseId}");
                return ['success' => true];
            }

            $error = $response->json();
            $errorCode = $error['errors'][0]['code'] ?? '';
            $errorDetail = $error['errors'][0]['detail'] ?? '';

            Log::warning("Keygen.sh: Attach user response", [
                'status' => $response->status(),
                'code' => $errorCode,
                'detail' => $errorDetail,
                'user_id' => $userId,
                'license_id' => $licenseId,
            ]);

            // If user is already attached, treat as success
            if (str_contains($errorCode, 'ALREADY') || str_contains($errorDetail, 'already') || str_contains($errorDetail, 'attached')) {
                return ['success' => true, 'already_attached' => true];
            }

            return [
                'success' => false,
                'error' => $errorDetail ?: 'Failed to attach user to license',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh attach user error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Create a user and attach them to a license.
     */
    public function createAndAttachUser(string $licenseId, string $email, string $name): array
    {
        // First create the user
        $createResult = $this->createUser($email, $name);

        if (!$createResult['success']) {
            Log::warning("Keygen.sh: Failed to create user {$email}", ['result' => $createResult]);
            return $createResult;
        }

        $userId = $createResult['user_id'];

        if (!$userId) {
            Log::warning("Keygen.sh: User created but no user_id returned for {$email}", ['result' => $createResult]);
            return [
                'success' => false,
                'error' => 'User created but no user ID returned',
            ];
        }

        Log::debug("Keygen.sh: User {$email} created/found with ID {$userId}, now attaching to license");

        // Then attach to license
        $attachResult = $this->attachUserToLicense($licenseId, $userId);

        if (!$attachResult['success']) {
            // User was created but attach failed
            Log::warning("Keygen.sh: Failed to attach user {$email} to license", ['result' => $attachResult]);
            return [
                'success' => false,
                'error' => 'User created but failed to attach to license: ' . ($attachResult['error'] ?? ''),
                'user_id' => $userId,
            ];
        }

        Log::debug("Keygen.sh: User {$email} successfully attached to license");

        return [
            'success' => true,
            'user_id' => $userId,
            'already_existed' => $createResult['already_exists'] ?? false,
        ];
    }

    /**
     * Delete a user by email.
     */
    public function deleteUserByEmail(string $email): array
    {
        // First find the user
        $findResult = $this->findUserByEmail($email);

        if (!$findResult['success']) {
            // User not found is not an error for deletion
            if (($findResult['error'] ?? '') === 'User not found') {
                return [
                    'success' => true,
                    'already_deleted' => true,
                ];
            }
            return $findResult;
        }

        $userId = $findResult['user_id'];

        // Delete the user
        return $this->deleteUser($userId);
    }

    /**
     * Delete a user by ID.
     */
    public function deleteUser(string $userId): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        try {
            $url = "{$this->apiUrl}/accounts/{$this->accountId}/users/{$userId}";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])
                ->delete($url);

            if ($response->successful() || $response->status() === 204) {
                return ['success' => true];
            }

            $error = $response->json();
            return [
                'success' => false,
                'error' => $error['errors'][0]['detail'] ?? 'Failed to delete user',
            ];

        } catch (\Exception $e) {
            Log::error('Keygen.sh delete user error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to connect to license server.',
                'is_connection_error' => true,
            ];
        }
    }

    /**
     * Sync all local users to Keygen.sh license.
     * Creates users that don't exist and attaches them to the license.
     */
    public function syncAllUsers(string $licenseId, array $users): array
    {
        if (!$this->isConfigured() || !$this->hasApiToken()) {
            Log::warning('Keygen.sh: Cannot sync users - not configured or no API token');
            return [
                'success' => false,
                'error' => 'Keygen.sh is not fully configured.',
            ];
        }

        $created = 0;
        $attached = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            $email = $user['email'] ?? null;
            $name = $user['name'] ?? '';

            if (!$email) {
                $failed++;
                $errors[] = "User has no email";
                continue;
            }

            $result = $this->createAndAttachUser($licenseId, $email, $name);

            if ($result['success']) {
                if ($result['already_existed'] ?? false) {
                    $attached++;
                } else {
                    $created++;
                }
            } else {
                $failed++;
                $errors[] = "{$email}: " . ($result['error'] ?? 'Unknown error');
            }
        }

        Log::info('Keygen.sh: Users synced', [
            'license_id' => $licenseId,
            'created' => $created,
            'attached' => $attached,
            'failed' => $failed,
        ]);

        return [
            'success' => ($created + $attached) > 0,
            'created' => $created,
            'attached' => $attached,
            'failed' => $failed,
            'errors' => $errors,
            'total' => count($users),
        ];
    }
}