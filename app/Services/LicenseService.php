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
     * Remove/deactivate the current license.
     */
    public function removeLicense(): bool
    {
        $license = License::getActive();

        if ($license) {
            $license->update(['is_active' => false]);
            return true;
        }

        return false;
    }
}