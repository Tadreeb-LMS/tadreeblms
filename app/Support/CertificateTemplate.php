<?php

namespace App\Support;

use App\Models\Config;

class CertificateTemplate
{
    public static function defaults(): array
    {
        return [
            'template' => 'classic-dark',
            'bg_texture' => '',
            'primary_color' => '#d4af37',
            'secondary_color' => '#f5d670',
            'bg_color' => '#1a1a2e',
            'text_color' => '#ffffff',
            'cert_label' => 'Certificate of Completion',
            'cert_title' => 'Achievement Award',
            'show_badge' => 1,
            'show_seal' => 1,
            'show_signature' => 1,
            'logo_image' => null,
            'seal_image' => null,
            'signature_image' => null,
        ];
    }

    public static function settings(): array
    {
        try {
            $record = Config::where('key', 'certificate_template_settings')->first();
        } catch (\Throwable $e) {
            return self::defaults();
        }

        if (! $record) {
            return self::defaults();
        }

        $settings = json_decode($record->value, true);

        if (! is_array($settings)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $settings);
    }
}
