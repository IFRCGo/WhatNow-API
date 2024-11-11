<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class VaporUiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Register the Vapor UI gate.
     *
     * This gate determines who can access Vapor UI in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewVaporUI', function () {
            // TODO: Configure Vapor UI for dev environments.
            return false;
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
