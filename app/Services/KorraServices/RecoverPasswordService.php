<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AangServices\UserServiceInterface as AangUserServiceInterface;
use App\Contracts\Services\KorraServices\RecoverPasswordServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\Mail\RecoverPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class RecoverPasswordService implements RecoverPasswordServiceInterface
{
    public function __construct(
        private readonly AangUserServiceInterface $aangUserService
    ) {}

    public function recover(string $email): array
    {
        $userResponse = $this->aangUserService->list([
            'filter[email]' => $email,
        ]);

        dd($userResponse);

        if ($userResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $userList = $userResponse->json()['message'];

        if (empty($userList)) {
            return [
                'message' => 'User not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        }

        $user = $userList[0];

        try {
            Mail::to($email)->send(new RecoverPassword(
                $user['person']
            ));

            return [
                'message' => 'Email sent successfully',
                'code' => Response::HTTP_OK,
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de recuperación de contraseña: '.$e->getMessage());

            return [
                'message' => $e->getMessage(),
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }

    }
}
