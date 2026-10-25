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
        $middleware->redirectUsersTo(fn () => auth()->user()?->role === 'bhw' ? '/bhw/dashboard' : '/resident/home');
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        if ($e->getStatusCode() === 419) {
            return redirect('/login')->with('status', 'Your session expired. Please try again.');
        }
            });
    })->create();
