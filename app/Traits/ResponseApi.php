<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseApi
{
    /**
     * Core of response
     *
     * @param string $message
     * @param integer $statusCode
     * @param object|array|null $data
     * @param boolean $isSuccess
     * @return JsonResponse
     */
    public function coreResponse(string $message, int $statusCode, $data = null, bool $isSuccess = true): JsonResponse
    {
        // Check the params
        if (!$message) {
            return response()->json(['message' => 'Message is required'], 500);
        }

        // Send the response
        if ($isSuccess) {
            return response()->json([
                'message' => $message,
                'code' => $statusCode,
                'results' => $data
            ], $statusCode);
        }

        $jsonDataError = [
            'message' => $message,
        ];
        if ($data) {
            $jsonDataError['errors'] = $data;

        }
        return response()->json($jsonDataError, $statusCode);
    }

    /**
     * Send any success response
     *
     * @param string $message
     * @param object|array|null $data
     * @param integer $statusCode
     * @return JsonResponse
     */
    public function success(string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        return $this->coreResponse($message, $statusCode, $data);
    }

    /**
     * Send any error response
     *
     * @param string $message
     * @param integer $statusCode
     * @param object|array|null $data
     * @return JsonResponse
     */
    public function error(string $message, int $statusCode = 500, $data = null): JsonResponse
    {
        return $this->coreResponse($message, $statusCode, $data, false);
    }

}
