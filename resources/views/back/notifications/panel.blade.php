@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/notifications.css')}}">
@endpush
@section('content')

    @php
        use App\Support\AdminNotificationTypes;

        $todayLabel     = jdate(now())->format('Y/m/d');
        $yesterdayLabel = jdate(now()->subDay())->format('Y/m/d');
        $prevDay        = null;
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
                                    <li class="breadcrumb-item active">اعلان‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body" id="np-app" data-filter="{{ $filter }}">

                {{-- ===== نوار بالا ===== --}}
                <div class="np-topbar">
                    <h4 class="np-title">
                        <i class="feather icon-bell"></i> اعلان‌های سیستم
                        @if($stats['unread'] > 0)
                            <span class="np-unread-badge">{{ $stats['unread'] }} خوانده‌نشده</span>
                        @endif
                    </h4>
                    <button type="button" id="np-read-all" class="np-btn np-btn--primary" {{ $stats['unread'] ? '' : 'style="display:none"' }}>
                        <i class="feather icon-check-circle"></i> همه را خوانده‌شده کن
                    </button>
                </div>

                {{-- ===== آمار ===== --}}
                <div class="np-stats">
                    <div class="np-stat">
                        <div class="np-stat__icon" style="--c1:#818CF8;--c2:#4F46E5;"><i class="feather icon-bell"></i></div>
                        <div><span class="np-stat__value">{{ number_format($stats['total']) }}</span><span class="np-stat__label">کل اعلان‌ها</span></div>
                    </div>
                    <div class="np-stat">
                        <div class="np-stat__icon" style="--c1:#FB7185;--c2:#E11D48;"><i class="feather icon-alert-circle"></i></div>
                        <div><span class="np-stat__value" id="np-stat-unread">{{ number_format($stats['unread']) }}</span><span class="np-stat__label">خوانده‌نشده</span></div>
                    </div>
                    <div class="np-stat">
                        <div class="np-stat__icon" style="--c1:#34D399;--c2:#059669;"><i class="feather icon-activity"></i></div>
                        <div><span class="np-stat__value">{{ number_format($stats['today']) }}</span><span class="np-stat__label">امروز</span></div>
                    </div>
                </div>

                {{-- ===== کارت اصلی ===== --}}
                <section class="card np-card">
                    <div class="np-card__header">
                        <nav class="np-tabs">
                            <a href="{{ request()->url() }}?filter=all"
                               class="np-tab {{ $filter === 'all' ? 'np-tab--active' : '' }}">
                                همه <span class="np-tab__count">{{ number_format($stats['total']) }}</span>
                            </a>
                            <a href="{{ request()->url() }}?filter=unread"
                               class="np-tab {{ $filter === 'unread' ? 'np-tab--active' : '' }}">
                                خوانده‌نشده <span class="np-tab__count np-tab__count--hot" id="np-tab-unread">{{ number_format($stats['unread']) }}</span>
                            </a>
                            <a href="{{ request()->url() }}?filter=read"
                               class="np-tab {{ $filter === 'read' ? 'np-tab--active' : '' }}">
                                خوانده‌شده <span class="np-tab__count">{{ number_format($stats['total'] - $stats['unread']) }}</span>
                            </a>
                        </nav>
                    </div>

                    <div class="np-card__body">

                        @if($notifications->count())
                            <div class="np-list">
                                @foreach ($notifications as $notification)
                                    @php
                                        $meta    = AdminNotificationTypes::meta($notification->type);
                                        $allowed = is_null($meta['can']) || Gate::allows($meta['can']);
                                    @endphp

                                    @continue(! $allowed)

                                    @php
                                        // گروه‌بندی روزانه
                                        $day = jdate($notification->created_at)->format('Y/m/d');
                                        $dayLabel = $day === $todayLabel ? 'امروز'
                                            : ($day === $yesterdayLabel ? 'دیروز' : $day);
                                    @endphp

                                    @if($dayLabel !== $prevDay)
                                        @php($prevDay = $dayLabel)
                                        <div class="np-day">
                                            <span>{{ $dayLabel }}</span>
                                            <i></i>
                                        </div>
                                    @endif

                                    @php($notification_link = notification_link($notification))

                                    <div class="np-item {{ $notification->read_at ? 'np-item--read' : 'np-item--unread' }}"
                                         data-id="{{ $notification->id }}">
                                        <div class="np-item__icon" style="background: linear-gradient(135deg, {{ $meta['c1'] }}, {{ $meta['c2'] }})">
                                            <i class="feather {{ $meta['icon'] }}"></i>
                                        </div>

                                        <div class="np-item__body">
                                            <p class="np-item__title">
                                                @unless($notification->read_at)<span class="np-dot"></span>@endunless
                                                {{ $meta['title'] }}
                                            </p>
                                            <span class="np-item__msg">{{ $notification->data['message'] ?? '' }}</span>
                                        </div>

                                        <div class="np-item__side">
                                        <span class="np-item__time" title="{{ jdate($notification->created_at) }}">
                                            <i class="feather icon-clock"></i>
                                            {{ jdate($notification->created_at)->ago() }}
                                        </span>
                                            <div class="np-item__actions">
                                                @unless($notification->read_at)
                                                    <button type="button" class="np-mark-read" data-id="{{ $notification->id }}"
                                                            title="خوانده‌شده علامت‌گذاری">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                @endunless
                                                @if($notification_link)
                                                    <a href="{{ $notification_link }}" class="np-view-btn">
                                                        <i class="feather icon-eye"></i> مشاهده
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="np-pagination">
                                <div class="np-pagination__meta">
                                    نمایش <b>{{ $notifications->firstItem() }}</b> تا <b>{{ $notifications->lastItem() }}</b> از
                                    <b>{{ number_format($notifications->total()) }}</b> اعلان
                                </div>
                                {{ $notifications->links() }}
                            </div>
                        @else
                            <div class="np-empty">
                                <div class="np-empty__icon"><i class="feather icon-inbox"></i></div>
                                <h5>{{ $filter === 'unread' ? 'همه اعلان‌ها خوانده شده‌اند!' : 'چیزی برای نمایش وجود ندارد!' }}</h5>
                                @if($filter !== 'all')
                                    <a href="{{ request()->url() }}?filter=all" class="np-btn np-btn--ghost">نمایش همه اعلان‌ها</a>
                                @endif
                            </div>
                        @endif

                    </div>
                </section>

            </div>
        </div>
    </div>

    <div id="np-toasts" class="np-toasts"></div>

    <style>

    </style>

@endsection

@push('scripts')
    <script>
        window.NP_ROUTES = {
            read:    '{{ route('admin.notifications.read', ['id' => ':id']) }}',
            readAll: '{{ route('admin.notifications.readAll') }}'
        };
    </script>
    <script src="{{ asset('back/assets/js/pages/notifications/panel.js') }}"></script>
@endpush
