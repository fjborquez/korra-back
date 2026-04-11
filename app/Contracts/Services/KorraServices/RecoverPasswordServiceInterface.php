<?php

namespace App\Contracts\Services\KorraServices;

interface RecoverPasswordServiceInterface
{
    public function recover(string $email): array;
}
