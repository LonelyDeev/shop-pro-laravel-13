@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/statistics/viewsList.css')}}">
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
                                    <li class="breadcrumb-item active">بازدیدها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ============ آمار ============ --}}
                <div class="vl-stats">
                    <div class="vl-stat">
                        <div class="vl-stat__icon" style="--c1:#34D399;--c2:#059669;"><i class="feather icon-eye"></i></div>
                        <div>
                            <span class="vl-stat__value">{{ number_format($stats['views']) }}</span>
                            <span class="vl-stat__label">کل بازدیدها</span>
                        </div>
                    </div>
                    <div class="vl-stat">
                        <div class="vl-stat__icon" style="--c1:#818CF8;--c2:#4F46E5;"><i class="feather icon-users"></i></div>
                        <div>
                            <span class="vl-stat__value">{{ number_format($stats['unique']) }}</span>
                            <span class="vl-stat__label">بازدیدکنندگان یکتا</span>
                        </div>
                    </div>
                    <div class="vl-stat">
                        <div class="vl-stat__icon" style="--c1:#60A5FA;--c2:#2563EB;"><i class="feather icon-user-check"></i></div>
                        <div>
                            <span class="vl-stat__value">{{ number_format($stats['users']) }}</span>
                            <span class="vl-stat__label">کاربران عضو</span>
                        </div>
                    </div>
                    <div class="vl-stat">
                        <div class="vl-stat__icon" style="--c1:#FB923C;--c2:#EA580C;"><i class="feather icon-user"></i></div>
                        <div>
                            <span class="vl-stat__value">{{ number_format($stats['guests']) }}</span>
                            <span class="vl-stat__label">مهمان‌ها (IP یکتا)</span>
                        </div>
                    </div>
                </div>

                {{-- ============ کارت اصلی ============ --}}
                <section class="card vl-card">
                    <div class="vl-card__header">
                        <h4 class="vl-card__title"><i class="feather icon-list"></i> لیست بازدیدها</h4>

                        <nav class="vl-pills">
                            @foreach($periodLabels as $key => $label)
                                <a href="{{ request()->url() }}?period={{ $key }}"
                                   class="vl-pill {{ $activePeriod === $key ? 'vl-pill--active' : '' }}">{{ $label }}</a>
                            @endforeach
                            <button type="button" id="vl-custom-btn"
                                    class="vl-pill {{ $activePeriod === 'custom' ? 'vl-pill--active' : '' }}">
                                <i class="feather icon-calendar"></i> بازه دلخواه
                            </button>
                        </nav>
                    </div>

                    {{-- بازه دلخواه --}}
                    <form method="GET" action="{{ request()->url() }}"
                          id="vl-range-form" class="vl-range {{ $activePeriod === 'custom' ? '' : 'vl-range--hidden' }}">
                        <input type="hidden" name="period" value="custom">
                        <div class="vl-range__field">
                            <label>از تاریخ</label>
                            <input type="text" name="from_date" class="form-control persian_date_picker"
                                   value="{{ request('from_date') }}" autocomplete="off">
                        </div>
                        <div class="vl-range__field">
                            <label>تا تاریخ</label>
                            <input type="text" name="to_date" class="form-control persian_date_picker"
                                   value="{{ request('to_date') }}" autocomplete="off">
                        </div>
                        <button type="submit" class="vl-btn"><i class="feather icon-filter"></i> اعمال بازه</button>
                    </form>

                    {{-- نمودار روند --}}
                    @if(count($chart))
                        @php($maxViews = max(1, collect($chart)->max('total')))
                        <div class="vl-chart">
                            <div class="vl-chart__head">
                                <span><i class="feather icon-bar-chart-2"></i> روند بازدید</span>
                                <span class="vl-chart__range">
                                {{ substr((string) jdate($from), 0, 10) }} تا {{ substr((string) jdate($to), 0, 10) }}
                            </span>
                            </div>
                            <div class="vl-chart__bars">
                                @foreach($chart as $bar)
                                    <div class="vl-bar" title="{{ $bar['title'] }} — {{ $bar['total'] }} بازدید">
                                        <span class="vl-bar__value">{{ $bar['total'] }}</span>
                                        <span class="vl-bar__col">
                                        <span class="vl-bar__fill"
                                              style="height: {{ $bar['total'] > 0 ? max(6, (int) round($bar['total'] / $maxViews * 100)) : 0 }}%"></span>
                                    </span>
                                        <span class="vl-bar__label">{{ $bar['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- جدول / حالت خالی --}}
                    @if($views->count())
                        <div class="table-responsive">
                            <table class="table mb-0 vl-table">
                                <thead>
                                <tr>
                                    <th>کاربر</th>
                                    <th>تاریخ</th>
                                    <th class="text-center">IP</th>
                                    <th class="text-center">پلتفرم</th>
                                    <th>آدرس صفحه</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($views as $view)
                                    @php($platform = get_option_property($view->options, 'platform'))
                                    <tr>
                                        <td>
                                            @if($view->user)
                                                @php($color = $avatarColors[$view->user_id % count($avatarColors)])
                                                <div class="vl-user">
                                                    <span class="vl-user__avatar" style="background: {{ $color }}">
                                                        {{ mb_substr($view->user->fullname, 0, 1) }}
                                                    </span>
                                                    <a href="{{ route('admin.users.show', ['user' => $view->user]) }}"
                                                       target="_blank" class="vl-user__name">
                                                        {{ $view->user->fullname }} <i class="feather icon-external-link"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="vl-guest"><i class="feather icon-user"></i> مهمان</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="vl-time">
                                                <i class="feather icon-clock"></i>
                                                {{ substr((string) jdate($view->created_at), 0, 16) }}
                                            </span>
                                        </td>
                                        <td class="text-center"><span class="vl-ip">{{ $view->ip }}</span></td>
                                        <td class="text-center">
                                            <span class="vl-platform">
                                                <i class="feather {{ $platformIcon($platform) }}"></i>
                                                {{ $platform ?: 'نامشخص' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ url(urldecode($view->path)) }}" target="_blank"
                                               class="vl-url" title="{{ urldecode($view->path) }}">
                                                {{ urldecode($view->path) }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="vl-pagination">
                            <div class="vl-pagination__meta">
                                نمایش <b>{{ $views->firstItem() }}</b> تا <b>{{ $views->lastItem() }}</b> از
                                <b>{{ number_format($views->total()) }}</b> بازدید
                            </div>
                            {{ $views->links() }}
                        </div>
                    @else
                        <div class="vl-empty">
                            <div class="vl-empty__icon"><i class="feather icon-eye-off"></i></div>
                            <h5>در این بازه بازدیدی ثبت نشده!</h5>
                            <p>بازه یا فیلتر زمانی دیگری را انتخاب کنید.</p>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>


    <script>
        $(function () {
            $('#vl-custom-btn').on('click', function () {
                var $form = $('#vl-range-form');
                var wasHidden = $form.hasClass('vl-range--hidden');
                $form.toggleClass('vl-range--hidden');
                if (wasHidden) $form.find('input[name="from_date"]').trigger('focus');
            });
        });
    </script>

@endsection
