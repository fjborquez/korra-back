<?php

namespace App\Contracts\Services\KorraServices;

interface InventoryServiceInterface
{
    public function list(int $houseId): array;

    public function discard(int $id): array;
}
