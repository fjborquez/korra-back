<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\ConfigurationServiceInterface;
use App\Services\KorraServices\ConfigurationService;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
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
            ConfigurationServiceInterface::class,
            ConfigurationService::class
        );
    }
}
