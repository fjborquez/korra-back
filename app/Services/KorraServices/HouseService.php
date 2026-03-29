<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AangServices\HouseServiceInterface as AangHouseServiceInterface;
use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;

class HouseService implements HouseServiceInterface
{
    public function __construct(
        private readonly AangHouseServiceInterface $aangHouseService
    ) {}

    public function list(int $userId): array
    {
        $params = [
            'filter[persons.user.id]' => $userId,
            'include' => 'city,persons.user',
        ];
        $houseListResponse = $this->aangHouseService->list($params);

        if ($houseListResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $houseListResponse->json(),
            'code' => Response::HTTP_OK,
        ];
    }
}
