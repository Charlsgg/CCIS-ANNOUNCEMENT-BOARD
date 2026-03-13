<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserType;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 1. Alias your custom middleware
        $middleware->alias([
            'check_type' => CheckUserType::class,
        ]);

        // 2. CSRF Exception
        // Note: Using 'forgot-password' matches your Route::post('/forgot-password')
        $middleware->validateCsrfTokens(except: [
            'forgot-password', 
            'login',    // I recommend adding these two temporarily 
            'register', // while you debug the session issue
        ]);

        // 3. Since you are using a Monolith, you can actually REMOVE 
        // $middleware->statefulApi(); 
        // It's designed for external SPAs and can sometimes interfere 
        // with standard web sessions on localhost.

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();