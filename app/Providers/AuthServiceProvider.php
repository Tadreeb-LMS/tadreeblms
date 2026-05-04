<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerGates();
    }

    /**
     * Register all gate definitions for permission checks.
     *
     * @return void
     */
    protected function registerGates()
    {
        // KPI Module Gates
        $kpiPermissions = [
            'kpi_access',
            'kpi_create',
            'kpi_edit',
            'kpi_view',
            'kpi_delete',
            'kpi_role_config_access',
            'kpi_role_config_create',
            'kpi_role_config_edit',
            'kpi_role_config_view',
            'kpi_role_config_delete',
            'kpi_target_access',
            'kpi_target_create',
            'kpi_target_edit',
            'kpi_target_view',
            'kpi_target_delete',
            'kpi_template_access',
            'kpi_template_create',
            'kpi_template_edit',
            'kpi_template_view',
            'kpi_template_delete',
        ];

        foreach ($kpiPermissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->checkPermissionTo($permission);
            });
        }
    }
}
