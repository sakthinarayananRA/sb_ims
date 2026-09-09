<?php

use App\Exceptions\InsufficientStockException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
        // Insufficient stock domain exception
        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            return $e->render($request);
        });

        // Corrupted or invalid encrypted token
        $exceptions->render(function (DecryptException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The provided encrypted customer token is invalid or corrupted.',
                    'errors' => [
                        'customer_token' => ['Invalid encryption payload.'],
                    ],
                ], 422);
            }
        });

        // Form Request and generic validation failures
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Model not found
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested resource was not found.',
                ], 404);
            }
        });

        // 404 Not Found HTTP exception
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage() ?: 'Resource not found.',
                ], 404);
            }
        });
    })->create();
