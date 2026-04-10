<?php

namespace App\Http\Controllers;

use App\Contracts\Services\KorraServices\ConfigurationServiceInterface;
use App\Exceptions\UnexpectedErrorException;
use App\Http\Requests\ConfigurationRequest;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends Controller
{
    private $fields = ['name', 'lastname', 'date_of_birth', 'password'];

    public function __construct(
        private readonly ConfigurationServiceInterface $configurationService
    ) {}

    public function get(int $userId) {
        try {
            $response = $this->configurationService->get($userId);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $userId, ConfigurationRequest $request)
    {
        $validated = $request->safe()->only($this->fields);

        try {
            $response = $this->configurationService->configure($userId, $validated);

            return response()->json(['message' => $response['message']], $response['code']);
        } catch (UnexpectedErrorException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
