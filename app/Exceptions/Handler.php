<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    // ... (keep any existing code)

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            if (Auth::check()) {
                return redirect('/');
            }
        }
        return parent::render($request, $exception);
    }
} 