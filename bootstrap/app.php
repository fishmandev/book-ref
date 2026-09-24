<?php

use App\Exceptions\Referral\ReferralException;
use App\Http\Middleware\ResolveCurrentMaster;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Authentication is stubbed in this test project.
        // The current master is taken from the X-Master-Id header.
        $middleware->api(prepend: [
            ResolveCurrentMaster::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport(ReferralException::class);

        $exceptions->render(function (ReferralException $exception, Request $request) {
            Log::warning('Referral attachment failed', [
                'exception' => $exception::class,
                ...$exception->context,
            ]);

            return response()->json(['details' => $exception->details], 422);
        });
    })->create();
