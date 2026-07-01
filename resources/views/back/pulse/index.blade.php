@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/pulse-monitor.css') }}">
@endpush

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper pulse-page">

            {{-- Breadcrumb --}}
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2">
                    <ol class="breadcrumb no-border" style="background:transparent;padding:12px 24px 0;">
                        <li class="breadcrumb-item" style="color:#64748b;"><i class="feather icon-home"></i> مدیریت</li>
                        <li class="breadcrumb-item active" style="color:#a78bfa;">مانیتور سیستم</li>
                    </ol>
                </div>
            </div>

            {{-- Hero --}}
            <div style="padding:0 24px;">
                <div class="p-hero" style="border-radius:16px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="position:relative;z-index:1;gap:10px;">
                        <div>
                            <h1 class="p-hero-title">
                                <i class="feather icon-activity" style="-webkit-text-fill-color:#a78bfa;margin-left:6px;"></i>
                                مانیتور سیستم
                                <span class="live-dot loading" id="liveDot"></span>
                            </h1>
                            <p class="p-hero-sub">
                                Laravel Pulse — نظارت زنده
                                <span id="sse-status">در حال اتصال...</span>
                            </p>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <span id="last-update" style="font-size:11px;color:var(--pm);align-self:center;"></span>
                            <button class="p-btn" onclick="forceRefresh()">
                                <i class="feather icon-refresh-cw" style="font-size:12px;margin-left:4px;"></i> رفرش فوری
                            </button>
                            <a href="{{ route('pulse') }}" target="_blank" class="p-btn">
                                <i class="feather icon-external-link" style="font-size:12px;margin-left:4px;"></i> Pulse اصلی
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{--  هشدار محدودیت‌های هاست اشتراکی                      --}}
            {{-- ===================================================== --}}
            @php
                $caps = $pulse['capabilities'] ?? [];
                $missing = [];
                if (!$caps['sys_getloadavg'] ?? true) $missing[] = 'دریافت CPU';
                if (!$caps['proc_meminfo'] ?? true) $missing[] = 'اطلاعات حافظه';
                if (!$caps['disk_total_space'] ?? true) $missing[] = 'اطلاعات دیسک';
                if (!$caps['shell_exec'] ?? true) $missing[] = 'اجرای دستورات سیستمی';
                if (!$caps['file_get_contents'] ?? true) $missing[] = 'خواندن فایل‌های سیستمی';
                $hasWarning = count($missing) > 0;
            @endphp
            @if($hasWarning)
                <div style="padding:0 24px;margin-bottom:20px;margin-top:20px;">
                    <div style="
            background: linear-gradient(135deg, rgba(255,107,107,0.08) 0%, rgba(246,201,14,0.08) 100%);
            border: 1px solid rgba(255,107,107,0.15);
            border-radius: 14px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            flex-wrap: wrap;
        ">
                        {{-- Icon --}}
                        <div style="
                width: 48px;
                height: 48px;
                min-width: 48px;
                background: linear-gradient(135deg, #ff6b6b, #f6c90e);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                box-shadow: 0 4px 14px rgba(255,107,107,0.25);
            ">
                            🚀
                        </div>

                        {{-- Content --}}
                        <div style="flex:1;min-width:200px;">
                            <div style="
                    font-size: 15px;
                    font-weight: 700;
                    color: #ff6b6b;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                ">
                                ⚠️ محدودیت‌های هاست اشتراکی
                                <span style="
                        font-size: 10px;
                        font-weight: 400;
                        background: rgba(255,107,107,0.12);
                        color: #ff6b6b;
                        padding: 2px 10px;
                        border-radius: 20px;
                    ">
                        {{ count($missing) }} مورد
                    </span>
                            </div>
                            <div style="
                    font-size: 13px;
                    color: var(--pm, #94a3b8);
                    margin-top: 4px;
                    line-height: 1.6;
                ">
                                برخی از قابلیت‌های مانیتورینگ به دلیل محدودیت‌های هاست اشتراکی در دسترس نیستند:
                                <span style="color: #f6c90e;font-weight:500;">{{ implode('، ', $missing) }}</span>.
                            </div>
                            <div style="
                    font-size: 12px;
                    color: #f6c90e;
                    margin-top: 4px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    flex-wrap: wrap;
                ">
                    <span style="
                        background: rgba(246,201,14,0.12);
                        padding: 2px 12px;
                        border-radius: 20px;
                        border: 1px solid rgba(246,201,14,0.1);
                    ">
                        💡 برای مشاهده‌ی اطلاعات کامل، از سرور مجازی (VPS) یا سرور اختصاصی استفاده کنید.
                    </span>
                                <span style="
                        font-size: 10px;
                        color: var(--pm, #94a3b8);
                        opacity: 0.6;
                    ">
                        (این محدودیت‌ها توسط هاست اعمال شده‌اند)
                    </span>
                            </div>
                        </div>

                        {{-- Optional Close Button --}}
                        <button onclick="this.parentElement.parentElement.style.display='none'" style="
                background: none;
                border: none;
                color: var(--pm, #94a3b8);
                font-size: 18px;
                cursor: pointer;
                padding: 4px 8px;
                border-radius: 6px;
                transition: background 0.2s;
                align-self: flex-start;
            " onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='none'">
                            ✕
                        </button>
                    </div>
                </div>
            @endif


            {{-- KPI Row --}}
            <div class="p-kpi-grid">
                {{-- KPI CPU --}}
                <div class="p-kpi k-cpu">
                    <div style="font-size:20px;margin-bottom:6px;">⚡</div>
                    <div class="p-kpi-val" id="kpi-cpu">
                        @if(isset($pulse['cpu']) && $pulse['cpu'] > 0)
                            {{ $pulse['cpu'] }}%
                        @else
                            <span style="color:var(--pm);font-size:14px;">—</span>
                        @endif
                    </div>
                    <div class="p-kpi-label">CPU</div>
                    <div class="p-kpi-sub {{ ($pulse['cpu'] ?? 0) > 80 ? 'bad' : (($pulse['cpu'] ?? 0) > 60 ? 'warn' : 'ok') }}" id="kpi-cpu-sub">
                        @if(isset($pulse['cpu']) && $pulse['cpu'] > 0)
                            {{ ($pulse['cpu'] ?? 0) > 80 ? '⚠ بالا' : '✓ نرمال' }}
                        @else
                            <span style="color:#f6c90e;">غیرفعال</span>
                        @endif
                    </div>
                </div>

                {{-- KPI Memory --}}
                <div class="p-kpi k-mem">
                    <div style="font-size:20px;margin-bottom:6px;">💾</div>
                    <div class="p-kpi-val" id="kpi-mem">
                        @if(isset($pulse['memory_used']) && $pulse['memory_used'] > 0)
                            {{ $pulse['memory_used'] }} MB
                        @else
                            <span style="color:var(--pm);font-size:14px;">—</span>
                        @endif
                    </div>
                    <div class="p-kpi-label">Memory</div>
                    <div class="p-kpi-sub ok" id="kpi-mem-sub">
                        @if(isset($pulse['memory_percent']) && $pulse['memory_percent'] > 0)
                            {{ $pulse['memory_percent'] }}% مصرف
                        @else
                            <span style="color:#f6c90e;">غیرقابل‌دسترس</span>
                        @endif
                    </div>
                </div>

                {{-- KPI Requests --}}
                <div class="p-kpi k-req">
                    <div style="font-size:20px;margin-bottom:6px;">🌐</div>
                    <div class="p-kpi-val" id="kpi-req">{{ number_format($pulse['total_requests'] ?? 0) }}</div>
                    <div class="p-kpi-label">درخواست (۲۴h)</div>
                    <div class="p-kpi-sub warn" id="kpi-req-sub">{{ $pulse['requests_per_min'] ?? 0 }}/min</div>
                </div>

                {{-- KPI Slow Requests --}}
                <div class="p-kpi k-slow">
                    <div style="font-size:20px;margin-bottom:6px;">🐢</div>
                    <div class="p-kpi-val {{ ($pulse['slow_requests_count'] ?? 0) > 0 ? 'bad' : '' }}" id="kpi-slow">{{ $pulse['slow_requests_count'] ?? 0 }}</div>
                    <div class="p-kpi-label">درخواست کند</div>
                    <div class="p-kpi-sub {{ ($pulse['slow_requests_count'] ?? 0) > 0 ? 'bad' : 'ok' }}" id="kpi-slow-sub">
                        {{ ($pulse['slow_requests_count'] ?? 0) > 0 ? 'نیاز به بررسی' : '✓ بدون مشکل' }}
                    </div>
                </div>

                {{-- KPI Exceptions --}}
                <div class="p-kpi k-exc">
                    <div style="font-size:20px;margin-bottom:6px;">🔥</div>
                    <div class="p-kpi-val {{ ($pulse['exceptions_count'] ?? 0) > 0 ? 'bad' : '' }}" id="kpi-exc">{{ $pulse['exceptions_count'] ?? 0 }}</div>
                    <div class="p-kpi-label">خطا (۲۴h)</div>
                    <div class="p-kpi-sub {{ ($pulse['exceptions_count'] ?? 0) > 10 ? 'bad' : 'ok' }}" id="kpi-exc-sub">
                        {{ ($pulse['exceptions_count'] ?? 0) > 10 ? '⚠ زیاد' : (($pulse['exceptions_count'] ?? 0) > 0 ? '⚠ دارد' : '✓ پاک') }}
                    </div>
                </div>

                {{-- KPI Cache --}}
                <div class="p-kpi k-cache">
                    <div style="font-size:20px;margin-bottom:6px;">⚡</div>
                    <div class="p-kpi-val" id="kpi-cache">{{ $pulse['cache_hit_rate'] ?? 0 }}%</div>
                    <div class="p-kpi-label">Cache Hit</div>
                    <div class="p-kpi-sub {{ ($pulse['cache_hit_rate'] ?? 0) >= 80 ? 'ok' : 'warn' }}" id="kpi-cache-sub">
                        {{ ($pulse['cache_hit_rate'] ?? 0) >= 80 ? '✓ خوب' : '⚠ بهینه‌سازی کن' }}
                    </div>
                </div>
            </div>

            {{-- Row 1: CPU Chart + Server Resources --}}
            <div class="p-grid p-grid-2">

                {{-- CPU Chart --}}
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-cpu"></i> تاریخچه CPU</h5>
                        <span class="p-tag tag-purple">Live</span>
                    </div>
                    <div class="p-card-body" style="padding-bottom:6px;">
                        @if(isset($pulse['cpu']) && $pulse['cpu'] > 0)
                            <div id="cpu-chart"></div>
                        @else
                            <div class="p-empty" style="padding:30px 0;">
                                <i class="feather icon-alert-circle" style="color:#f6c90e;font-size:28px;"></i>
                                <span style="font-size:13px;color:var(--pm);margin-top:10px;">
                                    داده‌های CPU در دسترس نیست (همه مقادیر صفر هستند)
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Server Resources --}}
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-server"></i> منابع سرور</h5>
                        <span class="p-tag tag-teal">System</span>
                    </div>
                    <div class="p-card-body">
                        {{-- Gauge rings --}}
                        <div class="gauge-row mb-3">

                            {{-- CPU Gauge --}}
                            <div class="g-item">
                                @if(isset($pulse['cpu']) && $pulse['cpu'] > 0)
                                    <div class="g-ring" id="ring-cpu" style="background:conic-gradient(#6c63ff {{ ($pulse['cpu'] ?? 0) * 3.6 }}deg, rgba(255,255,255,.06) 0%);box-shadow:0 0 18px #6c63ff33;">
                                        <span class="g-val" id="ring-cpu-val">{{ $pulse['cpu'] ?? 0 }}%</span>
                                    </div>
                                    <div class="g-name">CPU</div>
                                @else
                                    <div class="g-ring" style="background:rgba(255,255,255,.05);border:2px dashed rgba(255,255,255,.15);">
                                        <span class="g-val" style="font-size:10px;color:var(--pm);">—</span>
                                    </div>
                                    <div class="g-name" style="color:var(--pm);">CPU (غیرفعال)</div>
                                @endif
                            </div>

                            {{-- RAM Gauge --}}
                            <div class="g-item">
                                @if(isset($pulse['memory_percent']) && $pulse['memory_percent'] > 0)
                                    <div class="g-ring" id="ring-mem" style="background:conic-gradient(#00d4aa {{ ($pulse['memory_percent'] ?? 0) * 3.6 }}deg, rgba(255,255,255,.06) 0%);box-shadow:0 0 18px #00d4aa33;">
                                        <span class="g-val" id="ring-mem-val">{{ $pulse['memory_percent'] ?? 0 }}%</span>
                                    </div>
                                    <div class="g-name">RAM</div>
                                @else
                                    <div class="g-ring" style="background:rgba(255,255,255,.05);border:2px dashed rgba(255,255,255,.15);">
                                        <span class="g-val" style="font-size:10px;color:var(--pm);">—</span>
                                    </div>
                                    <div class="g-name" style="color:var(--pm);">RAM (غیرقابل‌دسترس)</div>
                                @endif
                            </div>

                            {{-- Disk Gauge --}}
                            <div class="g-item">
                                @if(isset($pulse['disk_percent']) && $pulse['disk_percent'] > 0)
                                    <div class="g-ring" id="ring-disk" style="background:conic-gradient(#f6c90e {{ ($pulse['disk_percent'] ?? 0) * 3.6 }}deg, rgba(255,255,255,.06) 0%);box-shadow:0 0 18px #f6c90e33;">
                                        <span class="g-val" id="ring-disk-val">{{ $pulse['disk_percent'] ?? 0 }}%</span>
                                    </div>
                                    <div class="g-name">Disk</div>
                                @else
                                    <div class="g-ring" style="background:rgba(255,255,255,.05);border:2px dashed rgba(255,255,255,.15);">
                                        <span class="g-val" style="font-size:10px;color:var(--pm);">—</span>
                                    </div>
                                    <div class="g-name" style="color:var(--pm);">Disk (غیرفعال)</div>
                                @endif
                            </div>
                        </div>

                        {{-- RAM Meter --}}
                        <div class="meter">
                            <div class="meter-row">
                                <span>RAM</span>
                                <span id="mem-detail">
                                    @if(isset($pulse['memory_total']) && $pulse['memory_total'] > 0)
                                        {{ $pulse['memory_used'] ?? 0 }} / {{ $pulse['memory_total'] }} MB
                                    @else
                                        <span style="color:#f6c90e;">غیرقابل‌دسترس</span>
                                    @endif
                                </span>
                            </div>
                            <div class="meter-track">
                                <div class="meter-fill mf-teal" id="meter-ram" style="width:{{ ($pulse['memory_percent'] ?? 0) > 0 ? $pulse['memory_percent'] : 0 }}%;"></div>
                            </div>
                        </div>

                        {{-- Disk Meter --}}
                        <div class="meter">
                            <div class="meter-row">
                                <span>Disk</span>
                                <span>
                                    @if(isset($pulse['disk_total']) && $pulse['disk_total'] > 0)
                                        {{ $pulse['disk_used'] ?? 0 }} / {{ $pulse['disk_total'] }} GB
                                    @else
                                        <span style="color:#f6c90e;">اطلاعات در دسترس نیست</span>
                                    @endif
                                </span>
                            </div>
                            <div class="meter-track">
                                <div class="meter-fill mf-yellow" id="meter-disk" style="width:{{ ($pulse['disk_percent'] ?? 0) > 0 ? $pulse['disk_percent'] : 0 }}%;"></div>
                            </div>
                            @if(!(isset($pulse['disk_total']) && $pulse['disk_total'] > 0))
                                <div style="font-size:10px;color:#f6c90e;margin-top:4px;">
                                    <i class="feather icon-alert-circle"></i> تابع disk_total_space/disk_free_space غیرفعال است
                                </div>
                            @endif
                        </div>

                        {{-- PHP Memory Peak --}}
                        <div class="meter">
                            <div class="meter-row"><span>PHP Memory Peak</span><span>{{ $pulse['memory_php_peak'] ?? 0 }} MB</span></div>
                            <div class="meter-track"><div class="meter-fill mf-purple" style="width:{{ $pulse['php_memory_limit'] ?? 0 > 0 ? min(round(($pulse['memory_php_peak'] ?? 0) / ($pulse['php_memory_limit'] ?? 256) * 100), 100) : 0 }}%;"></div></div>
                        </div>

                        {{-- DB Connections --}}
                        <div class="meter">
                            <div class="meter-row">
                                <span>DB اتصالات فعال</span>
                                <span id="db-conn-val">
                                    @if($pulse['db_connections_avail'] ?? false)
                                        {{ $pulse['db_connections'] ?? 0 }}
                                    @else
                                        <span style="color:#f6c90e;">غیرقابل‌دسترس</span>
                                    @endif
                                </span>
                            </div>
                            <div class="meter-track">
                                <div class="meter-fill mf-blue" id="meter-db" style="width:{{ ($pulse['db_connections_avail'] ?? false) ? min(($pulse['db_connections'] ?? 0) * 4, 100) : 0 }}%;"></div>
                            </div>
                            @if(!($pulse['db_connections_avail'] ?? false))
                                <div style="font-size:10px;color:#f6c90e;margin-top:4px;">
                                    <i class="feather icon-alert-circle"></i> دسترسی به SHOW STATUS میسر نیست
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Slow Requests + Slow Queries --}}
            <div class="p-grid p-grid-2">

                {{-- Slow Requests --}}
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-zap-off"></i> درخواست‌های کند</h5>
                        <span class="p-tag tag-red" id="slow-req-count">{{ count($slow_requests) }} مورد</span>
                    </div>
                    <div class="p-scroll">
                        @if(count($slow_requests))
                            <table class="p-table" id="slow-req-table">
                                <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>مسیر (URI)</th>
                                    <th style="text-align:right;">مدت (ms)</th>
                                    <th>زمان</th>
                                </tr>
                                </thead>
                                <tbody id="slow-req-body">
                                @foreach($slow_requests as $req)
                                    <tr>
                                        <td><span class="m-badge m-{{ strtolower($req['method']) }}">{{ $req['method'] }}</span></td>
                                        <td style="max-width:220px;">
                                            <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--pt);" title="{{ $req['uri'] }}">{{ $req['uri'] }}</span>
                                        </td>
                                        <td style="text-align:right;">
                                            @php $d = $req['duration']; @endphp
                                            <span class="d-badge {{ $d > 2000 ? 'd-bad' : ($d > 1000 ? 'd-warn' : 'd-ok') }}">
                                                {{ number_format($d) }}ms
                                            </span>
                                        </td>
                                        <td style="font-size:10px;color:var(--pm);white-space:nowrap;">
                                            {{ $req['time_ago'] }}<br>
                                            <span style="font-size:9px;opacity:.6;">{{ $req['time'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-empty" id="slow-req-empty">
                                <i class="feather icon-check-circle" style="color:#43e97b;opacity:.7;"></i>
                                <span style="font-size:12px;">هیچ درخواست کندی ثبت نشده ✓</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Slow Queries --}}
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-database"></i> کوئری‌های کند</h5>
                        <span class="p-tag tag-yellow" id="slow-qry-count">{{ count($slow_queries) }} مورد</span>
                    </div>
                    <div class="p-scroll">
                        @if(count($slow_queries))
                            <table class="p-table" id="slow-qry-table">
                                <thead>
                                <tr>
                                    <th>SQL</th>
                                    <th style="text-align:right;">مدت (ms)</th>
                                    <th>زمان</th>
                                </tr>
                                </thead>
                                <tbody id="slow-qry-body">
                                @foreach($slow_queries as $q)
                                    <tr>
                                        <td>
                                            <div class="sql-code" title="{{ $q['sql'] }}">{{ Str::limit($q['sql'], 80) }}</div>
                                        </td>
                                        <td style="text-align:right;">
                                            @php $d = $q['duration']; @endphp
                                            <span class="d-badge {{ $d > 1000 ? 'd-bad' : ($d > 500 ? 'd-warn' : 'd-ok') }}">
                                                {{ number_format($d) }}ms
                                            </span>
                                        </td>
                                        <td style="font-size:10px;color:var(--pm);white-space:nowrap;">
                                            {{ $q['time_ago'] }}<br>
                                            <span style="font-size:9px;opacity:.6;">{{ $q['time'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-empty" id="slow-qry-empty">
                                <i class="feather icon-check-circle" style="color:#43e97b;opacity:.7;"></i>
                                <span style="font-size:12px;">هیچ کوئری کندی ثبت نشده ✓</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Row 3: Request Chart + Cache --}}
            <div class="p-grid p-grid-3">
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-trending-up"></i> درخواست‌ها ۲۴h</h5>
                        <span class="p-tag tag-blue">Requests</span>
                    </div>
                    <div class="p-card-body" style="padding-bottom:6px;">
                        <div id="req-chart"></div>
                    </div>
                </div>

                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-zap"></i> Cache</h5>
                        <span class="p-tag tag-green">Cache</span>
                    </div>
                    <div class="p-card-body">
                        @php
                            $ch  = $pulse['cache_hits']   ?? 0;
                            $cm  = $pulse['cache_misses'] ?? 0;
                            $cw  = $pulse['cache_writes'] ?? 0;
                            $ct  = max($ch + $cm, 1);
                            $chr = round($ch / $ct * 100, 1);
                            $r   = 38; $circ = 2 * M_PI * $r;
                            $hl  = $circ * ($chr / 100);
                            $ml  = $circ * (1 - $chr / 100);
                        @endphp
                        <div class="donut-wrap">
                            <svg width="100" height="100" viewBox="0 0 100 100" id="cache-donut">
                                <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="14"/>
                                <circle cx="50" cy="50" r="{{ $r }}" fill="none" id="cache-hit-arc"
                                        stroke="#43e97b" stroke-width="14"
                                        stroke-dasharray="{{ $hl }} {{ $circ - $hl }}"
                                        stroke-dashoffset="{{ $circ * 0.25 }}"
                                        stroke-linecap="round"/>
                                <circle cx="50" cy="50" r="{{ $r }}" fill="none" id="cache-miss-arc"
                                        stroke="#ff6b6b" stroke-width="14"
                                        stroke-dasharray="{{ $ml }} {{ $circ - $ml }}"
                                        stroke-dashoffset="{{ $circ * 0.25 - $hl }}"
                                        stroke-linecap="round"/>
                                <text x="50" y="47" text-anchor="middle" fill="#fff" font-size="14" font-weight="800" id="cache-rate-text">{{ $chr }}%</text>
                                <text x="50" y="61" text-anchor="middle" fill="#64748b" font-size="9">Hit Rate</text>
                            </svg>
                            <div class="donut-leg">
                                <div class="donut-row">
                                    <div class="donut-label"><span class="donut-dot" style="background:#43e97b;"></span>Hit</div>
                                    <div class="donut-val" id="cache-hits-val">{{ number_format($ch) }}</div>
                                </div>
                                <div class="donut-row">
                                    <div class="donut-label"><span class="donut-dot" style="background:#ff6b6b;"></span>Miss</div>
                                    <div class="donut-val" id="cache-miss-val">{{ number_format($cm) }}</div>
                                </div>
                                <div class="donut-row">
                                    <div class="donut-label"><span class="donut-dot" style="background:#f6c90e;"></span>Write</div>
                                    <div class="donut-val" id="cache-write-val">{{ number_format($cw) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="meter mt-3">
                            <div class="meter-row"><span>Hit Rate</span><span id="cache-rate-label">{{ $chr }}%</span></div>
                            <div class="meter-track"><div class="meter-fill mf-green" id="meter-cache" style="width:{{ $chr }}%"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Queue Jobs --}}
            <div class="p-full">
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-layers"></i> صف‌های پردازش</h5>
                        <span class="p-tag tag-blue">Queue</span>
                    </div>

                    <div class="q-sum-grid" id="queue-summary">
                        @php
                            $qd = $pulse['queue_done']    ?? 0;
                            $qr = $pulse['queue_running'] ?? 0;
                            $qf = $pulse['queue_failed']  ?? 0;
                            $qp = $pulse['queue_pending'] ?? 0;
                            $qt = max($qd + $qr + $qf + $qp, 1);
                        @endphp
                        @foreach([['موفق','#43e97b',$qd,'mf-green','queue_done'],['در حال اجرا','#4fc3f7',$qr,'mf-blue','queue_running'],['در انتظار','#f6c90e',$qp,'mf-yellow','queue_pending'],['شکست','#ff6b6b',$qf,'mf-red','queue_failed']] as [$lbl,$col,$val,$cls,$key])
                            <div class="q-sum-box">
                                <div class="q-sum-val" style="color:{{ $col }};" id="q-{{ $key }}">{{ number_format($val) }}</div>
                                <div class="q-sum-lbl">{{ $lbl }}</div>
                                <div class="meter-track"><div class="meter-fill {{ $cls }}" id="q-meter-{{ $key }}" style="width:{{ round($val/$qt*100) }}%"></div></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-scroll">
                        @if(count($queue_jobs))
                            <table class="p-table" id="queue-table">
                                <thead>
                                <tr>
                                    <th>Job</th>
                                    <th>Class کامل</th>
                                    <th>صف</th>
                                    <th>وضعیت</th>
                                    <th style="text-align:right;">مدت (s)</th>
                                    <th>زمان</th>
                                </tr>
                                </thead>
                                <tbody id="queue-body">
                                @foreach($queue_jobs as $job)
                                    <tr>
                                        <td style="font-family:monospace;font-size:11px;color:#a78bfa;white-space:nowrap;">{{ $job['job_short'] }}</td>
                                        <td style="max-width:180px;">
                                            <span style="font-size:10px;color:var(--pm);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $job['job'] }}">{{ $job['job'] }}</span>
                                        </td>
                                        <td><span class="d-badge d-info">{{ $job['queue'] }}</span></td>
                                        <td>
                                            <span class="q-badge q-{{ $job['status'] }}">
                                                @switch($job['status'])
                                                    @case('done')    ✓ موفق @break
                                                    @case('running') ⟳ اجرا @break
                                                    @case('failed')  ✗ شکست @break
                                                    @case('pending') ⏳ انتظار @break
                                                    @case('slow')    🐢 کند @break
                                                    @default {{ $job['status'] }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td style="text-align:right;">
                                            @if($job['duration'] !== null)
                                                <span class="d-badge {{ $job['duration'] > 30 ? 'd-bad' : ($job['duration'] > 10 ? 'd-warn' : 'd-ok') }}">
                                                    {{ $job['duration'] }}s
                                                </span>
                                            @else
                                                <span style="color:var(--pm);">—</span>
                                            @endif
                                        </td>
                                        <td style="font-size:10px;color:var(--pm);white-space:nowrap;">
                                            {{ $job['time_ago'] }}<br>
                                            <span style="font-size:9px;opacity:.6;">{{ $job['time'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-empty" id="queue-empty">
                                <i class="feather icon-layers"></i>
                                <span style="font-size:12px;">هیچ جابی ثبت نشده</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Exceptions --}}
            <div class="p-full" style="padding-top:18px;">
                <div class="p-card">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-alert-triangle"></i> خطاها (Exceptions)</h5>
                        <span class="p-tag tag-red" id="exc-count">{{ count($exceptions) }} نوع</span>
                    </div>
                    <div class="p-card-body" id="exc-body">
                        @if(count($exceptions))
                            @foreach($exceptions as $exc)
                                <div class="exc-block">
                                    <div class="exc-class" title="{{ $exc['class'] }}">
                                        {{ $exc['class_short'] }}
                                        <span style="font-size:10px;color:var(--pm);font-weight:400;">{{ $exc['class'] }}</span>
                                    </div>
                                    <div class="exc-msg">{{ $exc['message'] }}</div>
                                    <div class="exc-meta">
                                        <span class="d-badge d-bad">{{ $exc['count'] }} بار</span>
                                        <span class="exc-time"><i class="feather icon-clock" style="font-size:10px;margin-left:3px;"></i>آخرین بار: {{ $exc['time_ago'] }}</span>
                                        <span style="font-size:10px;color:var(--pm);opacity:.6;">{{ $exc['last_seen'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-empty">
                                <i class="feather icon-check-circle" style="color:#43e97b;opacity:.8;"></i>
                                <span style="font-size:12px;">هیچ خطایی ثبت نشده ✓</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{--  کارت نمایش وضعیت قابلیت‌های سرور (Capabilities)        --}}
            {{-- ===================================================== --}}
            <div class="p-full" style="padding-top:18px;">
                <div class="p-card" style="border-left:4px solid #a78bfa;">
                    <div class="p-card-head">
                        <h5 class="p-card-title"><i class="feather icon-shield"></i> وضعیت دسترسی به توابع سرور</h5>
                        <span class="p-tag tag-purple">Capabilities</span>
                    </div>
                    <div class="p-card-body" style="padding:12px 20px;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px 20px;font-size:12px;">
                            @php $caps = $pulse['capabilities'] ?? []; @endphp
                            @foreach($caps as $func => $enabled)
                                <div style="display:flex;align-items:center;gap:6px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04);">
                                    <span style="font-family:monospace;color:var(--pm);">{{ $func }}</span>
                                    <span style="margin-right:auto;">
                                        @if($enabled)
                                            <span style="color:#43e97b;">✅ فعال</span>
                                        @else
                                            <span style="color:#ff6b6b;">❌ غیرفعال</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <div style="margin-top:12px;padding:8px 12px;background:rgba(246,201,14,.08);border-radius:6px;font-size:11px;color:var(--pm);border-right:2px solid #f6c90e;">
                            <i class="feather icon-info" style="margin-left:4px;"></i>
                            توابع غیرفعال ممکن است باعث نشود برخی اطلاعات در این داشبورد نمایش داده نشوند.
                        </div>
                    </div>
                </div>
            </div>

            <div style="height:40px;"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script>
        var cpuChartData   = @json($pulse['cpu_history'] ?? array_fill(0, 12, 0));
        var cpuChartLabels = @json($pulse['cpu_labels']  ?? []);
        var reqChartData   = @json($pulse['req_history'] ?? array_fill(0, 24, 0));
        var reqChartLabels = @json($pulse['req_labels']  ?? []);
        var sseUrl = "{{ route('admin.pulse.stream') }}";
        var forceRefreshAPI="{{ route('admin.pulse.refresh') }}";
    </script>
    <script src="{{ asset('back/assets/js/pages/pulse/index.js') }}"></script>
@endpush
