<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseTrait
{
    /**
     * Generate success response.
     */
    public function success(mixed $data = null, string $message = 'Success', int $status_code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status_code);
    }

    /**
     * Generate error response.
     */
    public function error(mixed $errors = null, mixed $data = null, string $message = 'Error', int $status_code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $status_code);
    }
}
