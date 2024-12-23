<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Utility class for generating JSON responses.
 */
class ResponseUtils
{
    /**
     * Generates a success response.
     *
     * @param array<string, mixed> $data The data to include in the response.
     * @param int $status The HTTP status code for the response. Defaults to 200.
     *
     * @return JsonResponse
     */
    public static function success(array $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse(data: $data, status: $status);
    }

    /**
     * Generates an error response.
     *
     * @param string $message The error message to include in the response.
     * @param int $status The HTTP status code for the response. Defaults to 500.
     *
     * @return JsonResponse
     */
    public static function error(string $message, int $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse(
            data: ['error' => $message],
            status: $status
        );
    }
}
