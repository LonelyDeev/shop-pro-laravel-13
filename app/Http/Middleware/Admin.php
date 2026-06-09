<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::guard('adminPanel')->check()){
            config()->set('auth.defaults.guard', 'adminPanel');
            return $next($request);
        }
        return redirect()->route('admin.login');

        /* if (auth()->check() && auth()->user()->isAdmin()) {
             return $next($request);
         }

         abort(403);*/
    }

}
