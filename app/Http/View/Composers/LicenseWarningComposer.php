<?php

namespace App\Http\View\Composers;

use App\Services\LicenseService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class LicenseWarningComposer
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        try {
            $stats = $this->licenseService->getUsageStats();

            $licenseWarning = null;
            $licenseWarningType = 'warning';

            if ($stats['has_license'] && $stats['is_exceeded']) {
                $licenseWarning = "User limit exceeded! You have {$stats['active_users']} active users but your license only allows {$stats['max_users']}.";
                $licenseWarningType = 'warning';
            } elseif ($stats['has_license'] && $stats['is_warning']) {
                $remaining = $stats['remaining_users'];
                $licenseWarning = "You are approaching your user limit. Only {$remaining} user slot(s) remaining out of {$stats['max_users']}.";
                $licenseWarningType = 'warning';
            }

            $view->with([
                'licenseWarning' => $licenseWarning,
                'licenseWarningType' => $licenseWarningType,
                'licenseStats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('LicenseWarningComposer error: ' . $e->getMessage());
            $view->with([
                'licenseWarning' => null,
                'licenseWarningType' => 'warning',
                'licenseStats' => [],
            ]);
        }
    }
}