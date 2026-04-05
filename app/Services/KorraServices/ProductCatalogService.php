<?php

namespace App\Services\KorraServices;

use App\Contracts\Services\KorraServices\ProductCatalogServiceInterface;
use App\Contracts\Services\ZukoServices\ProductCatalogServiceInterface as ZukoProductCatalogServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Http\Response;

class ProductCatalogService implements ProductCatalogServiceInterface
{
    public function __construct(
        private readonly ZukoProductCatalogServiceInterface $zukoProductCatalogService,
    ) {}

    public function list(): array
    {
        $productCatalogListResponse = $this->zukoProductCatalogService->list();

        if ($productCatalogListResponse->failed()) {
            throw new UnexpectedErrorException;
        }

        return [
            'message' => $productCatalogListResponse->json(),
            'code' => Response::HTTP_OK,
        ];
    }
}
