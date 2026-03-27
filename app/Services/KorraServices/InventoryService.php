<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AzulaServices\InventoryServiceInterface as AzulaInventoryServiceInterface;
use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;

class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        private readonly AzulaInventoryServiceInterface $azulaInventoryService
    ) {}

    public function list(int $houseId): array
    {
        $params = [
            'filter[house_id]' => $houseId,
            'filter[has_active_product_status]' => true,
            'include' => 'productStatus',
        ];
        $inventoryListResponse = $this->azulaInventoryService->list($params);

        if ($inventoryListResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $inventoryListResponse->json(),
            'code' => Response::HTTP_OK,
        ];
    }
}
