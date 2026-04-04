<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\UnitOfMeasurementServiceInterface;
use App\Services\KorraServices\UnitOfMeasurementService;
use Illuminate\Support\ServiceProvider;

class UnitOfMeasurementServiceProvider extends ServiceProvider
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
            UnitOfMeasurementServiceInterface::class,
            UnitOfMeasurementService::class
        );
    }
}
