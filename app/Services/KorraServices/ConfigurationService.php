<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\KorraServices\ConfigurationServiceInterface;
use App\Contracts\Services\AangServices\UserServiceInterface as AangUserServiceInterface;
use App\Contracts\Services\AangServices\PersonServiceInterface as AangPersonServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;


class ConfigurationService implements ConfigurationServiceInterface
{
    public function __construct(
        private readonly AangUserServiceInterface $aangUserService,
        private readonly AangPersonServiceInterface $aangPersonService,
    ) {}

    public function get(int $userId): array
    {
        $userResponse = $this->aangUserService->get($userId);

        if ($userResponse->notFound()) {
            return [
                'message' => 'User not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } else if ($userResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $user = $userResponse->json();

        $personResponse = $this->aangPersonService->get($user['person']['id']);

        if ($userResponse->notFound()) {
            return [
                'message' => 'Person not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } else if ($userResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $person = $personResponse->json();

        return [
            'message' => [
                'name' => $person['name'],
                'lastname' => $person['lastname'],
                'date_of_birth' => $person['date_of_birth'],
            ],
            'code' => Response::HTTP_OK,
        ];

    }

    public function configure(int $userId, array $data = []): array
    {
        $userResponse = $this->aangUserService->get($userId);

        if ($userResponse->notFound()) {
            return [
                'message' => 'User not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } elseif ($userResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $user = $userResponse->json();

        $personResponse = $this->aangPersonService->get($user['person']['id']);

        if ($personResponse->notFound()) {
            return [
                'message' => 'Person not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } elseif ($personResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $person = $personResponse->json();
        $person['name'] = $data['name'];
        $person['lastname'] = $data['lastname'];
        $person['date_of_birth'] = $data['date_of_birth'];

        $updatePersonResponse = $this->aangPersonService->update($person['id'], $person);

        if ($updatePersonResponse->unprocessableEntity()) {
            $message = $updatePersonResponse->json('message');
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($updatePersonResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        if (!empty($data['password'])) {
            $user['password'] = $data['password'];
            $updateUserResponse = $this->aangUserService->update($user['id'], $user);

            if ($updateUserResponse->unprocessableEntity()) {
                $message = $updateUserResponse->json('message');
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;

                return [
                    'message' => $message,
                    'code' => $code,
                ];
            } elseif ($updateUserResponse->failed()) {
                throw new UnexpectedErrorException;
            }
        }

        return [
            'message' => 'Configuration updated successfully',
            'code' => Response::HTTP_OK,
        ];
    }
}
