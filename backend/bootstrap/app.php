<?php

use App\Exceptions\Api\ApiException;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (ApiException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() === 0 ? 422 : $e->getCode(), $e->errorCode(), $e->errors());
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error('Les données fournies sont invalides.', 422, 'validation_error', $e->errors());
        });

        $exceptions->render(function (AuthenticationException $e) {
            return ApiResponse::error('Authentification requise.', 401, 'unauthenticated');
        });

        $exceptions->render(function (AuthorizationException $e) {
            return ApiResponse::error('Accès non autorisé.', 403, 'forbidden');
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return ApiResponse::error('Ressource introuvable.', 404, 'not_found');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
            return ApiResponse::error('Trop de tentatives. Réessayez plus tard.', 429, 'too_many_requests');
        });
    })->create();