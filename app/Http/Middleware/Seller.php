<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Seller
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

        if(option('multi_vendor_system_status','false')=="false"){
            abort(404);
        }
        if(Auth::guard('seller')->check()){

            if (Auth::guard('seller')->user()->status_register=="complete"){
                if (Auth::guard('seller')->user()->status=="ACTIVE"){
                    config()->set('auth.defaults.guard', 'seller');
                    return $next($request);
                }else{
                    Auth::guard('seller')->logout();
                    session()->put('toast-error', 'حساب کاربری شما غیر فعال شده است.');
                    return redirect('/seller/login');
                }
            }else{
                Auth::guard('seller')->logout();
                session()->put('toast-error', 'ثبت نام تکمیل نشده است.');
                return redirect('/seller/login');
            }

        }
        return redirect('/seller/login');

        /* if (auth()->check() && auth()->user()->isAdmin()) {
             return $next($request);
         }

         abort(403);*/
    }

}
