<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // When we ask for a serializer, return the data array serializer
        $this->app->bind(
            'League\Fractal\Serializer\SerializerAbstract',
            \League\Fractal\Serializer\DataArraySerializer::class
        );

        // When we ask for Fractal, return an instance with the above serializer.
        $this->app->bind('League\Fractal\Manager', function ($app) {
            $manager = new \League\Fractal\Manager();
            $manager->setSerializer($app['League\Fractal\Serializer\SerializerAbstract']);

            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            if (! isset($options['path'])) {
                $options['path'] = '/' . request()->path();
            }

            return new LengthAwarePaginator(array_values($this->forPage($page, $perPage)->toArray()), $this->count(), $perPage, $page, $options);
        });
    }
}
