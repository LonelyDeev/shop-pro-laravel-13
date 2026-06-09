<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Services\PulseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PulseController extends Controller
{
    public function __construct(protected PulseService $pulseService)
    {
        // دسترسی فقط برای کسانی که permission دارن
        // اگر از spatie استفاده می‌کنید:
        // $this->middleware('can:pulse.monitor');
    }

    /**
     * نمایش صفحه اصلی مانیتور
     */
    public function index()
    {
        $data = $this->pulseService->getDashboardData();

        return view('back.pulse.index', [
            'pulse'         => $data['pulse'],
            'slow_requests' => $data['slow_requests'],
            'slow_queries'  => $data['slow_queries'],
            'exceptions'    => $data['exceptions'],
            'queue_jobs'    => $data['queue_jobs'],
        ]);
    }

    /**
     * SSE Stream - ارسال داده‌های زنده هر ۱۰ ثانیه
     * GET /admin/pulse/stream
     */
    public function stream(Request $request)
    {
        // جلوگیری از timeout
        set_time_limit(0);
        ignore_user_abort(false);

        return response()->stream(function () use ($request) {
            $counter = 0;

            while (true) {
                // اگر client قطع کرد، بسته بشه
                if (connection_aborted()) break;

                // هر ۱۰ ثانیه داده ارسال کن
                $data = $this->pulseService->getDashboardData();

                // encode برای SSE
                $json = json_encode([
                    'pulse'         => $data['pulse'],
                    'slow_requests' => array_slice($data['slow_requests'], 0, 20),
                    'slow_queries'  => array_slice($data['slow_queries'],  0, 20),
                    'exceptions'    => array_slice($data['exceptions'],    0, 20),
                    'queue_jobs'    => array_slice($data['queue_jobs'],    0, 20),
                    'ts'            => now()->timestamp,
                ]);

                echo "id: {$counter}\n";
                echo "event: pulse\n";
                echo "data: {$json}\n\n";

                ob_flush();
                flush();

                $counter++;

                // هر ۳۰ ثانیه یک heartbeat برای نگه‌داشتن connection
                if ($counter % 3 === 0) {
                    echo ": heartbeat\n\n";
                    ob_flush();
                    flush();
                }

                // کمی صبر کن (10 ثانیه)
                sleep(10);

                // اگر کاربر logout کرد یا session منقضی شد
                if (!auth()->check()) break;
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',  // مهم برای nginx
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Force refresh cache و برگشت JSON (برای دکمه رفرش دستی)
     * POST /admin/pulse/refresh
     */
    public function refresh()
    {
        $this->pulseService->clearCache();
        $data = $this->pulseService->getDashboardData();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
