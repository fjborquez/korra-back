<?php

namespace App\Contracts\Services\KorraServices;

interface InventoryServiceInterface
{
    public function create(int $userId, int $houseId, array $data = []): array;

    public function list(int $houseId): array;

    public function discard(int $id): array;

    public function consume(int $id): array;
}
