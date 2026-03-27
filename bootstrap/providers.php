<?php

use App\Providers\AangServices\AuthTokenServiceProvider;
use App\Providers\AangServices\HouseServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AzulaServices\InventoryServiceProvider;

return [
    AppServiceProvider::class,

    HouseServiceProvider::class,
    AuthTokenServiceProvider::class,

    InventoryServiceProvider::class,

    App\Providers\KorraServices\AuthTokenServiceProvider::class,
    App\Providers\KorraServices\HouseServiceProvider::class,
    App\Providers\KorraServices\InventoryServiceProvider::class,

];
