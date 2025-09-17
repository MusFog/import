<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Wraps data or delegates errors to a standard shape.
     *
     * @param mixed $text
     * @param int $statusCode
     * @return JsonResponse
     */
    public function toResponse($text, int $statusCode = 200): JsonResponse
    {
        if ($statusCode >= 400) {
            return $this->toError($text, $statusCode);
        }

        return response()->json([
            'data' => $text,
        ], $statusCode);
    }

    /**
     * Standard error shape: {"ERROR": ...}.
     * For forming a common error contract.
     *
     * @param mixed $text
     * @param int $statusCode
     * @return JsonResponse
     */
    public function toError($text, int $statusCode): JsonResponse
    {
        return response()->json([
            'ERROR' => $text,
        ], $statusCode);
    }
}
