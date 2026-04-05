<?php

namespace App\Http\Controllers;

use App\Exceptions\UnexpectedErrorException;
use App\Services\KorraServices\ProductCatalogService;
use Illuminate\Http\Response;

class ProductCatalogController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $productCatalogService
    ) {}

    public function list()
    {
        try {
            $response = $this->productCatalogService->list();

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            report($exception);

            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
