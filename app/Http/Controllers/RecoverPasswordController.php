<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\RecoverPasswordServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecoverPasswordController
{
    public function __construct(
        private readonly RecoverPasswordServiceInterface $recoverPasswordService
    ) {}

    public function recover(Request $request)
    {
        try {
            $response = $this->recoverPasswordService->recover($request->input('email'));

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reset(Request $request)
    {
        try {
            $response = $this->recoverPasswordService->reset($request->all());

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
