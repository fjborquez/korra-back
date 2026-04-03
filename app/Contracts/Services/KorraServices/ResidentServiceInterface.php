<?php

namespace App\Contracts\Services\KorraServices;

interface ResidentServiceInterface
{
    public function create(int $userId, int $houseId, array $residentData): array;
}
