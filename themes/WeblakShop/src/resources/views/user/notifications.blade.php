@extends('front::user.layouts.master')

@push('styles')
    <style>
        /* ===== Page Layout ===== */
        .notifications-page { padding: 2rem 0; }

        .notif-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding: 1.5rem 1.75rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 20px;
            color: #fff;
            box-shadow: 0 12px 32px rgba(99, 102, 241, .25);
        }
        .notif-page-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .notif-page-title-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        .notif-page-title h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
        }
        .notif-page-title small {
            display: block;
            font-size: .8rem;
            opacity: .85;
            margin-top: 2px;
        }
        .notif-page-stats {
            display: flex;
            gap: 1rem;
        }
        .notif-stat {
            background: rgba(255, 255, 255, .15);
            backdrop-filter: blur(10px);
            padding: .6rem 1.1rem;
            border-radius: 12px;
            text-align: center;
            min-width: 80px;
        }
        .notif-stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
            color: #fff;
        }
        .notif-stat-label {
            font-size: .7rem;
            opacity: .85;
            margin-top: 4px;
        }

        /* ===== Cards Grid ===== */
        .notif-list-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            background: #fff;
            border: 1px solid #f0f1f7;
            border-radius: 16px;
            text-decoration: none;
            color: inherit;
            transition: all .25s ease;
            position: relative;
            overflow: hidden;
        }
        .notif-item::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #6366f1, #8b5cf6);
            opacity: 0;
            transition: opacity .25s ease;
        }
        .notif-item.unread::before { opacity: 1; }
        .notif-item:hover {
            border-color: #e0e7ff;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(99, 102, 241, .1);
        }
        .notif-item.read {
            opacity: .7;
            background: #fafafa;
        }
        .notif-item.read .notif-item-icon {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #9ca3af;
        }

        .notif-item-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6366f1;
            font-size: 1.3rem;
            transition: all .25s ease;
        }
        .notif-item.unread .notif-item-icon {
            box-shadow: 0 4px 14px rgba(99, 102, 241, .25);
        }

        .notif-item-body { flex: 1; min-width: 0; }
        .notif-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
        }
        .notif-item-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.5;
        }
        .notif-item-time {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .72rem;
            color: #9ca3af;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .notif-item-text {
            margin: 0;
            font-size: .85rem;
            color: #6b7280;
            line-height: 1.7;
        }

        .notif-item-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            width: 8px;
            height: 8px;
            background: #6366f1;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .15);
        }

        /* ===== Empty State ===== */
        .notif-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 20px;
            border: 2px dashed #e5e7eb;
        }
        .notif-empty-icon-wrap {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #9ca3af;
        }
        .notif-empty-title {
            margin: 0 0 8px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #4b5563;
        }
        .notif-empty-text {
            margin: 0;
            font-size: .875rem;
            color: #9ca3af;
        }

        /* ===== Pagination ===== */
        .notifications-page .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        /* ===== New Item Animation ===== */
        @keyframes notifCardIn {
            from { opacity: 0; transform: translateY(-12px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .notif-item.new-notif {
            animation: notifCardIn .4s ease;
        }

        /* ===== Responsive ===== */
        @media (max-width: 575.98px) {
            .notif-page-header {
                padding: 1.25rem;
                border-radius: 16px;
            }
            .notif-page-title h2 { font-size: 1.15rem; }
            .notif-page-title-icon {
                width: 42px;
                height: 42px;
                font-size: 1.3rem;
            }
            .notif-stat { padding: .5rem .8rem; min-width: 65px; }
            .notif-stat-num { font-size: 1.2rem; }
            .notif-item {
                padding: 14px;
                gap: 12px;
            }
            .notif-item-icon {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }
            .notif-item-title { font-size: .9rem; }
            .notif-item-text { font-size: .8rem; }
            .notif-item-time { display: none; }
        }
    </style>
@endpush

@section('user-content')
    <div id="messages-page" class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
        <div class="notifications-page">

            <div class="notif-page-header">
                <div class="notif-page-title">
                    <div class="notif-page-title-icon">
                        <i class="mdi mdi-bell-ring"></i>
                    </div>
                    <div>
                        <h2>{{ trans('front::messages.profile.all-notifications') }}</h2>
                        <small>آخرین پیام‌ها و اعلان‌های شما در یک نگاه</small>
                    </div>
                </div>

                <div class="notif-page-stats">
                    <div class="notif-stat">
                        <div class="notif-stat-num">{{ $notifications->total() ?? $notifications->count() }}</div>
                        <div class="notif-stat-label">کل اعلان‌ها</div>
                    </div>
                    <div class="notif-stat">
                        <div class="notif-stat-num">
                            {{ $notifications->where('read_at', null)->count() }}
                        </div>
                        <div class="notif-stat-label">خوانده‌نشده</div>
                    </div>
                </div>
            </div>

            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>

            <div class="content-body">
                <section id="statistics-card">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="notif-list-grid">
                                @if($notifications->count())
                                    @foreach($notifications as $notification)
                                        @php
                                            $isRead = ! is_null($notification->read_at);
                                        @endphp

                                        @if($notification->type === 'SendMessage')
                                            <div class="notif-item {{ $isRead ? 'read' : 'unread' }}">
                                                @if(! $isRead)
                                                    <span class="notif-item-badge"></span>
                                                @endif

                                                <div class="notif-item-icon">
                                                    <i class="mdi mdi-comment-text-outline"></i>
                                                </div>

                                                <div class="notif-item-body">
                                                    <div class="notif-item-header">
                                                        <h5 class="notif-item-title">
                                                            {{ $notification->data['title'] }}
                                                        </h5>
                                                        <span class="notif-item-time">
                                                            <i class="mdi mdi-clock-outline"></i>
                                                            {{ jdate($notification->created_at)->ago() }}
                                                        </span>
                                                    </div>
                                                    <p class="notif-item-text">
                                                        {{ $notification->data['message'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="notif-empty-state">
                                        <div class="notif-empty-icon-wrap">
                                            <i class="mdi mdi-bell-off-outline"></i>
                                        </div>
                                        <h5 class="notif-empty-title">صندوق اعلان شما خالی است</h5>
                                        <p class="notif-empty-text">
                                            وقتی اعلان جدیدی دریافت کنید، اینجا نمایش داده می‌شود
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{ $notifications->links('front::components.paginate') }}
            </div>
        </div>
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['persian-datepicker', 'jquery.validate']])
