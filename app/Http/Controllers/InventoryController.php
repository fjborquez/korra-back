<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryServiceInterface $inventoryService
    ) {}

    public function list(int $userId, int $houseId)
    {
        try {
            $response = $this->inventoryService->list($houseId);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function discard(int $userId, int $houseId, int $inventoryId)
    {
        try {
            $response = $this->inventoryService->discard($inventoryId);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function consume(int $userId, int $houseId, int $inventoryId)
    {
        try {
            $response = $this->inventoryService->consume($inventoryId);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
