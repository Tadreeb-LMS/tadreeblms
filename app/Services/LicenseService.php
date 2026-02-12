<?php

namespace App\Services;

use App\Models\License;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected $keygenClient;

    public function __construct(KeygenClient $keygenClient)
    {
        $this->keygenClient = $keygenClient;
    }

    /**
     * Get the current active license with validation check.
     */
    public function getCurrentLicense(bool $forceRevalidate = false): ?License
    {
        $license = License::getActive();

        if (!$license) {
            return null;
        }

        // Check if revalidation is needed
        if ($forceRevalidate || $license->needsRevalidation()) {
            $this->revalidateLicense($license);
            $license->refresh();
        }

        return $license;
    }

    /**
     * Validate and save a new license key.
     */
    public function activateLicense(string $licenseKey): array
    {
        // Validate with Keygen.sh
        $result = $this->keygenClient->validateLicense($licenseKey);

        if (!$result['valid'] && !($result['is_connection_error'] ?? false)) {
            return [
                'success' => false,
                'message' => $result['error'] ?? 'Invalid license key.',
                'code' => $result['code'] ?? 'INVALID',
            ];
        }

        // If connection error, we can't activate a new license
        if ($result['is_connection_error'] ?? false) {
            return [
                'success' => false,
                'message' => $result['error'],
                'code' => 'CONNECTION_ERROR',
            ];
        }

        // Deactivate any existing active license
        License::where('is_active', true)->update(['is_active' => false]);

        // Create or update license
        $license = License::create([
            'license_key' => $licenseKey,
            'status' => $result['status'] ?? 'active',
            'max_users' => $result['max_users'],
            'license_type' => $result['license_type'],
            'licensed_to' => $result['licensed_to'],
            'licensee_email' => $result['licensee_email'],
            'expiry_date' => $result['expiry_date'] ? now()->parse($result['expiry_date']) : null,
            'support_valid_until' => $result['support_valid_until'] ? now()->parse($result['support_valid_until']) : null,
            'last_validated_at' => now(),
            'validation_response' => $result['raw_response'] ?? null,
            'metadata' => $result['metadata'] ?? null,
            'is_active' => true,
        ]);

        // Auto-sync user count to Keygen after activation
        $this->syncUsersToKeygen();

        return [
            'success' => true,
            'message' => 'License activated successfully.',
            'license' => $license,
        ];
    }

    /**
     * Revalidate an existing license.
     */
    public function revalidateLicense(License $license): array
    {
        $result = $this->keygenClient->validateLicense($license->license_key);

        // If connection error, use cached data (graceful degradation)
        if ($result['is_connection_error'] ?? false) {
            if ($license->isWithinGracePeriod()) {
                return [
                    'success' => true,
                    'message' => 'Using cached license data (license server unreachable).',
                    'cached' => true,
                    'license' => $license,
                ];
            }

            // Outside grace period - mark as needing validation
            return [
                'success' => false,
                'message' => 'License validation required. Please check your internet connection.',
                'code' => 'VALIDATION_REQUIRED',
            ];
        }

        // Update license with new data
        $license->update([
            'status' => $result['status'] ?? ($result['valid'] ? 'active' : 'invalid'),
            'max_users' => $result['max_users'] ?? $license->max_users,
            'license_type' => $result['license_type'] ?? $license->license_type,
            'licensed_to' => $result['licensed_to'] ?? $license->licensed_to,
            'licensee_email' => $result['licensee_email'] ?? $license->licensee_email,
            'expiry_date' => isset($result['expiry_date']) ? now()->parse($result['expiry_date']) : $license->expiry_date,
            'support_valid_until' => isset($result['support_valid_until']) ? now()->parse($result['support_valid_until']) : $license->support_valid_until,
            'last_validated_at' => now(),
            'validation_response' => $result['raw_response'] ?? $license->validation_response,
            'metadata' => $result['metadata'] ?? $license->metadata,
        ]);

        return [
            'success' => $result['valid'],
            'message' => $result['valid'] ? 'License validated successfully.' : ($result['error'] ?? 'License is not valid.'),
            'license' => $license->fresh(),
        ];
    }

    /**
     * Get the count of active users.
     */
    public function getActiveUsersCount(): int
    {
        return User::where('active', 1)->count();
    }

    /**
     * Get license usage statistics.
     */
    public function getUsageStats(): array
    {
        $license = License::getActive();
        $activeUsers = $this->getActiveUsersCount();

        if (!$license) {
            return [
                'has_license' => false,
                'active_users' => $activeUsers,
                'max_users' => null,
                'remaining_users' => null,
                'usage_percentage' => 0,
                'is_exceeded' => false,
                'is_warning' => false,
            ];
        }

        $maxUsers = $license->max_users;
        $remaining = $maxUsers ? max(0, $maxUsers - $activeUsers) : null;
        $usagePercentage = $maxUsers ? min(100, round(($activeUsers / $maxUsers) * 100)) : 0;

        return [
            'has_license' => true,
            'license' => $license,
            'active_users' => $activeUsers,
            'max_users' => $maxUsers,
            'remaining_users' => $remaining,
            'usage_percentage' => $usagePercentage,
            'is_exceeded' => $maxUsers && $activeUsers > $maxUsers,
            'is_warning' => $maxUsers && $activeUsers >= ($maxUsers * 0.9), // 90% threshold
        ];
    }

    /**
     * Check if user limit is exceeded (non-blocking, just returns status).
     */
    public function isUserLimitExceeded(): bool
    {
        $stats = $this->getUsageStats();
        return $stats['is_exceeded'];
    }

    /**
     * Check if a new user can be created (limit not exceeded).
     * Returns array with 'allowed' boolean and 'message' if not allowed.
     */
    public function canCreateUser(): array
    {
        $license = License::getActive();

        if (!$license) {
            // No license = no restrictions (or you can change this to require license)
            return ['allowed' => true];
        }

        $maxUsers = $license->max_users;
        if (!$maxUsers) {
            // Unlimited users
            return ['allowed' => true];
        }

        $activeUsers = $this->getActiveUsersCount();

        if ($activeUsers >= $maxUsers) {
            return [
                'allowed' => false,
                'message' => "User limit reached. Your license allows {$maxUsers} users and you currently have {$activeUsers} active users.",
                'current_users' => $activeUsers,
                'max_users' => $maxUsers,
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $maxUsers - $activeUsers,
        ];
    }

    /**
     * Get the license ID from stored validation response.
     */
    public function getLicenseId(): ?string
    {
        $license = License::getActive();

        if (!$license || !$license->validation_response) {
            return null;
        }

        return $license->validation_response['data']['id'] ?? null;
    }

    /**
     * Called when a user is created - creates user in Keygen.sh.
     */
    public function onUserCreated(User $user = null): array
    {
        $licenseId = $this->getLicenseId();

        if (!$licenseId) {
            Log::info('LicenseService: No active license, skipping user creation in Keygen');
            return ['success' => true, 'skipped' => true];
        }

        if (!$user) {
            Log::warning('LicenseService: No user provided for Keygen creation');
            return ['success' => false, 'error' => 'No user provided'];
        }

        // Create user in Keygen and attach to license
        $result = $this->keygenClient->createAndAttachUser(
            $licenseId,
            $user->email,
            $user->first_name . ' ' . $user->last_name
        );

        if ($result['success']) {
            Log::info('LicenseService: User created in Keygen', [
                'email' => $user->email,
            ]);
        } else {
            Log::warning('LicenseService: Failed to create user in Keygen', [
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        return $result;
    }

    /**
     * Called when a user is deleted - removes user from Keygen.sh.
     */
    public function onUserDeleted(User $user = null): array
    {
        $licenseId = $this->getLicenseId();

        if (!$licenseId) {
            Log::info('LicenseService: No active license, skipping user deletion in Keygen');
            return ['success' => true, 'skipped' => true];
        }

        if (!$user) {
            Log::warning('LicenseService: No user provided for Keygen deletion');
            return ['success' => false, 'error' => 'No user provided'];
        }

        // Delete user from Keygen by email
        $result = $this->keygenClient->deleteUserByEmail($user->email);

        if ($result['success']) {
            Log::info('LicenseService: User deleted from Keygen', [
                'email' => $user->email,
            ]);
        } else {
            Log::warning('LicenseService: Failed to delete user from Keygen', [
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        return $result;
    }

    /**
     * Remove/deactivate the current license.
     */
    public function removeLicense(): bool
    {
        $license = License::getActive();

        if ($license) {
            $licenseId = $license->validation_response['data']['id'] ?? null;

            if ($licenseId) {
                $this->keygenClient->removeAllUsersFromLicense($licenseId);
            }

            $license->update(['is_active' => false]);
            return true;
        }

        return false;
    }

    /**
     * Sync all active users to Keygen license.
     */
    public function syncUsersToKeygen(): array
    {
        $licenseId = $this->getLicenseId();

        if (!$licenseId) {
            return [
                'success' => false,
                'error' => 'No active license found.',
            ];
        }

        // Remove existing users first
        $this->keygenClient->removeAllUsersFromLicense($licenseId);

        // Get all active users
        $users = User::where('active', 1)->get()->map(function ($user) {
            return [
                'email' => $user->email,
                'name' => trim($user->first_name . ' ' . $user->last_name),
            ];
        })->toArray();

        // Sync all users
        return $this->keygenClient->syncAllUsers($licenseId, $users);
    }

    /**
     * Remove all users from Keygen license.
     */
    public function removeUsersFromKeygen(): array
    {
        $licenseId = $this->getLicenseId();

        if (!$licenseId) {
            return [
                'success' => false,
                'error' => 'No active license found.',
            ];
        }

        return $this->keygenClient->removeAllUsersFromLicense($licenseId);
    }
}