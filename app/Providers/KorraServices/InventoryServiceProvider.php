<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Services\KorraServices\InventoryService;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
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
            InventoryServiceInterface::class,
            InventoryService::class
        );
    }
}
