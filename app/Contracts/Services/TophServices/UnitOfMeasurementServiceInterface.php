<?php

namespace App\Contracts\Services\TophServices;

use Illuminate\Http\Client\Response;

interface UnitOfMeasurementServiceInterface
{
    public function list(array $params = []): Response;
}
