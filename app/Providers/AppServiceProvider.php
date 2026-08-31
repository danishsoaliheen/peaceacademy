<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // In app/Providers/AppServiceProvider.php  →  boot()
        require_once app_path('Helpers/PaymentMethodHelper.php');

        // Laravel 12 defaults pagination links to Tailwind markup, but this
        // app is built on Bootstrap 5 (no Tailwind CSS loaded), which is why
        // the Next/Previous buttons were rendering unstyled / misaligned.
        // This switches every paginated view in the app to the Bootstrap 5
        // pagination view (already published at
        // resources/views/vendor/pagination/bootstrap-5.blade.php).
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | RBAC — wire Blade's @can() / Gate::allows() to our own permission
        | system (App\Models\User::hasPermission()).
        |--------------------------------------------------------------------------
        |
        | The sidebar (resources/views/layouts/dashboard.blade.php) and many
        | other views use @can('students.view'), @can('fee-vouchers.edit'),
        | etc. throughout the app. Laravel's @can directive is powered by the
        | Gate facade, but this app never registered any of those ability
        | names with the Gate (no Gate::define(), no Policies). Without a
        | matching Gate definition, Laravel denies every @can() check by
        | default — which is why admins (and everyone else) were seeing an
        | almost-empty sidebar despite the RolePermission/User logic already
        | being correct.
        |
        | Gate::before runs before any other check for EVERY ability. If it
        | returns true/false, that decision wins immediately; returning null
        | falls through to normal Gate resolution (there isn't any here, so
        | it would just deny — but we always return a boolean below).
        |
        | This single hook makes every permission string used anywhere in
        | the app (sidebar, controllers' authorize(), @can in Blade, etc.)
        | resolve via the existing role_permissions table, with admins
        | automatically passing every check (per User::isAdmin()).
        |--------------------------------------------------------------------------
        */
        Gate::before(function ($user, string $ability) {
            if (!$user) {
                return false;
            }

            return $user->hasPermission($ability);
        });
    }
}