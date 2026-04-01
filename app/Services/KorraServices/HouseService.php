<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AangServices\HouseServiceInterface as AangHouseServiceInterface;
use App\Contracts\Services\AangServices\UserServiceInterface as AangUserServiceInterface;
use App\Contracts\Services\AangServices\PersonHouseServiceInterface as AangPersonHouseServiceInterface;
use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;
use App\HouseRole;

class HouseService implements HouseServiceInterface
{
    public function __construct(
        private readonly AangHouseServiceInterface $aangHouseService,
        private readonly AangUserServiceInterface $aangUserService,
        private readonly AangPersonHouseServiceInterface $aangPersonHouseService,
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

    public function create(int $userId, array $data): array
    {
        $message = 'Person added to house';
        $code = Response::HTTP_CREATED;
        $getUserResponse = $this->aangUserService->get($userId);

        if ($getUserResponse->notFound()) {
            $message = 'User not found';
            $code = Response::HTTP_NOT_FOUND;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($getUserResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $user = $getUserResponse->json();
        $createHouseResponse = $this->aangHouseService->create($data);

        if ($createHouseResponse->unprocessableEntity()) {
            $message = $createHouseResponse->json('message');
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($createHouseResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $houseUrl = $createHouseResponse->header('Location');
        $houseUrl = explode('/', $houseUrl);
        $houseId = (int) end($houseUrl);

        $getHouseResponse = $this->aangHouseService->get($houseId);

        if ($getHouseResponse->notFound()) {
            $message = 'House not found';
            $code = Response::HTTP_NOT_FOUND;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($getHouseResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $houses = $user['person']['houses'];
        $housesId = [];

        foreach ($houses as $house) {
            $housesId[$house['id']] = [
                'is_default' => array_key_exists("is_default", $data) ? 0 : $house['pivot']['is_default'],
                'house_role_id' => $house['pivot']['house_role_id'],
            ];
        }

        $housesId[$houseId] = [
            'is_default' => array_key_exists("is_default", $data) && $data['is_default'],
            'house_role_id' => HouseRole::HOST,
        ];

        $userHouseRelationshipResponse = $this->aangPersonHouseService->create($user['person']['id'], ['houses' => $housesId]);

        if ($userHouseRelationshipResponse->unprocessableEntity()) {
            $message = $userHouseRelationshipResponse->json('message');
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($userHouseRelationshipResponse->badRequest()) {
            // TODO: Corregir mensaje
            // TODO: Borrar casa
            $message = 'The person already has a house with description in city';
            $code = Response::HTTP_BAD_REQUEST;
        } elseif ($userHouseRelationshipResponse->notFound()) {
            $message = 'Person not found';
            $code = Response::HTTP_NOT_FOUND;
        } elseif ($userHouseRelationshipResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $message,
            'code' => $code,
        ];
    }
}
