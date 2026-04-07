<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;

/**
 * Class LocaleMiddleware.
 */
class LocaleMiddleware
{
    protected $localeService;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }



    public function handle($request, Closure $next)
    {
        if (!config('locale.status')) {
            return $next($request);
        }

        $locale = $this->localeService->resolveFromRequest($request);
        $this->localeService->apply($locale);
        $this->localeService->persistSelection($request, $locale);

        return $next($request);
    }
}
