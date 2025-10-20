<?php

use App\Http\Middleware\BingoAuthenticate;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            return route('bingo.register_index');
        });
        $middleware->redirectUsersTo(function (Request $request) {
            return route('bingo.index');
        });
        $middleware->alias([
            'bingo.auth' => BingoAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
        if ($e instanceof AuthenticationException) {
            return redirect()->route('bingo.register_index');
        }

        if ($e instanceof NotFoundHttpException) {
            return redirect()->route('bingo.register_index');
        }
    });
    })->create();
