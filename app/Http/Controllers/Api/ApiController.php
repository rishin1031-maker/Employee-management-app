<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DatabaseConnectionErrors;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function fromException(\Throwable $e, int $fallbackStatus = 422): JsonResponse
    {
        if (DatabaseConnectionErrors::isUnavailable($e)) {
            return $this->error(DatabaseConnectionErrors::userMessage(), 503);
        }

        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : $fallbackStatus;

        return $this->error($e->getMessage(), $status);
    }
}
