<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsPrinter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user() &&  auth()->user()->verified && auth()->user()->role == 'print') {
            return $next($request);
        }
        auth()->logout();
        return redirect(route('not-verify'));
    }
}
