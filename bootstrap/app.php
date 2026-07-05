<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveTenant::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleImpersonation::class,
        ]);
        $middleware->alias([
            'receptionist'  => \App\Http\Middleware\ReceptionistMiddleware::class,
            'hotel.staff'   => \App\Http\Middleware\EnsureHotelStaff::class,
            'hotel.setup'   => \App\Http\Middleware\EnsureHotelOwnerHasHotel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Throwable $e, $request) {
            if (! \App\Models\ErrorLog::isReportable($e)) {
                return null;
            }

            $errorLog = \App\Models\ErrorLog::recordFromThrowable($e, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message'   => 'Server Error',
                    'reference' => $errorLog->code,
                ], 500);
            }

            if (! config('app.debug')) {
                return response()->view('errors.500', ['code' => $errorLog->code], 500);
            }

            return null;
        });
    })->create();
