<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\AzulaServices\InventoryServiceInterface as AzulaInventoryServiceInterface;
use App\Contracts\Services\KorraServices\InventoryServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\Services\AangServices\HouseServiceInterface as AangHouseService;
use App\Contracts\Services\TophServices\UnitOfMeasurementServiceInterface as TophUnitOfMeasurementService;

class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        private readonly AzulaInventoryServiceInterface $azulaInventoryService,
        private readonly AangHouseService $aangHouseService,
        private readonly TophUnitOfMeasurementService $tophUnitOfMeasurementService,
    ) {}

    public function create(int $userId, int $houseId, array $data = []): array
    {
        $house = $this->searchActiveHouse($houseId);

        if ($this->isError($house)) {
            return $house;
        }

        $inventory = $this->searchInventoryByParams([
            'house_id' => $houseId,
        ]);
        $newDetailData = $data;
        $newDetailData['house_description'] = $house['description'];
        $newDetailData['expiration_date'] = $this->getExpirationDateOrNull($newDetailData);
        $newDetailData['house_id'] = $houseId;

        if (empty($inventory)) {
            $createdInventory = $this->createInventoryDetail($newDetailData);

            if ($this->isError($createdInventory)) {
                return $createdInventory;
            }
        } else {
            // actualizar inventario existente
            // Obtener las diferentes coincidencias del item en el inventario
            $existingDetailsByCatalog = $this->searchItem($inventory, $newDetailData);

            if (empty($existingDetailsByCatalog)) {
                // Si el detalle de inventario no estaba de antes
                $createdInventory = $this->createInventoryDetail($newDetailData);

                if ($this->isError($createdInventory)) {
                    return $createdInventory;
                }
            } else {
                // El detalle de inventario ya estaba de antes
                $existingDetailByUomAndExpirationDate = $this->searchItemDetails($existingDetailsByCatalog, $newDetailData);

                if ($existingDetailByUomAndExpirationDate) {
                    // Si tienen la misma UOM y la misma fecha de expiración: sumar y actualizar
                    $existingDetailByUomAndExpirationDate['quantity'] += $newDetailData['quantity'];
                    $updatedInventory = $this->updateInventory($existingDetailByUomAndExpirationDate['id'], $existingDetailByUomAndExpirationDate);

                    if ($this->isError($updatedInventory)) {
                        return $updatedInventory;
                    }
                } else {
                    $existingDetailByUom = $this->searchItemDetailsByProperty($existingDetailsByCatalog, $newDetailData, 'uom_id');
                    $existingDetailByExpirationDate = $this->searchItemDetailsByProperty($existingDetailsByCatalog, $newDetailData, 'expiration_date');

                    if ($existingDetailByUom) {
                        // Si tienen misma UOM pero distinta fecha de expiración: crear nuevo detalle
                        $createdInventory = $this->createInventoryDetail($newDetailData);

                        if ($this->isError($createdInventory)) {
                            return $createdInventory;
                        }
                    } elseif ($existingDetailByExpirationDate) {
                        // Si tienen distinta UOM pero misma fecha de expiración: convertir UOM y sumar
                        $newFromConversion = $this->searchFromUom($newDetailData['uom_id'], $existingDetailByExpirationDate['uom_id']);
                        $oldFromConversion = $this->searchFromUom($existingDetailByExpirationDate['uom_id'], $newDetailData['uom_id']);

                        if ($newFromConversion != null && $this->isError($newFromConversion)) {
                            return $newFromConversion;
                        }

                        if ($oldFromConversion != null && $this->isError($oldFromConversion)) {
                            return $oldFromConversion;
                        }

                        if ($newFromConversion == null || $oldFromConversion == null) {
                            $createdInventory = $this->createInventoryDetail($newDetailData);

                            if ($this->isError($createdInventory)) {
                                return $createdInventory;
                            } else {
                                return [
                                    'message' => 'Inventory created successfully',
                                    'code' => Response::HTTP_CREATED,
                                ];
                            }
                        }

                        if ($newFromConversion['factor'] >= $oldFromConversion['factor']) {
                            $quantityWithUom = $this->calculateQuantity($existingDetailByExpirationDate, $newDetailData, $oldFromConversion);
                        } else {
                            $quantityWithUom = $this->calculateQuantity($newDetailData, $existingDetailByExpirationDate, $newFromConversion);
                        }

                        $newDetailData['quantity'] = $quantityWithUom['quantity'];
                        $newDetailData['uom_abbreviation'] = $quantityWithUom['uom']['abbreviation'];
                        $newDetailData['uom_id'] = $quantityWithUom['uom']['id'];
                        $updatedInventory = $this->updateInventory($existingDetailByExpirationDate['id'], $newDetailData);

                        if ($this->isError($updatedInventory)) {
                            return $updatedInventory;
                        } else {
                            return [
                                'message' => 'Inventory created successfully',
                                'code' => Response::HTTP_CREATED,
                            ];
                        }
                    } else {
                        // Si tienen distinta UOM y distinta fecha de expiración: crear nuevo detalle
                        $createdInventory = $this->createInventoryDetail($newDetailData);

                        if ($this->isError($createdInventory)) {
                            return $createdInventory;
                        }
                    }
                }
            }
        }

        return [
            'message' => 'Inventory created successfully',
            'code' => Response::HTTP_CREATED,
        ];
    }

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

    private function calculateFoodWaste($inventory = [])
    {
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

    private function searchActiveHouse($houseId)
    {
        $house = $this->searchHouseById($houseId);

        if ($this->isError($house)) {
            return $house;
        }

        if (! $house['is_active']) {
            return [
                'message' => 'House is not active',
                'code' => Response::HTTP_CONFLICT,
            ];
        }

        return $house;
    }

    private function searchHouseById($houseId)
    {
        $houseGetResponse = $this->aangHouseService->get($houseId);

        if ($houseGetResponse->notFound()) {
            return [
                'message' => 'House not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } elseif ($houseGetResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return $houseGetResponse->json();
    }

    private function isError(array $response)
    {
        return array_key_exists('message', $response) && array_key_exists('code', $response);
    }

    private function searchInventoryByParams($params = [])
    {
        $filterParams = [
            'filter[has_active_product_status]' => true,
        ];

        if (array_key_exists('house_id', $params)) {
            $filterParams['filter[house_id]'] = $params['house_id'];
        }

        $inventoryGetResponse = $this->azulaInventoryService->list($filterParams);

        if ($inventoryGetResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return $inventoryGetResponse->json();
    }

    private function getExpirationDateOrNull($detail)
    {
        return array_key_exists('expiration_date', $detail) ? $detail['expiration_date'] : null;
    }

    private function createInventoryDetail(array $detail)
    {
        $inventoryCreateResponse = $this->azulaInventoryService->create($detail);

        if ($inventoryCreateResponse->unprocessableEntity()) {
            return [
                'message' => $inventoryCreateResponse->json('message'),
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ];
        } elseif ($inventoryCreateResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [];
    }

    private function searchItem($inventory, $item)
    {
        return array_filter($inventory, function ($inventoryDetail) use ($item) {
            return $inventoryDetail['catalog_id'] === (int) $item['catalog_id'];
        });
    }

    private function searchItemDetails($items, $item)
    {
        return Arr::first($items, function ($detail) use ($item) {
            return $detail['uom_id'] === (int) $item['uom_id']
                && $detail['expiration_date'] === $item['expiration_date'];
        });
    }

    private function searchItemDetailsByProperty($items, $item, $property)
    {
        return Arr::first($items, function ($detail) use ($item, $property) {
            return $detail[$property] === $item[$property];
        });
    }

    private function searchFromUom($originalUom, $uomToBeSearch)
    {
        $newUom = $this->getUom($originalUom);

        if ($this->isError($newUom)) {
            return $newUom;
        }

        return $this->getFromUomById($newUom['from_conversions'], $uomToBeSearch);
    }

    private function getUom($uomId)
    {
        $uomGetResponse = $this->tophUnitOfMeasurementService->get($uomId);

        if ($uomGetResponse->notFound()) {
            return [
                'message' => 'Unit of measurement not found',
                'code' => Response::HTTP_NOT_FOUND,
            ];
        } elseif ($uomGetResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return $uomGetResponse->json();
    }

    private function getFromUomById($uoms, $uomId)
    {
        return Arr::first($uoms, function ($fromUom) use ($uomId) {
            return $fromUom['to_unit_id'] === $uomId;
        });
    }

    private function calculateQuantity($baseItemDetail, $toApplyItemDetail, $uom)
    {
        $itemQuantity = [];

        $itemQuantity['quantity'] = ($baseItemDetail['quantity'] * $uom['factor']) + $toApplyItemDetail['quantity'];
        $itemQuantity['uom']['abbreviation'] = $toApplyItemDetail['uom_abbreviation'];
        $itemQuantity['uom']['id'] = $toApplyItemDetail['uom_id'];

        return $itemQuantity;
    }
}
