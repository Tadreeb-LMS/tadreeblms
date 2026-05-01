<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourceLocale = 'en';
$targetLocales = ['ar', 'es', 'fr', 'it'];
$langRoot = $root . '/resources/lang';
$separator = "\n[[[TADREEB_TRANSLATION_SEPARATOR]]]\n";

$translationCache = [];
$placeholderMap = [];
$nextPlaceholder = 0;

foreach ($targetLocales as $locale) {
    $sourceFiles = glob($langRoot . '/' . $sourceLocale . '/*.php') ?: [];
    sort($sourceFiles);

    foreach ($sourceFiles as $sourceFile) {
        $basename = basename($sourceFile);
        $targetFile = $langRoot . '/' . $locale . '/' . $basename;

        if (shouldSkipSourceFileForLocale($basename, $locale, $langRoot)) {
            continue;
        }

        $sourceData = include $sourceFile;
        $targetData = file_exists($targetFile) ? include $targetFile : [];

        $merged = mergeTranslations(
            $sourceData,
            is_array($targetData) ? $targetData : [],
            $locale,
            $translationCache,
            $placeholderMap,
            $nextPlaceholder
        );

        $resolved = resolvePlaceholders($merged, $translationCache, $placeholderMap, $locale, $separator);
        writePhpArrayFile($targetFile, $resolved);

        echo 'Updated ' . str_replace($root . '/', '', $targetFile) . PHP_EOL;
    }
}

function shouldSkipSourceFileForLocale(string $basename, string $locale, string $langRoot): bool
{
    if (strpos($basename, 'license-') !== 0) {
        return false;
    }

    $existingLicenseFiles = glob($langRoot . '/' . $locale . '/license-*.php') ?: [];

    return !empty($existingLicenseFiles) && !in_array($langRoot . '/' . $locale . '/' . $basename, $existingLicenseFiles, true);
}

function mergeTranslations($source, $target, string $locale, array &$translationCache, array &$placeholderMap, int &$nextPlaceholder)
{
    if (is_array($source)) {
        $output = is_array($target) ? $target : [];

        foreach ($source as $key => $value) {
            $output[$key] = mergeTranslations(
                $value,
                array_key_exists($key, $output) ? $output[$key] : null,
                $locale,
                $translationCache,
                $placeholderMap,
                $nextPlaceholder
            );
        }

        return $output;
    }

    if (!is_string($source)) {
        return $target ?? $source;
    }

    if (is_string($target) && $target !== '' && $target !== $source) {
        return $target;
    }

    if (!needsTranslation($source)) {
        return $source;
    }

    $cacheKey = $locale . '|' . md5($source);
    if (!array_key_exists($cacheKey, $translationCache)) {
        $translationCache[$cacheKey] = null;
    }

    $placeholder = '__TRANSLATION_PLACEHOLDER_' . $nextPlaceholder++ . '__';
    $placeholderMap[$placeholder] = [
        'cacheKey' => $cacheKey,
        'locale' => $locale,
        'source' => $source,
    ];

    return $placeholder;
}

function resolvePlaceholders($value, array &$translationCache, array $placeholderMap, string $locale, string $separator)
{
    $pending = [];

    foreach ($placeholderMap as $placeholder => $meta) {
        if ($meta['locale'] !== $locale) {
            continue;
        }

        if ($translationCache[$meta['cacheKey']] === null) {
            $pending[$meta['cacheKey']] = $meta['source'];
        }
    }

    if (!empty($pending)) {
        $translated = translateMany(array_values($pending), $locale, $separator);
        $index = 0;

        foreach (array_keys($pending) as $cacheKey) {
            $translationCache[$cacheKey] = $translated[$index++] ?? $pending[$cacheKey];
        }
    }

    return replacePlaceholders($value, $translationCache, $placeholderMap, $locale);
}

function replacePlaceholders($value, array $translationCache, array $placeholderMap, string $locale)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = replacePlaceholders($item, $translationCache, $placeholderMap, $locale);
        }

        return $value;
    }

    if (!is_string($value) || !array_key_exists($value, $placeholderMap)) {
        return $value;
    }

    $meta = $placeholderMap[$value];
    if ($meta['locale'] !== $locale) {
        return $value;
    }

    return $translationCache[$meta['cacheKey']] ?? $meta['source'];
}

function needsTranslation(string $text): bool
{
    return preg_match('/[A-Za-z]/', $text) === 1;
}

function translateMany(array $texts, string $targetLocale, string $separator): array
{
    $results = [];
    $batchTexts = [];
    $batchMaps = [];
    $batchSizeLimit = 3600;
    $currentSize = 0;

    $flush = function () use (&$results, &$batchTexts, &$batchMaps, &$currentSize, $targetLocale, $separator): void {
        if (empty($batchTexts)) {
            return;
        }

        $translatedBatch = requestBatchTranslation($batchTexts, $batchMaps, $targetLocale, $separator);
        foreach ($translatedBatch as $item) {
            $results[] = $item;
        }

        $batchTexts = [];
        $batchMaps = [];
        $currentSize = 0;
    };

    foreach ($texts as $text) {
        [$protectedText, $map] = protectTranslationTokens($text);
        $projected = $currentSize + strlen($protectedText) + strlen($separator);

        if (!empty($batchTexts) && $projected > $batchSizeLimit) {
            $flush();
        }

        $batchTexts[] = $protectedText;
        $batchMaps[] = $map;
        $currentSize += strlen($protectedText) + strlen($separator);
    }

    $flush();

    return $results;
}

function requestBatchTranslation(array $protectedTexts, array $tokenMaps, string $targetLocale, string $separator): array
{
    $joinedText = implode($separator, $protectedTexts);
    $translatedJoinedText = requestTranslation($joinedText, $targetLocale);
    $translatedParts = explode($separator, $translatedJoinedText);

    if (count($translatedParts) !== count($protectedTexts)) {
        $translatedParts = [];
        foreach ($protectedTexts as $text) {
            $translatedParts[] = requestTranslation($text, $targetLocale);
        }
    }

    $restored = [];
    foreach ($translatedParts as $index => $translatedText) {
        $restored[] = restoreTranslationTokens($translatedText, $tokenMaps[$index] ?? []);
    }

    return $restored;
}

function requestTranslation(string $text, string $targetLocale): string
{
    $query = http_build_query([
        'client' => 'gtx',
        'sl' => 'en',
        'tl' => $targetLocale,
        'dt' => 't',
        'q' => $text,
    ]);

    $url = 'https://translate.googleapis.com/translate_a/single?' . $query;
    $response = @file_get_contents($url);
    if ($response === false) {
        return $text;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !isset($decoded[0]) || !is_array($decoded[0])) {
        return $text;
    }

    $translated = '';
    foreach ($decoded[0] as $segment) {
        if (is_array($segment) && isset($segment[0])) {
            $translated .= $segment[0];
        }
    }

    return $translated !== '' ? $translated : $text;
}

function protectTranslationTokens(string $text): array
{
    $map = [];
    $counter = 0;

    $patterns = [
        '/<[^>]+>/',
        '/https?:\/\/[^\s"\']+|\/\/[^\s"\']+/',
        '/:[A-Za-z_][A-Za-z0-9_]*/',
        '/%[0-9\$\-\.]*[bcdeEfFgGosuxX]/',
        '/\{[0-9A-Za-z_]+\}/',
        '/&[A-Za-z0-9#]+;/',
    ];

    foreach ($patterns as $pattern) {
        $text = preg_replace_callback($pattern, function ($matches) use (&$map, &$counter) {
            $token = '__TOKEN_' . $counter++ . '__';
            $map[$token] = $matches[0];
            return $token;
        }, $text);
    }

    return [$text, $map];
}

function restoreTranslationTokens(string $text, array $map): string
{
    if (empty($map)) {
        return $text;
    }

    return strtr($text, $map);
}

function writePhpArrayFile(string $filePath, array $data): void
{
    if (!is_dir(dirname($filePath))) {
        mkdir(dirname($filePath), 0777, true);
    }

    $content = "<?php\n\nreturn " . exportPhpValue($data, 0) . ";\n";
    file_put_contents($filePath, $content);
}

function exportPhpValue($value, int $indent): string
{
    if (is_array($value)) {
        if ($value === []) {
            return 'array()';
        }

        $indentation = str_repeat('  ', $indent);
        $nextIndentation = str_repeat('  ', $indent + 1);
        $lines = ['array('];

        foreach ($value as $key => $item) {
            $exportedKey = is_int($key) ? $key : var_export($key, true);
            $lines[] = $nextIndentation . $exportedKey . ' => ' . exportPhpValue($item, $indent + 1) . ',';
        }

        $lines[] = $indentation . ')';
        return implode("\n", $lines);
    }

    return var_export($value, true);
}