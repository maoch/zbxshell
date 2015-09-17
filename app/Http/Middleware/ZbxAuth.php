<?php

namespace App\Http\Middleware;

use Closure;

class ZbxAuth
{

    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($_COOKIE['username']) && isset($_COOKIE['password']) && isset($_COOKIE['sessionid'])) {
            return $next($request);
        } else {
            return redirect('/');
        }

    }
}
