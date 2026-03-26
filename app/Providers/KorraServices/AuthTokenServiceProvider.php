<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\AuthTokenServiceInterface;
use App\Services\KorraServices\AuthTokenService;
use Illuminate\Support\ServiceProvider;

class AuthTokenServiceProvider extends ServiceProvider
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
            AuthTokenServiceInterface::class,
            AuthTokenService::class
        );
    }
}
