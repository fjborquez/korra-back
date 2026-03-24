<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Services\KorraServices\HouseService;
use Illuminate\Support\ServiceProvider;

class HouseServiceProvider extends ServiceProvider
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
            HouseServiceInterface::class,
            HouseService::class
        );
    }
}
