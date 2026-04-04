<?php

namespace App\Contracts\Services\KorraServices;

interface UnitOfMeasurementServiceInterface
{
    public function list(array $filter = []): array;
}
