<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsPrinter
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (auth()->user()->role == 'print') {
            $request->hasRole = true;
        }
        return $next($request);
    }
}
