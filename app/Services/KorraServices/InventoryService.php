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
            'include' => 'productStatus'
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

    public function discard(int $id): array
    {
        $inventoryGetResponse = $this->azulaInventoryService->get($id);

        if ($inventoryGetResponse->notFound()) {
            $message = 'Inventory item not found';
            $code = Response::HTTP_NOT_FOUND;

            return [
                'message' => $message,
                'code' => $code,
            ];
        } elseif ($inventoryGetResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        $inventory = $inventoryGetResponse->json();
        $inventoryPutResponse = $this->azulaInventoryService->discard($id);

        if ($inventoryPutResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => 'Item: '.$inventory['quantity'].' '.$inventory['uom_abbreviation'].' '.$inventory['catalog_description'].' has been discarded',
            'code' => Response::HTTP_OK,
        ];
    }
}
