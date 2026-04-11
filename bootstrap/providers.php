<?php

use App\Providers\AangServices\AuthTokenServiceProvider;
use App\Providers\AangServices\CityServiceProvider;
use App\Providers\AangServices\HouseServiceProvider;
use App\Providers\AangServices\PersonHouseServiceProvider;
use App\Providers\AangServices\PersonServiceProvider;
use App\Providers\AangServices\UserServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AzulaServices\InventoryServiceProvider;
use App\Providers\KorraServices\ConfigurationServiceProvider;
use App\Providers\KorraServices\ResidentServiceProvider;
use App\Providers\TophServices\UnitOfMeasurementServiceProvider;
use App\Providers\ZukoServices\ProductCatalogServiceProvider;
use App\Proviers\KorraServices\RecoverPasswordServiceProvider;

return [
    AppServiceProvider::class,

    HouseServiceProvider::class,
    AuthTokenServiceProvider::class,
    UserServiceProvider::class,
    PersonHouseServiceProvider::class,
    CityServiceProvider::class,
    PersonServiceProvider::class,

    InventoryServiceProvider::class,

    ProductCatalogServiceProvider::class,

    UnitOfMeasurementServiceProvider::class,

    App\Providers\KorraServices\AuthTokenServiceProvider::class,
    App\Providers\KorraServices\HouseServiceProvider::class,
    App\Providers\KorraServices\InventoryServiceProvider::class,
    App\Providers\KorraServices\CityServiceProvider::class,
    App\Providers\KorraServices\ProductCatalogServiceProvider::class,
    ResidentServiceProvider::class,
    App\Providers\KorraServices\UnitOfMeasurementServiceProvider::class,
    ConfigurationServiceProvider::class,
    RecoverPasswordServiceProvider::class,

];
