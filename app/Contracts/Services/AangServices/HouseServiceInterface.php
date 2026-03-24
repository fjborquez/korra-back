<?php

namespace App\Contracts\Services\AangServices;

use Illuminate\Http\Client\Response;

interface HouseServiceInterface
{
    public function list(): Response;
}
