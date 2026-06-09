<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $whitelist = array('127.0.0.1', "::1");

        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            return $next($request);
        }
        if (!$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);

    }
}
