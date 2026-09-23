<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Central API response envelope.
 *
 * Success:      { "success": true, "message"?: string, "data"?: mixed, "meta"?: array }
 * Error:        { "success": false, "message": string, "code"?: string, "errors"?: array }
 */
final class ApiResponse
{
    public const DEFAULT_ERROR_MESSAGE = 'Quelque chose s\'est mal passé.';

    public static function success(mixed $data = null, string $message = '', array $meta = [], int $status = 200): JsonResponse
    {
        $payload = ['success' => true];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function created(mixed $data = null, string $message = '', array $meta = []): JsonResponse
    {
        return self::success($data, $message, $meta, 201);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public static function error(
        string $message,
        int $status = 400,
        ?string $code = null,
        ?array $errors = null,
        array $headers = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status, $headers);
    }

    public static function fromException(\Throwable $e): JsonResponse
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return self::error('Les données fournies sont invalides.', 422, 'validation_error', $e->errors());
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return self::error('Authentification requise.', 401, 'unauthenticated');
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return self::error('Accès non autorisé.', 403, 'forbidden');
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return self::error('Ressource introuvable.', 404, 'not_found');
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            return self::error($e->getMessage() !== '' ? $e->getMessage() : self::DEFAULT_ERROR_MESSAGE, $e->getStatusCode());
        }

        return self::error(self::DEFAULT_ERROR_MESSAGE, 500, 'server_error');
    }
}