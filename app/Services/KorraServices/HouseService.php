<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AangServices\HouseServiceInterface as AangHouseServiceInterface;
use App\Contracts\Services\AangServices\PersonHouseServiceInterface as AangPersonHouseServiceInterface;
use App\Contracts\Services\AangServices\UserServiceInterface as AangUserServiceInterface;
use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\HouseRole;
use Symfony\Component\HttpFoundation\Response;

class HouseService implements HouseServiceInterface
{
    public function __construct(
        private readonly AangHouseServiceInterface $aangHouseService,
        private readonly AangUserServiceInterface $aangUserService,
        private readonly AangPersonHouseServiceInterface $aangPersonHouseService,
    ) {}

    public function get(int $userId, int $houseId): array
    {
        // TODO: Validar que el usuario tenga acceso a la casa
        $houseGetResponse = $this->aangHouseService->get($houseId);

        if ($houseGetResponse->notFound()) {
            $message = 'House not found';
            $code = Response::HTTP_NOT_FOUND;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($houseGetResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $house = $houseGetResponse->json();

        return [
            'message' => $house,
            'code' => Response::HTTP_OK,
        ];

    }

    public function list(int $userId): array
    {
        $params = [
            'filter[persons.user.id]' => $userId,
            'filter[is_active]' => 1,
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
                'is_default' => array_key_exists('is_default', $data) ? 0 : $house['pivot']['is_default'],
                'house_role_id' => $house['pivot']['house_role_id'],
            ];
        }

        $housesId[$houseId] = [
            'is_default' => array_key_exists('is_default', $data) && $data['is_default'],
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

    public function update(int $userId, int $houseId, array $data): array
    {
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
        $houses = $user['person']['houses'];
        $housesId = [];
        $oldHouse = [];

        foreach ($houses as $house) {
            $housesId[$house['id']] = [
                'is_default' => array_key_exists('is_default', $data) ? 0 : $house['pivot']['is_default'],
                'house_role_id' => $house['pivot']['house_role_id'],
            ];

            if ($house['id'] == $houseId) {
                $oldHouse = $house;
                $housesId[$house['id']] = [
                    'is_default' => $data['is_default'],
                    'house_role_id' => $house['pivot']['house_role_id'],
                ];
            }
        }

        $updateHouseResponse = $this->aangHouseService->update($houseId, $data);

        if ($updateHouseResponse->unprocessableEntity()) {
            $message = $updateHouseResponse->json('message');
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
            $this->aangHouseService->update($houseId, $oldHouse);

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($updateHouseResponse->notFound()) {
            $message = 'House not found';
            $code = Response::HTTP_NOT_FOUND;
            $this->aangHouseService->update($houseId, $oldHouse);

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($updateHouseResponse->failed()) {
            $this->aangHouseService->update($houseId, $oldHouse);
            throw new UnexpectedErrorException;
        }

        $personHouseUpdateResponse = $this->aangPersonHouseService->update($user['person']['id'], ['houses' => $housesId]);

        if ($personHouseUpdateResponse->unprocessableEntity()) {
            $message = $personHouseUpdateResponse->json('message');
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($personHouseUpdateResponse->notFound()) {
            $message = 'Person not found';
            $code = Response::HTTP_NOT_FOUND;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($personHouseUpdateResponse->badRequest()) {
            // TODO: COrregir mensaje
            $message = 'The person already has a house with description in city';
            $code = Response::HTTP_BAD_REQUEST;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($personHouseUpdateResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => 'House updated successfully',
            'code' => Response::HTTP_OK,
        ];
    }

    public function delete(int $userId, int $houseId): array
    {
        $response = $this->aangHouseService->disable($houseId);
        $message = '';
        $code = 0;

        if ($response->noContent()) {
            $message = 'House disabled successfully';
            $code = Response::HTTP_OK;
        } elseif ($response->notFound()) {
            $message = 'House not found';
            $code = Response::HTTP_NOT_FOUND;
        } elseif ($response->badRequest()) {
            $message = 'House is already disabled';
            $code = Response::HTTP_BAD_REQUEST;
        } else {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $message,
            'code' => $code,
        ];
    }
}
