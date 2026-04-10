<?php

namespace App\Contracts\Services\KorraServices;

interface ConfigurationServiceInterface
{
    public function get(int $userId): array;

    public function configure(int $userId): array;
}
