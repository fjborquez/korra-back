<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AzulaServices\InventoryServiceInterface as AzulaInventoryServiceInterface;
use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Support\Arr;
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

        $foodWastePercentage = $this->calculateFoodWaste($inventoryListResponse->json());

        return [
            'message' => [
                'inventory' => $inventoryListResponse->json(),
                'statistics' => [
                    'food_waste_percentage' => $foodWastePercentage,
                ],
            ],
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

    public function consume(int $id): array
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

        $currentStatus = $this->extractActiveProductStatus($inventory);

        if ($currentStatus['id'] === 3) {
            return [
                'message' => 'Be careful!!! It is not possible to consume an expired product',
                'code' => Response::HTTP_CONFLICT,
            ];
        }

        if ($currentStatus['id'] == 4) {
            return [
                'message' => 'The product is already consumed',
                'code' => Response::HTTP_CONFLICT,
            ];
        }

        if ($currentStatus['id'] == 5) {
            return [
                'message' => 'It is not possible to consume a discarded product',
                'code' => Response::HTTP_CONFLICT,
            ];
        }

        $inventory['quantity'] = 0;
        $updateInventoryResponse = $this->updateInventory($id, $inventory);

        if (! empty($updateInventoryResponse)) {
            return $updateInventoryResponse;
        }

        $inventoryPutResponse = $this->azulaInventoryService->consume($id);

        if ($inventoryPutResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => 'Item has been consumed',
            'code' => Response::HTTP_OK,
        ];
    }

    private function extractActiveProductStatus($inventory)
    {
        return Arr::first($inventory['product_status'], function ($productStatus) {
            return $productStatus['pivot']['is_active'];
        });
    }

    private function updateInventory($detailId, $data = [])
    {
        $inventoryUpdateResponse = $this->azulaInventoryService->update($detailId, $data);

        if ($inventoryUpdateResponse->unprocessableEntity()) {
            return [
                'message' => $inventoryUpdateResponse->json('message'),
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ];
        } elseif ($inventoryUpdateResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [];
    }

    private function calculateFoodWaste($inventory = []) {
        $inventoryCount = 0;
        $expiredCount = 0;

        foreach ($inventory as $item) {
            foreach ($item['product_status'] as $status) {
                if ($status['pivot']['is_active']) {
                    if ($status['id'] == 1 || $status['id'] == 2
                        || $status['id'] == 3 || $status['id'] == 6) {
                        $inventoryCount++;

                        if ($status['id'] == 3) {
                            $expiredCount++;
                        }
                    }
                }
            }
        }

        if ($inventoryCount > 0) {
            return ($expiredCount / $inventoryCount) * 100;
        } else {
            return 0;
        }
    }
}
