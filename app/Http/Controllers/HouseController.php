<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\HouseServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\UserHouseRequest;


class HouseController extends Controller
{
    private $fields = ['description', 'city_id', 'is_default', 'house_id'];

    public function __construct(
        private readonly HouseServiceInterface $houseService
    ) {}

    public function list(int $userId)
    {
        try {
            $response = $this->houseService->list($userId);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(int $userId, UserHouseRequest $request)
    {
        $validated = $request->safe()->only($this->fields);

        try {
            $response = $this->houseService->create($userId, $validated);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
