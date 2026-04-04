<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Http\Response;
use App\Services\KorraServices\ProductCatalogService;

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
