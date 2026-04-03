<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AangServices\PersonServiceInterface as AangPersonServiceInterface;
use App\Contracts\Services\AangServices\PersonHouseServiceInterface as AangPersonHouseServiceInterface;
use App\Contracts\Services\KorraServices\ResidentServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\HouseRole;
use Symfony\Component\HttpFoundation\Response;

class ResidentService implements ResidentServiceInterface
{
    public function __construct(
        private readonly AangPersonServiceInterface $aangPersonService,
        private readonly AangPersonHouseServiceInterface $aangPersonHouseService
    ) {}

    public function create(int $userId, int $houseId, array $residentData): array
    {
        $createPersonResponse = $this->aangPersonService->create($residentData);

        if ($createPersonResponse->unprocessableEntity()) {
            $message = $createPersonResponse->json('message');
            $code = $createPersonResponse->status();

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($createPersonResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $personUrlParts = explode('/', $createPersonResponse->header('Location'));
        $personId = (int) end($personUrlParts);

        $createPersonHouseResponse = $this->aangPersonHouseService->create($personId, ['houses' => [
            $houseId => [
                'is_default' => false,
                'house_role_id' => HouseRole::RESIDENT,
            ],
        ]]);

        if ($createPersonHouseResponse->notFound()) {
            $message = 'House not found';
            $code = Response::HTTP_NOT_FOUND;
            $this->aangPersonService->delete($personId);

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($createPersonHouseResponse->failed()) {
            $this->aangPersonService->delete($personId);
            throw new UnexpectedErrorException;
        }

        return [
            'message' => 'Resident created successfully',
            'code' => Response::HTTP_CREATED,
        ];
    }
}
