<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Classes\Repositories\AlertRepositoryInterface',
            'App\Classes\Repositories\AlertRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\ApplicationRepositoryInterface',
            'App\Classes\Repositories\ApplicationRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\OrganisationRepositoryInterface',
            'App\Classes\Repositories\OrganisationRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\RegionRepositoryInterface',
            'App\Classes\Repositories\RegionRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\UsageLogRepositoryInterface',
            'App\Classes\Repositories\UsageLogRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\WhatNowRepositoryInterface',
            'App\Classes\Repositories\WhatNowRepository'
        );

        $this->app->bind(
            'App\Classes\Repositories\WhatNowTranslationRepositoryInterface',
            'App\Classes\Repositories\WhatNowTranslationRepository'
        );
    }
}
