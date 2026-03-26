<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/user/{id}/houses', [HouseController::class, 'list']);
Route::get('/user/{id}/houses/{houseId}/inventory', [InventoryController::class, 'list']);

Route::post('/auth/token', [AuthTokenController::class, 'create']);
