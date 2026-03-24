<?php

namespace App\Contracts\Services\KorraServices;

interface InventoryServiceInterface
{
    public function list(int $houseId): array;
}
