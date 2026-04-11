<?php

namespace App\Providers\KorraServices;

use App\Contracts\Services\KorraServices\RecoverPasswordServiceInterface;
use App\Services\KorraServices\RecoverPasswordService;
use Illuminate\Support\ServiceProvider;

class RecoverPasswordServiceProvider extends ServiceProvider
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
            RecoverPasswordServiceInterface::class,
            RecoverPasswordService::class
        );
    }
}
