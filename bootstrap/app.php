<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\EnsureSiteIsNotInMaintenance::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            $request = request();

            if (! in_array($response->getStatusCode(), [404, 500, 503], true) || ! $request->isMethod('GET')) {
                return $response;
            }

            return \Inertia\Inertia::render('Errors/Show', [
                'status' => $response->getStatusCode(),
            ])->toResponse($request)->setStatusCode($response->getStatusCode());
        });
    })->create();
