<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class PulseService
{
    protected int $cacheTtl = 15;

    public function getDashboardData(): array
    {
        return [
            'pulse'         => $this->getSystemMetrics(),
            'slow_requests' => $this->getSlowRequests(),
            'slow_queries'  => $this->getSlowQueries(),
            'exceptions'    => $this->getExceptions(),
            'queue_jobs'    => $this->getQueueJobs(),
        ];
    }

    // =====================================================
    //  System Metrics
    // =====================================================

    public function getSystemMetrics(): array
    {
        return Cache::remember('pulse.system_metrics', $this->cacheTtl, function () {
            $serverData = $this->getServerMetrics();
            $memPeak    = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
            $memLimit   = $this->parseMemoryLimit(ini_get('memory_limit'));

            $diskTotalRaw = @disk_total_space(base_path());
            $diskFreeRaw  = @disk_free_space(base_path());
            $diskAvailable = $diskTotalRaw !== false && $diskFreeRaw !== false;

            $diskTotal = $diskAvailable ? round($diskTotalRaw / 1024 / 1024 / 1024, 2) : 0;
            $diskFree  = $diskAvailable ? round($diskFreeRaw  / 1024 / 1024 / 1024, 2) : 0;
            $diskUsed  = round($diskTotal - $diskFree, 2);
            $diskPct   = $diskTotal > 0 ? round($diskUsed / $diskTotal * 100, 1) : 0;

            $dbConn     = $this->getDbConnections();
            $reqStats   = $this->getRequestStats();
            $slowReqCnt = $this->countEntries('slow_request', 1440);
            $excCount   = $this->countEntries('exception', 1440);
            $cacheStats = $this->getCacheStats();
            $queueStats = $this->getQueueStats();

            [$cpuHistory, $cpuLabels] = $this->getCpuHistory();
            [$reqHistory, $reqLabels] = $this->getRequestHistory();

            return array_merge($serverData, [
                'memory_php_peak'     => $memPeak,
                'php_memory_limit'    => $memLimit,
                'disk_total'          => $diskTotal,
                'disk_used'           => $diskUsed,
                'disk_free'           => $diskFree,
                'disk_percent'        => $diskPct,
                'db_connections'      => $dbConn,
                'slow_requests_count' => $slowReqCnt,
                'exceptions_count'    => $excCount,
                'cpu_history'         => $cpuHistory,
                'cpu_labels'          => $cpuLabels,
                'req_history'         => $reqHistory,
                'req_labels'          => $reqLabels,
            ], $reqStats, $cacheStats, $queueStats);
        });
    }

    protected function getServerMetrics(): array
    {
        $defaults = ['cpu' => 0, 'memory_used' => 0, 'memory_total' => 0, 'memory_percent' => 0];

        try {
            $row = DB::table('pulse_values')
                ->where('type', 'system')
                ->orderByDesc('timestamp')
                ->limit(1)
                ->first();

            if ($row) {
                $payload  = json_decode($row->value, true);
                if (is_array($payload)) {
                    $memUsed  = (int) ($payload['memory_used']  ?? 0);
                    $memTotal = (int) ($payload['memory_total'] ?? 0);
                    return [
                        'cpu'            => round((float) ($payload['cpu'] ?? 0), 1),
                        'memory_used'    => $memUsed,
                        'memory_total'   => $memTotal,
                        'memory_percent' => $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0,
                    ];
                }
            }
        } catch (\Exception) {}

        return array_merge($defaults, $this->getMetricsFromOS());
    }

    protected function getMetricsFromOS(): array
    {
        $cpu = 0; $memUsed = 0; $memTotal = 0; $available = false;

        if (PHP_OS_FAMILY === 'Linux') {
            if ($this->isFuncAllowed('sys_getloadavg')) {
                $load = @sys_getloadavg();
                if (is_array($load)) {
                    $cores = $this->getCpuCores();
                    $cpu = round(min(100, ($load[0] / $cores) * 100), 1);
                    $available = true;
                }
            }

            if (@is_readable('/proc/meminfo')) {
                $info = @file_get_contents('/proc/meminfo');
                if ($info !== false) {
                    preg_match('/MemTotal:\s+(\d+)/', $info, $mt);
                    preg_match('/MemAvailable:\s+(\d+)/', $info, $ma);
                    if ($mt && $ma) {
                        $memTotal = (int) round($mt[1] / 1024);
                        $memUsed  = $memTotal - (int) round($ma[1] / 1024);
                        $available = true;
                    }
                }
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            // --- CPU از طریق wmic ---
            if ($this->isFuncAllowed('shell_exec')) {
                $out = @shell_exec('wmic cpu get loadpercentage 2>nul');
                if ($out) {
                    preg_match('/\d+/', $out, $m);
                    if (!empty($m)) {
                        $cpu = (float) $m[0];
                        $available = true;
                    }
                }

                // --- RAM از طریق wmic ---
                $memOut = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value 2>nul');
                if ($memOut) {
                    preg_match('/TotalVisibleMemorySize=(\d+)/', $memOut, $tot);
                    preg_match('/FreePhysicalMemory=(\d+)/', $memOut, $free);
                    if ($tot && $free) {
                        $memTotal = (int) round($tot[1] / 1024); // KB -> MB
                        $memUsed  = $memTotal - (int) round($free[1] / 1024);
                        $available = true;
                    }
                }
            }
        }

        if (!$available) {
            // فقط مصرف حافظه خود پروسه PHP رو نشون بده (بهتر از هیچی)
            $memUsed = round(memory_get_usage(true) / 1024 / 1024, 1);
        }

        return [
            'cpu'            => $cpu,
            'memory_used'    => $memUsed,
            'memory_total'   => $memTotal,
            'memory_percent' => $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0,
            'os_metrics_available' => $available,
        ];
    }

    protected function getCpuHistory(): array
    {
        $history = []; $labels = [];
        try {
            $rows = DB::table('pulse_values')
                ->where('type', 'system')
                ->orderByDesc('timestamp')
                ->limit(12)
                ->get(['timestamp', 'value']);

            foreach ($rows->reverse() as $row) {
                $p = json_decode($row->value, true);
                $history[] = round((float)($p['cpu'] ?? 0), 1);
                $labels[]  = date('H:i', $row->timestamp);
            }
        } catch (\Exception) {}

        if (empty($history)) {
            for ($i = 11; $i >= 0; $i--) {
                $history[] = 0;
                $labels[]  = date('H:i', now()->subMinutes($i * 5)->timestamp);
            }
        }
        return [$history, $labels];
    }

    protected function getRequestHistory(): array
    {
        $history = array_fill(0, 24, 0);
        $labels  = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        }
        try {
            $rows = DB::table('pulse_aggregates')
                ->where('type', 'request')
                ->where('aggregate', 'count')
                ->where('period', 60)
                ->where('bucket', '>=', now()->subDay()->timestamp)
                ->get(['bucket', 'value']);

            foreach ($rows as $row) {
                $history[(int) date('G', $row->bucket)] += (int) $row->value;
            }
        } catch (\Exception) {}
        return [$history, $labels];
    }

    protected function getRequestStats(): array
    {
        try {
            $since = now()->subDay()->timestamp;
            $total = DB::table('pulse_aggregates')
                ->where('type', 'request')->where('aggregate', 'count')
                ->where('period', 60)->where('bucket', '>=', $since)->sum('value');
            $perMin = DB::table('pulse_aggregates')
                ->where('type', 'request')->where('aggregate', 'count')
                ->where('period', 60)->where('bucket', '>=', now()->subMinute()->timestamp)->sum('value');
            return ['total_requests' => (int)$total, 'requests_per_min' => (int)$perMin];
        } catch (\Exception) {
            return ['total_requests' => 0, 'requests_per_min' => 0];
        }
    }

    protected function countEntries(string $type, int $minutes = 1440): int
    {
        try {
            return (int) DB::table('pulse_entries')
                ->where('type', $type)
                ->where('timestamp', '>=', now()->subMinutes($minutes)->timestamp)
                ->count();
        } catch (\Exception) { return 0; }
    }

    protected function getDbConnections(): array
    {
        try {
            $r = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            if (empty($r)) {
                return ['value' => 0, 'available' => false];
            }
            return ['value' => (int) ($r[0]->Value ?? 0), 'available' => true];
        } catch (\Exception) {
            return ['value' => 0, 'available' => false];
        }
    }

    protected function getCacheStats(): array
    {
        try {
            $since = now()->subDay()->timestamp;
            $hits   = (int) DB::table('pulse_aggregates')->where('type','cache_hit')
                ->where('aggregate','count')->where('bucket','>=',$since)->sum('value');
            $misses = (int) DB::table('pulse_aggregates')->where('type','cache_miss')
                ->where('aggregate','count')->where('bucket','>=',$since)->sum('value');
            $writes = (int) DB::table('pulse_aggregates')->where('type','cache_write')
                ->where('aggregate','count')->where('bucket','>=',$since)->sum('value');
            $total  = max($hits + $misses, 1);
            return [
                'cache_hits'     => $hits,
                'cache_misses'   => $misses,
                'cache_writes'   => $writes,
                'cache_hit_rate' => round($hits / $total * 100, 1),
            ];
        } catch (\Exception) {
            return ['cache_hits'=>0,'cache_misses'=>0,'cache_writes'=>0,'cache_hit_rate'=>0];
        }
    }

    protected function getQueueStats(): array
    {
        try {
            $since = now()->subDay()->timestamp;
            return [
                'queue_done'    => (int) DB::table('pulse_entries')->where('type','processed')  ->where('timestamp','>=',$since)->count(),
                'queue_running' => (int) DB::table('pulse_entries')->where('type','processing') ->where('timestamp','>=',$since)->count(),
                'queue_failed'  => (int) DB::table('pulse_entries')->where('type','failed_job') ->where('timestamp','>=',$since)->count(),
                'queue_pending' => (int) DB::table('pulse_entries')->where('type','queued')     ->where('timestamp','>=',$since)->count(),
            ];
        } catch (\Exception) {
            return ['queue_done'=>0,'queue_running'=>0,'queue_failed'=>0,'queue_pending'=>0];
        }
    }

    // =====================================================
    //  Slow Requests - جزئیات کامل
    //  key = "METHOD /uri"  |  value = duration ms
    // =====================================================

    public function getSlowRequests(): array
    {
        return Cache::remember('pulse.slow_requests', $this->cacheTtl, function () {
            try {
                return DB::table('pulse_entries')
                    ->where('type', 'slow_request')
                    ->where('timestamp', '>=', now()->subDay()->timestamp)
                    ->orderByDesc('value')
                    ->limit(50)
                    ->get(['timestamp', 'key', 'value'])
                    ->map(function ($row) {
                        $parts   = explode(' ', $row->key ?? '', 2);
                        $method  = count($parts) === 2 ? strtoupper($parts[0]) : 'GET';
                        $uri     = count($parts) === 2 ? $parts[1] : ($row->key ?? '-');
                        return [
                            'method'   => $method,
                            'uri'      => $uri,
                            'duration' => (int) ($row->value ?? 0),
                            'time'   => Jalalian::fromDateTime(date('Y-m-d H:i:s', $row->timestamp))->format('Y-m-d H:i:s'),
                            'time_ago'    => Jalalian::fromDateTime(date('Y-m-d H:i:s',$row->timestamp))->ago(),
                        ];
                    })->toArray();
            } catch (\Exception $e) { return []; }
        });
    }

    // =====================================================
    //  Slow Queries
    //  key = SQL text  |  value = duration ms
    // =====================================================

    public function getSlowQueries(): array
    {
        return Cache::remember('pulse.slow_queries', $this->cacheTtl, function () {
            try {
                return DB::table('pulse_entries')
                    ->where('type', 'slow_query')
                    ->where('timestamp', '>=', now()->subDay()->timestamp)
                    ->orderByDesc('value')
                    ->limit(50)
                    ->get(['timestamp', 'key', 'value'])
                    ->map(function ($row) {
                        return [
                            'sql'      => $row->key ?? '-',
                            'duration' => (int) ($row->value ?? 0),
                            'time'   => Jalalian::fromDateTime(date('Y-m-d H:i:s', $row->timestamp))->format('Y-m-d H:i:s'),
                            'time_ago'    => Jalalian::fromDateTime(date('Y-m-d H:i:s',$row->timestamp))->ago(),
                        ];
                    })->toArray();
            } catch (\Exception $e) { return []; }
        });
    }

    // =====================================================
    //  Exceptions
    //  key = "ClassName:message"  |  value = null
    // =====================================================

    public function getExceptions(): array
    {
        return Cache::remember('pulse.exceptions', $this->cacheTtl, function () {
            try {
                $rows = DB::table('pulse_entries')
                    ->where('type', 'exception')
                    ->where('timestamp', '>=', now()->subDay()->timestamp)
                    ->orderByDesc('timestamp')
                    ->limit(100)
                    ->get(['timestamp', 'key']);

                return $rows
                    ->groupBy('key')
                    ->map(function ($group) {
                        $first    = $group->first();
                        $key      = $first->key ?? '';
                        $colonPos = strpos($key, ':');
                        $class    = $colonPos !== false ? substr($key, 0, $colonPos) : $key;
                        $message  = $colonPos !== false ? substr($key, $colonPos + 1) : '';

                        return [
                            'class'       => $class,
                            'class_short' => class_basename($class),
                            'message'     => $message,
                            'count'       => $group->count(),
                            'last_seen'   => Jalalian::fromDateTime(date('Y-m-d H:i:s', $group->max('timestamp')))->format('Y-m-d H:i:s'),
                            'time_ago'    => Jalalian::fromDateTime(date('Y-m-d H:i:s', $group->max('timestamp')))->ago(),
                        ];
                    })
                    ->sortByDesc('count')
                    ->values()
                    ->toArray();
            } catch (\Exception $e) { return []; }
        });
    }

    // =====================================================
    //  Queue Jobs
    //  key = "queue:JobClass"  |  value = duration ms
    // =====================================================

    public function getQueueJobs(): array
    {
        return Cache::remember('pulse.queue_jobs', $this->cacheTtl, function () {
            try {
                $statusMap = [
                    'queued'     => 'pending',
                    'processing' => 'running',
                    'processed'  => 'done',
                    'failed_job' => 'failed',
                    'slow_job'   => 'slow',
                ];

                return DB::table('pulse_entries')
                    ->whereIn('type', array_keys($statusMap))
                    ->where('timestamp', '>=', now()->subDay()->timestamp)
                    ->orderByDesc('timestamp')
                    ->limit(50)
                    ->get(['timestamp', 'type', 'key', 'value'])
                    ->map(function ($row) use ($statusMap) {
                        $key      = $row->key ?? '';
                        $colonPos = strpos($key, ':');
                        $queue    = $colonPos !== false ? substr($key, 0, $colonPos) : 'default';
                        $job      = $colonPos !== false ? substr($key, $colonPos + 1) : $key;

                        return [
                            'job'       => $job,
                            'job_short' => class_basename($job),
                            'queue'     => $queue,
                            'status'    => $statusMap[$row->type] ?? $row->type,
                            'duration'  => $row->value ? round($row->value / 1000, 2) : null,
                            'time'   => Jalalian::fromDateTime(date('Y-m-d H:i:s', $row->timestamp))->format('Y-m-d H:i:s'),
                            'time_ago'    => Jalalian::fromDateTime(date('Y-m-d H:i:s',$row->timestamp))->ago(),
                        ];
                    })->toArray();
            } catch (\Exception $e) { return []; }
        });
    }

    // =====================================================
    //  Helpers
    // =====================================================

    protected function parseMemoryLimit(string $limit): int
    {
        $unit  = strtolower(substr(trim($limit), -1));
        $value = (int) $limit;
        return match ($unit) {
            'g' => $value * 1024,
            'm' => $value,
            'k' => max(1, (int)($value / 1024)),
            default => max(1, (int)($value / 1024 / 1024)),
        };
    }

    protected function timeAgo(int $timestamp): string
    {
        $diff = now()->timestamp - $timestamp;
        if ($diff < 60)    return $diff . ' ثانیه پیش';
        if ($diff < 3600)  return (int)($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return (int)($diff / 3600) . ' ساعت پیش';
        return (int)($diff / 86400) . ' روز پیش';
    }

    public function clearCache(): void
    {
        foreach (['pulse.system_metrics','pulse.slow_requests','pulse.slow_queries','pulse.exceptions','pulse.queue_jobs'] as $k) {
            Cache::forget($k);
        }
    }

    protected function isFuncAllowed(string $func): bool
    {
        if (!function_exists($func)) return false;
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array($func, $disabled, true);
    }

    protected function getCpuCores(): int
    {
        if ($this->isFuncAllowed('shell_exec')) {
            $nproc = @shell_exec('nproc 2>/dev/null');
            if ($nproc !== null && is_numeric(trim($nproc))) {
                return (int) trim($nproc);
            }
        }

        if (@is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== false) {
                $cores = preg_match_all('/^processor\s+:\s+\d+/m', $cpuinfo, $matches);
                if ($cores > 0) return $cores;
            }
        }

        return 1;
    }


}
