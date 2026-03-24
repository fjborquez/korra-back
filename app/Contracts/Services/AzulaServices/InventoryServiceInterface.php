<?php

namespace App\Contracts\Services\AzulaServices;

use Illuminate\Http\Client\Response;

interface InventoryServiceInterface
{
    public function list(): Response;
}
