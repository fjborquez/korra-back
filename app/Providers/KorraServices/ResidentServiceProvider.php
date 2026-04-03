<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\ResidentServiceInterface;
use App\Services\KorraServices\ResidentService;
use Illuminate\Support\ServiceProvider;

class ResidentServiceProvider extends ServiceProvider
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
            ResidentServiceInterface::class,
            ResidentService::class
        );
    }
}
