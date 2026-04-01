<?php

namespace App\Contracts\Services\AangServices;

use Illuminate\Http\Client\Response;

interface UserServiceInterface
{
    public function get(int $id): Response;
}
