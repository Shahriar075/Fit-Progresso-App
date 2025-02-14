<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserActive{

    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->active) {
            return response()->json(['error' => 'Your account is deactivated.'], 403);
        }

        return $next($request);
    }
}
