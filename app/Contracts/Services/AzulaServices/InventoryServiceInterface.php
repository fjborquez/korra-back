<?php

namespace App\Contracts\Services\AzulaServices;

use Illuminate\Http\Client\Response;

interface InventoryServiceInterface
{
    public function list(array $params = []): Response;

    public function discard(int $id): Response;

    public function get(int $id): Response;
}
