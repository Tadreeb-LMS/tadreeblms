<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateLanguageKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:validate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate translation keys follow module.key_name and detect ambiguous duplicates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $langRoot = resource_path('lang');
        $files = glob($langRoot . '/*/*.php') ?: [];

        $invalidKeys = [];
        $ambiguousDuplicates = [];

        foreach ($files as $file) {
            $module = pathinfo($file, PATHINFO_FILENAME);

            $payload = require $file;
            if (!is_array($payload)) {
                continue;
            }

            $normalizedMap = [];
            $this->scanTranslationArray($payload, '', $file, $invalidKeys, $ambiguousDuplicates, $normalizedMap);
        }

        if (empty($invalidKeys) && empty($ambiguousDuplicates)) {
            $this->info('All translation keys are standardized.');
            return 0;
        }

        if (!empty($invalidKeys)) {
            $this->error('Invalid key format found:');
            foreach ($invalidKeys as $item) {
                $this->line(sprintf('- %s :: %s (%s)', $item['file'], $item['key'], $item['reason']));
            }
        }

        if (!empty($ambiguousDuplicates)) {
            $this->error('Ambiguous duplicate keys found after normalization:');
            foreach ($ambiguousDuplicates as $item) {
                $this->line(sprintf('- %s :: [%s]', $item['file'], implode(', ', $item['keys'])));
            }
        }

        return 1;
    }

    /**
     * Recursively scan translation arrays.
     *
     * @param array $array
     * @param string $prefix
     * @param string $file
     * @param array $invalidKeys
     * @param array $ambiguousDuplicates
     * @param array $normalizedMap
     * @return void
     */
    private function scanTranslationArray(
        array $array,
        string $prefix,
        string $file,
        array &$invalidKeys,
        array &$ambiguousDuplicates,
        array &$normalizedMap
    ) {
        foreach ($array as $key => $value) {
            $keyString = (string) $key;
            if ($keyString === '') {
                continue;
            }

            $fullKey = $prefix === '' ? $keyString : $prefix . '.' . $keyString;
            $isLocaleCodeKey = str_ends_with($prefix, 'langs');

            $segments = explode('.', $fullKey);
            foreach ($segments as $segment) {
                if ($segment === '') {
                    continue;
                }

                if ($isLocaleCodeKey && $segment === $keyString) {
                    if (!preg_match('/^[a-z]{2,8}(?:[_-][a-z0-9]{2,8})*$/i', $segment)) {
                        $invalidKeys[] = [
                            'file' => $file,
                            'key' => $fullKey,
                            'reason' => 'locale code segment `' . $segment . '` is invalid',
                        ];
                    }
                    break;
                }

                if (!preg_match('/^[a-z0-9_]+$/', $segment)) {
                    $invalidKeys[] = [
                        'file' => $file,
                        'key' => $fullKey,
                        'reason' => 'segment `' . $segment . '` is not snake_case',
                    ];
                    break;
                }
            }

            $normalizedSegments = array_values(array_filter($segments, function ($segment) {
                return $segment !== '';
            }));
            $normalized = implode('.', array_map([$this, 'normalizeSegment'], $normalizedSegments));
            if ($normalized !== '' && isset($normalizedMap[$normalized]) && $normalizedMap[$normalized] !== $fullKey) {
                $ambiguousDuplicates[$file . '::' . $normalized] = [
                    'file' => $file,
                    'keys' => [$normalizedMap[$normalized], $fullKey],
                ];
            } elseif ($normalized !== '') {
                $normalizedMap[$normalized] = $fullKey;
            }

            if (is_array($value)) {
                $this->scanTranslationArray($value, $fullKey, $file, $invalidKeys, $ambiguousDuplicates, $normalizedMap);
            }
        }
    }

    /**
     * Normalize a segment to snake_case-like form.
     *
     * @param string $segment
     * @return string
     */
    private function normalizeSegment(string $segment): string
    {
        $segment = strtolower($segment);
        $segment = preg_replace('/[^a-z0-9]+/', '_', $segment) ?? $segment;
        $segment = trim($segment, '_');

        return $segment;
    }
}
