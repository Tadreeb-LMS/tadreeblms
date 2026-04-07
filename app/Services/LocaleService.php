<?php

namespace App\Services;

use App\Models\Locale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LocaleService
{
    public function supportedLocales(): array
    {
        $locales = [];

        if (Schema::hasTable('locales')) {
            $locales = Locale::query()
                ->pluck('short_name')
                ->filter()
                ->map(function ($locale) {
                    return strtolower(trim((string) $locale));
                })
                ->unique()
                ->values()
                ->toArray();
        }

        if (empty($locales)) {
            $locales = array_map('strtolower', array_keys((array) config('locale.languages', [])));
        }

        if (empty($locales)) {
            $locales = [strtolower((string) config('app.locale', 'en'))];
        }

        return $locales;
    }

    public function defaultLocale(): string
    {
        $default = $this->normalizeLocale(config('app.locale', 'en'));

        return $this->isSupported($default) ? $default : $this->supportedLocales()[0];
    }

    public function normalizeLocale($locale): string
    {
        return strtolower(trim((string) $locale));
    }

    public function isSupported($locale): bool
    {
        if ($locale === null || $locale === '') {
            return false;
        }

        return in_array($this->normalizeLocale($locale), $this->supportedLocales(), true);
    }

    public function resolveFromRequest(Request $request): string
    {
        $candidates = [
            $request->route('lang'),
            $request->query('lang'),
            $request->session()->get('locale'),
            $this->mapLegacyUserLocale(optional($request->user())->fav_lang ?? null),
            $this->defaultLocale(),
        ];

        foreach ($candidates as $candidate) {
            if ($this->isSupported($candidate)) {
                return $this->normalizeLocale($candidate);
            }
        }

        return $this->defaultLocale();
    }

    public function apply(string $locale): void
    {
        $locale = $this->isSupported($locale)
            ? $this->normalizeLocale($locale)
            : $this->defaultLocale();

        app()->setLocale($locale);
        config()->set('app.locale', $locale);

        Carbon::setLocale($locale);
        setlocale(LC_TIME, $this->phpLocaleFor($locale));
    }

    public function persistSelection(Request $request, string $locale): void
    {
        $locale = $this->normalizeLocale($locale);
        $displayType = $this->displayTypeFor($locale);

        $request->session()->put('locale', $locale);
        $request->session()->put('display_type', $displayType);

        if ($displayType === 'rtl') {
            $request->session()->put('lang-rtl', true);
            return;
        }

        $request->session()->forget('lang-rtl');
    }

    public function displayTypeFor(string $locale): string
    {
        $locale = $this->normalizeLocale($locale);

        if (Schema::hasTable('locales')) {
            $displayType = Locale::query()
                ->where('short_name', $locale)
                ->value('display_type');

            if (in_array($displayType, ['ltr', 'rtl'], true)) {
                return $displayType;
            }
        }

        $languages = (array) config('locale.languages', []);
        if (array_key_exists($locale, $languages) && isset($languages[$locale][2])) {
            return $languages[$locale][2] ? 'rtl' : 'ltr';
        }

        return config('app.display_type', 'ltr');
    }

    protected function phpLocaleFor(string $locale): string
    {
        $languages = (array) config('locale.languages', []);
        if (array_key_exists($locale, $languages) && isset($languages[$locale][1])) {
            return (string) $languages[$locale][1];
        }

        return (string) config('app.locale_php', 'en_US');
    }

    protected function mapLegacyUserLocale($legacyLocale): ?string
    {
        if ($legacyLocale === null || $legacyLocale === '') {
            return null;
        }

        $legacyLocale = strtolower(trim((string) $legacyLocale));
        $map = [
            'english' => 'en',
            'arabic' => 'ar',
            'french' => 'fr',
            'spanish' => 'es',
            'italian' => 'it',
        ];

        return $map[$legacyLocale] ?? $legacyLocale;
    }
}