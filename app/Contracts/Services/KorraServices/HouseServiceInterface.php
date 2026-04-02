<?php

namespace App\Contracts\Services\KorraServices;

interface HouseServiceInterface
{
    public function list(int $userId): array;

    public function create(int $userId, array $data): array;

    public function update(int $userId, int $houseId, array $data): array;

    public function delete(int $userId, int $houseId): array;
}
