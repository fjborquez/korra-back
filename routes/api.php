<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UnitOfMeasurementController;
use Illuminate\Support\Facades\Route;

Route::get('/user/{id}/houses', [HouseController::class, 'list']);
Route::post('/user/{id}/houses', [HouseController::class, 'create']);
Route::put('/user/{id}/houses/{houseId}', [HouseController::class, 'update']);
Route::delete('/user/{id}/houses/{houseId}', [HouseController::class, 'delete']);
Route::post('/user/{id}/houses/{houseId}/residents', [ResidentController::class, 'create']);
Route::put('/user/{id}/houses/{houseId}/residents/{residentId}', [ResidentController::class, 'update']);
Route::get('/user/{id}/houses/{houseId}/inventory', [InventoryController::class, 'list']);
Route::put('/user/{id}/houses/{houseId}/inventory/{inventoryId}/discard', [InventoryController::class, 'discard']);
Route::put('/user/{id}/houses/{houseId}/inventory/{inventoryId}/consume', [InventoryController::class, 'consume']);

Route::get('/product-catalog', [ProductCatalogController::class, 'list']);

Route::get('/unit-of-measurement', [UnitOfMeasurementController::class, 'list']);

Route::post('/auth/token', [AuthTokenController::class, 'create']);

Route::get('/city', [CityController::class, 'list']);
