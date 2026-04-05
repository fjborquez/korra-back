<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\Http\Requests\InventoryRequest;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    private $fields = ['quantity', 'uom_id', 'uom_abbreviation', 'purchase_date', 'expiration_date',
        'catalog_id', 'catalog_description', 'brand_id', 'brand_name', 'category_id', 'category_name', 'product_status'];

    public function __construct(
        private readonly InventoryServiceInterface $inventoryService
    ) {}

    public function store(int $userId, int $houseId, InventoryRequest $request)
    {
        $validated = $request->safe()->only($this->fields);

        try {
            $response = $this->inventoryService->create($userId, $houseId, $validated);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $userId, int $houseId, int $inventoryId, InventoryRequest $request)
    {
        $validated = $request->safe()->only($this->fields);

        try {
            $response = $this->inventoryService->update($userId, $houseId, $inventoryId, $validated);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            report($exception);

            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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
