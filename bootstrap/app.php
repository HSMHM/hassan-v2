<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ProposalAuth;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPageVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            TrackPageVisit::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'proposal.auth' => ProposalAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render a branded Inertia error page for HTTP exceptions in production.
        $exceptions->respond(function ($response, $exception, $request) {
            $status = $response->getStatusCode();

            if (! app()->environment(['local', 'testing'])
                && in_array($status, [403, 404, 500, 503], true)
                && ! $request->expectsJson()) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
