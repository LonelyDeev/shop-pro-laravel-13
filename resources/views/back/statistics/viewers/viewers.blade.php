@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/statistics/viewers.css')}}">
@endpush
@section('content')

    @php
        $activePeriod = in_array($period, ['daily','weekly','monthly','yearly','custom']) ? $period : 'daily';
        $periodLabels = ['daily' => 'روزانه', 'weekly' => 'هفتگی', 'monthly' => 'ماهانه', 'yearly' => 'سالانه'];

        $platformIcon = function ($p) {
            $p = mb_strtolower((string) $p);
            if ($p === '') return 'icon-globe';
            if (\Illuminate\Support\Str::contains($p, 'windows')) return 'icon-monitor';
            if (\Illuminate\Support\Str::contains($p, ['mac', 'linux'])) return 'icon-monitor';
            if (\Illuminate\Support\Str::contains($p, ['android', 'mobile', 'iphone', 'ipad', 'ios'])) return 'icon-smartphone';
            if (\Illuminate\Support\Str::contains($p, ['bot', 'spider', 'crawl'])) return 'icon-cpu';
            return 'icon-globe';
        };

        $avatarColors = ['#7c3aed', '#0ea5e9', '#059669', '#ea580c', '#db2777', '#0891b2'];
    @endphp

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">گزارشات</li>
                                    <li class="breadcrumb-item active">بازدیدکنندگان</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ============ کارت‌های آمار ============ --}}
                <div class="vw-stats">
                    <div class="vw-stat">
                        <div class="vw-stat__icon" style="--c1:#818CF8;--c2:#4F46E5;"><i class="feather icon-users"></i></div>
                        <div>
                            <span class="vw-stat__value">{{ number_format($stats['unique']) }}</span>
                            <span class="vw-stat__label">بازدیدکنندگان یکتا</span>
                        </div>
                    </div>
                    <div class="vw-stat">
                        <div class="vw-stat__icon" style="--c1:#34D399;--c2:#059669;"><i class="feather icon-eye"></i></div>
                        <div>
                            <span class="vw-stat__value">{{ number_format($stats['views']) }}</span>
                            <span class="vw-stat__label">کل بازدیدها</span>
                        </div>
                    </div>
                    <div class="vw-stat">
                        <div class="vw-stat__icon" style="--c1:#60A5FA;--c2:#2563EB;"><i class="feather icon-user-check"></i></div>
                        <div>
                            <span class="vw-stat__value">{{ number_format($stats['users']) }}</span>
                            <span class="vw-stat__label">کاربران عضو</span>
                        </div>
                    </div>
                    <div class="vw-stat">
                        <div class="vw-stat__icon" style="--c1:#FB923C;--c2:#EA580C;"><i class="feather icon-user"></i></div>
                        <div>
                            <span class="vw-stat__value">{{ number_format($stats['guests']) }}</span>
                            <span class="vw-stat__label">مهمان‌ها</span>
                        </div>
                    </div>
                </div>

                {{-- ============ کارت اصلی ============ --}}
                <section class="card vw-card">
                    <div class="vw-card__header">
                        <h4 class="vw-card__title"><i class="feather icon-map-pin"></i> لیست بازدیدکنندگان</h4>

                        <nav class="vw-pills">
                            @foreach($periodLabels as $key => $label)
                                <a href="{{ request()->url() }}?period={{ $key }}"
                                   class="vw-pill {{ $activePeriod === $key ? 'vw-pill--active' : '' }}">{{ $label }}</a>
                            @endforeach
                            <button type="button" id="vw-custom-btn"
                                    class="vw-pill {{ $activePeriod === 'custom' ? 'vw-pill--active' : '' }}">
                                <i class="feather icon-calendar"></i> بازه دلخواه
                            </button>
                        </nav>
                    </div>

                    {{-- بازه دلخواه --}}
                    <form method="GET" action="{{ request()->url() }}"
                          id="vw-range-form"
                          class="vw-range {{ $activePeriod === 'custom' ? '' : 'vw-range--hidden' }}">
                        <input type="hidden" name="period" value="custom">
                        <div class="vw-range__field">
                            <label>از تاریخ</label>
                            <input type="text" name="from_date" class="form-control persian_date_picker"
                                   value="{{ request('from_date') }}" autocomplete="off">
                        </div>
                        <div class="vw-range__field">
                            <label>تا تاریخ</label>
                            <input type="text" name="to_date" class="form-control persian_date_picker"
                                   value="{{ request('to_date') }}" autocomplete="off">
                        </div>
                        <button type="submit" class="vw-btn"><i class="feather icon-filter"></i> اعمال بازه</button>
                    </form>

                    {{-- نمودار روند --}}
                    @if(count($chart))
                        @php($maxViews = max(1, collect($chart)->max('total')))
                        <div class="vw-chart">
                            <div class="vw-chart__head">
                                <span><i class="feather icon-bar-chart-2"></i> روند بازدید</span>
                                <span class="vw-chart__range">
                                {{ substr((string) jdate($from), 0, 10) }} تا {{ substr((string) jdate($to), 0, 10) }}
                            </span>
                            </div>
                            <div class="vw-chart__bars">
                                @foreach($chart as $bar)
                                    <div class="vw-bar" title="{{ $bar['title'] }} — {{ $bar['total'] }} بازدید">
                                        <span class="vw-bar__value">{{ $bar['total'] }}</span>
                                        <span class="vw-bar__col">
                                        <span class="vw-bar__fill"
                                              style="height: {{ $bar['total'] > 0 ? max(6, (int) round($bar['total'] / $maxViews * 100)) : 0 }}%"></span>
                                    </span>
                                        <span class="vw-bar__label">{{ $bar['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- جدول / حالت خالی --}}
                    @if($viewers->count())
                        <div class="table-responsive">
                            <table class="table mb-0 vw-table">
                                <thead>
                                <tr>
                                    <th>کاربر</th>
                                    <th>آخرین بازدید</th>
                                    <th class="text-center">IP</th>
                                    <th class="text-center">پلتفرم</th>
                                    <th>آدرس صفحه</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($viewers as $viewer)
                                    @php
                                        $platform    = get_option_property($viewer->options, 'platform');
                                        $avatarColor = $avatarColors[$viewer->user_id % count($avatarColors)];
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($viewer->user)
                                                <div class="vw-user">
                                                    <span class="vw-user__avatar" style="background: {{ $avatarColor }}">
                                                        {{ mb_substr($viewer->user->fullname, 0, 1) }}
                                                    </span>
                                                    <a href="{{ route('admin.users.show', ['user' => $viewer->user]) }}"
                                                       target="_blank" class="vw-user__name">
                                                        {{ $viewer->user->fullname }} <i class="feather icon-external-link"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="vw-guest"><i class="feather icon-user"></i> مهمان</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="vw-time">
                                                <i class="feather icon-clock"></i>
                                                {{ substr((string) jdate($viewer->created_at), 0, 16) }}
                                            </span>
                                        </td>
                                        <td class="text-center"><span class="vw-ip">{{ $viewer->ip }}</span></td>
                                        <td class="text-center">
                                            <span class="vw-platform">
                                                <i class="feather {{ $platformIcon($platform) }}"></i>
                                                {{ $platform ?: 'نامشخص' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ url(urldecode($viewer->path)) }}" target="_blank"
                                               class="vw-url" title="{{ urldecode($viewer->path) }}">
                                                {{ urldecode($viewer->path) }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="vw-pagination">
                            <div class="vw-pagination__meta">
                                نمایش <b>{{ $viewers->firstItem() }}</b> تا <b>{{ $viewers->lastItem() }}</b> از
                                <b>{{ number_format($viewers->total()) }}</b> بازدیدکننده
                            </div>
                            {{ $viewers->links() }}
                        </div>
                    @else
                        <div class="vw-empty">
                            <div class="vw-empty__icon"><i class="feather icon-eye-off"></i></div>
                            <h5>در این بازه بازدیدی ثبت نشده!</h5>
                            <p>بازه یا فیلتر زمانی دیگری را انتخاب کنید.</p>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>



@endsection
@push('scripts')

    <script>
        $(function () {
            // نمایش/مخفی فرم بازه دلخواه
            $('#vw-custom-btn').on('click', function () {
                var $form = $('#vw-range-form');
                var wasHidden = $form.hasClass('vw-range--hidden');
                $form.toggleClass('vw-range--hidden');
                if (wasHidden) {
                    $form.find('input[name="from_date"]').trigger('focus');
                }
            });

            // اگر دیت‌پیکر شمسی خودکار مقداردهی نشد، این را باز کنید:
            // $('.persian_date_picker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true });
        });
    </script>
@endpush
