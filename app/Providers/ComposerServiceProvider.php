<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Http\Composers\GlobalComposer;
use Illuminate\Support\ServiceProvider;
use App\Http\Composers\Backend\SidebarComposer;
use App\Http\View\Composers\LicenseWarningComposer;

/**
 * Class ComposerServiceProvider.
 */
class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Global
         */
        View::composer(
        // This class binds the $logged_in_user variable to every view
            '*',
            GlobalComposer::class
        );

        /*
         * Frontend
         */

        /*
         * Backend
         */
        View::composer(
        // This binds items like number of users pending approval when account approval is set to true
            'backend.includes.sidebar',
            SidebarComposer::class
        );

        /*
         * License warning on user listing and creation views
         */
        View::composer(
            [
                'backend.employee.index',
                'backend.employee.external_index',
                'backend.teachers.index',
                'backend.assessment_accounts.index',
                'backend.auth.user.index',
            ],
            LicenseWarningComposer::class
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}