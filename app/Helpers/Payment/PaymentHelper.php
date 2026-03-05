<?php

namespace App\Helpers\Payment;

use App\Services\ExternalApps\ExternalAppService;

class PaymentHelper
{
    /**
     * Get all enabled gateway slugs.
     *
     * @return array
     */
    public static function getEnabledGateways(): array
    {
        $list = ExternalAppService::staticGetModuleEnv('payment-gateways', 'GATEWAY_ENABLED_LIST') ?: '';
        if (empty($list)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $list)));
    }

    /**
     * Check if a specific gateway is enabled.
     *
     * @param  string  $slug  Gateway slug (e.g. 'stripe', 'paypal')
     * @return bool
     */
    public static function isGatewayEnabled(string $slug): bool
    {
        return in_array($slug, self::getEnabledGateways(), true);
    }

    /**
     * Get configuration for a specific gateway.
     *
     * @param  string  $slug  Gateway slug (e.g. 'stripe', 'paypal')
     * @return array  Keys: enabled, mode, api_key, secret_key, webhook_secret
     */
    public static function getGatewayConfig(string $slug): array
    {
        $prefix = strtoupper($slug);

        return [
            'enabled' => filter_var(
                ExternalAppService::staticGetModuleEnv('payment-gateways', $prefix . '_ENABLED') ?: 'false',
                FILTER_VALIDATE_BOOLEAN
            ),
            'mode' => ExternalAppService::staticGetModuleEnv('payment-gateways', $prefix . '_MODE') ?: 'sandbox',
            'api_key' => ExternalAppService::staticGetModuleEnv('payment-gateways', $prefix . '_API_KEY') ?: '',
            'secret_key' => ExternalAppService::staticGetModuleEnv('payment-gateways', $prefix . '_SECRET_KEY') ?: '',
            'webhook_secret' => ExternalAppService::staticGetModuleEnv('payment-gateways', $prefix . '_WEBHOOK_SECRET') ?: '',
        ];
    }

    /**
     * Get all gateway configurations (enabled gateways only).
     *
     * @return array  Keyed by gateway slug
     */
    public static function getAllEnabledConfigs(): array
    {
        $configs = [];
        foreach (self::getEnabledGateways() as $slug) {
            $configs[$slug] = self::getGatewayConfig($slug);
        }
        return $configs;
    }
}