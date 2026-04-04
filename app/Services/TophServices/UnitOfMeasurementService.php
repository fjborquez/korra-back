<?php

namespace App\Services\TophServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use App\Contracts\Services\TophServices\UnitOfMeasurementServiceInterface;

class UnitOfMeasurementService implements UnitOfMeasurementServiceInterface
{
    public function list(array $params = []): Response
    {
        return Http::accept('application/json')->retry(3, 100, null, false)->get(Config::get('toph.url').'/unit-of-measurement', $params);
    }
}
