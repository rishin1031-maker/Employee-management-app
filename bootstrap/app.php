<?php

use App\Support\DatabaseConnectionErrors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.auth'            => \App\Http\Middleware\AdminAuth::class,
            'employee.auth'         => \App\Http\Middleware\EmployeeAuth::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'api.password'          => \App\Http\Middleware\ApiForcePasswordChange::class,
        ]);

        $middleware->throttleApi('api');
    })
    ->withProviders([                                      // ← ADD THIS
        App\Providers\RepositoryServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Token is invalid.'], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Token has expired.', 'code' => 'token_expired'], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! DatabaseConnectionErrors::isUnavailable($e)) {
                return null;
            }

            Log::error('Database server unavailable', ['exception' => $e]);

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => DatabaseConnectionErrors::userMessage(),
                ], 503);
            }

            return response()->view('errors.database-unavailable', [], 503);
        });
    })->create();
