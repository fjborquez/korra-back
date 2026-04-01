<?php

namespace App\Contracts\Services\KorraServices;

interface HouseServiceInterface
{
    public function list(int $userId): array;

    public function create(int $userId, array $data): array;
}
