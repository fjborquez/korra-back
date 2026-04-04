<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\ProductCatalogServiceInterface;
use App\Services\KorraServices\ProductCatalogService;
use Illuminate\Support\ServiceProvider;

class ProductCatalogServiceProvider extends ServiceProvider
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
            ProductCatalogServiceInterface::class,
            ProductCatalogService::class
        );
    }
}
