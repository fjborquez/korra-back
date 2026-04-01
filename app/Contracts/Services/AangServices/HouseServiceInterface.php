<?php

namespace App\Contracts\Services\AangServices;

use Illuminate\Http\Client\Response;

interface HouseServiceInterface
{
    public function list(): Response;

    public function create(array $data = []): Response;

    public function get(int $houseId): Response;

    public function disable(int $houseId): Response;
}
