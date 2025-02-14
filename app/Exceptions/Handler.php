<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    public function render($request, Exception|Throwable $exception)
    {
        print_r("here");
        exit();
        if ($exception instanceof TokenExpiredException) {
            return response()->json(['error' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof TokenInvalidException) {
            return response()->json(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof JWTException) {
            return response()->json(['error' => 'Token absent or invalid'], Response::HTTP_UNAUTHORIZED);
        }

        return parent::render($request, $exception);
    }
}
