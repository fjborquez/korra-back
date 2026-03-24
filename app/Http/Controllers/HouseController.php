<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;

class HouseController extends Controller
{
    public function __construct(
        private readonly HouseServiceInterface $houseService
    ) {}

    public function list(int $userId)
    {
        try {
            $response = $this->houseService->list($userId);
            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
