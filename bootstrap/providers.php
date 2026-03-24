<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    App\Providers\AangServices\HouseServiceProvider::class,

    App\Providers\AzulaServices\InventoryServiceProvider::class,

    App\Providers\KorraServices\HouseServiceProvider::class,
    App\Providers\KorraServices\InventoryServiceProvider::class,

];
