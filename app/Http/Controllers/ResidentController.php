<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\ResidentServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\Http\Requests\ResidentRequest;
use Symfony\Component\HttpFoundation\Response;

class ResidentController extends Controller
{
    public function __construct(
        private readonly ResidentServiceInterface $residentService
    ) {}

    public function create(int $userId, int $houseId, ResidentRequest $data)
    {
        try {
            $response = $this->residentService->create($userId, $houseId, $data->all());

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
