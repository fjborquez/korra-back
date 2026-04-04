<?php

namespace App\Contracts\Services\KorraServices;

interface ResidentServiceInterface
{
    public function create(int $userId, int $houseId, array $residentData): array;

    public function update(int $userId, int $houseId, int $residentId, array $residentData): array;
}
