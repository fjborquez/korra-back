<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/user/{id}/houses', [HouseController::class, 'list']);
Route::post('/user/{id}/houses', [HouseController::class, 'create']);
Route::delete('/user/{id}/houses/{houseId}', [HouseController::class, 'delete']);
Route::get('/user/{id}/houses/{houseId}/inventory', [InventoryController::class, 'list']);
Route::put('/user/{id}/houses/{houseId}/inventory/{inventoryId}/discard', [InventoryController::class, 'discard']);
Route::put('/user/{id}/houses/{houseId}/inventory/{inventoryId}/consume', [InventoryController::class, 'consume']);

Route::post('/auth/token', [AuthTokenController::class, 'create']);

Route::get('/city', [CityController::class, 'list']);
