<?php

namespace App\Traits;

trait ResponseApi
{
    /**
     * Core of response
     *
     * @param string $message
     * @param integer $statusCode
     * @param object|array|null $data
     * @param boolean $isSuccess
     * @return array
     */
    public function coreResponse(string $message, int $statusCode, $data = null, bool $isSuccess = true): array
    {
        $template = [
            'success' => $isSuccess,
            'message' => $message,
            'code' => $statusCode,
            'results' => $data
        ];

        // Send the response
        if ($isSuccess) {
            return $template;
        }

        $template['message'] = $message;
        if ($data) {
            $template['results'] = $data;

        }
        return $template;
    }

    /**
     * Send any success response
     *
     * @param string $message
     * @param object|array|null $data
     * @param integer $statusCode
     * @return array
     */
    public function success(string $message, $data = null, int $statusCode = 200): array
    {
        return $this->coreResponse($message, $statusCode, $data);
    }

    /**
     * Send any error response
     *
     * @param string $message
     * @param integer $statusCode
     * @param object|array|null $data
     * @return array
     */
    public function error(string $message, $data = null, int $statusCode = 500): array
    {
        return $this->coreResponse($message, $statusCode, $data, false);
    }

}
