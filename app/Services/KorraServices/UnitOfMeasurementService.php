<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\KorraServices\UnitOfMeasurementServiceInterface;
use App\Contracts\Services\TophServices\UnitOfMeasurementServiceInterface as TophUnitOfMeasurementServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;

class UnitOfMeasurementService implements UnitOfMeasurementServiceInterface
{
    public function __construct(
        private readonly TophUnitOfMeasurementServiceInterface $tophUnitOfMeasurementService
    ) {}

    public function list(array $filter = []): array
    {
        $clonedFilter = array_merge([
            'filter' => [
                'category.name' => 'Mass,Volume,Count',
            ],
        ], $filter);
        $unitOfMeasurementListResponse = $this->tophUnitOfMeasurementService->list($clonedFilter);

        if ($unitOfMeasurementListResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $unitOfMeasurementListResponse->json(),
            'code' => Response::HTTP_OK,
        ];
    }
}
