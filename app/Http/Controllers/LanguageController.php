<?php

namespace App\Http\Controllers;

use App\Services\LocaleService;
use Illuminate\Http\Request;

/**
 * Class LanguageController.
 */
class LanguageController extends Controller
{
    /**
     * @param $locale
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function swap($locale, Request $request, LocaleService $localeService)
    {
        $normalizedLocale = $localeService->normalizeLocale($locale);

        if ($localeService->isSupported($normalizedLocale)) {
            $localeService->apply($normalizedLocale);
            $localeService->persistSelection($request, $normalizedLocale);
        }

        return redirect()->back();
    }
}
