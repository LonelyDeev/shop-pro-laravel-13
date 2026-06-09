<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // مسیر درخواست بدون اسلش‌های اول و آخر
        $currentUrl = trim($request->path(), '/');

        // ۱️⃣ اگر فایل .env حاضر نیست، ادامه می‌دهیم
        if (!file_exists(base_path('.env'))) {
            return $next($request);
        }

        // ۲️⃣ بررسی اتصال به دیتابیس
        try {
            DB::connection()->getPdo();          // سعی در برقراری اتصال
        } catch (\Exception $e) {
            return $next($request);
        }

        // ۳️⃣ قبل از کوئری، وجود جدول redirects را چک می‌کنیم
        if (!Schema::hasTable('redirects')) {
            // جدول هنوز ساخته نشده (مثلاً در حین نصب) → ادامه بدون ریدایرکت
            return $next($request);
        }

        // ۴️⃣ اگر جدول موجود بود، ریدایرکت را جستجو می‌کنیم
        $redirect = Redirect::where('from', $currentUrl)->first();

        if ($redirect) {
            return redirect($redirect->to, $redirect->type);
        }

        // ۵️⃣ در غیر این صورت به میدلورهای بعدی می‌رویم
        return $next($request);
    }
}
