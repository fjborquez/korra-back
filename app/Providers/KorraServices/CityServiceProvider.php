<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\CityServiceInterface;
use App\Services\KorraServices\CityService;
use Illuminate\Support\ServiceProvider;

class CityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(
            CityServiceInterface::class,
            CityService::class
        );
    }
}
