<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
    }


}