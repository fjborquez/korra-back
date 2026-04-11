<?php

namespace App\Contracts\Services\AangServices;

use Illuminate\Http\Client\Response;

interface UserServiceInterface
{
    public function list(array $params = []): Response;

    public function get(int $id): Response;

    public function update(int $id, array $data = []): Response;
}
