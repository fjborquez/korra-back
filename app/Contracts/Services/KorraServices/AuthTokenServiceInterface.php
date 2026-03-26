<?php

namespace App\Contracts\Services\KorraServices;

interface AuthTokenServiceInterface
{
    public function create(array $data = []): array;
}
