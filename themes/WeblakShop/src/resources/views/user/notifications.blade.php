@extends('front::user.layouts.master')

@push('styles')
    <style>
        /* ===== Notifications Page ===== */
        .notifications-page { padding: 1.5rem 0; }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .notifications-title h2 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
        }

        /* ===== Timeline ===== */
        .notification-timeline {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }
        .notification-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            right: 20px;
            width: 2px;
            background: linear-gradient(to bottom, #4f7df9, rgba(79, 125, 249, .1));
        }
        .notification-timeline li {
            position: relative;
            padding: 1rem 3.5rem 1rem 1rem;
            border-radius: 12px;
            transition: background .25s ease, transform .25s ease, box-shadow .25s ease;
            margin-bottom: .5rem;
        }
        .notification-timeline li:hover {
            background: #f8f9ff;
            transform: translateX(-4px);
            box-shadow: 0 4px 14px rgba(79, 125, 249, .08);
        }
        .notification-timeline li.unread {
            background: linear-gradient(90deg, #f0f6ff 0%, transparent 100%);
        }
        .notification-timeline li.read {
            opacity: .65;
        }
        .notification-timeline .timeline-icon {
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            z-index: 1;
            box-shadow: 0 4px 12px rgba(79, 125, 249, .35);
        }
        .notification-timeline .timeline-info p {
            margin: 0 0 .25rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: .95rem;
        }
        .notification-timeline .timeline-info span {
            color: #6c757d;
            font-size: .875rem;
            line-height: 1.6;
            display: block;
        }
        .notification-timeline .timeline-time {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-top: .5rem;
            color: #adb5bd;
            font-size: .75rem;
        }

        /* ===== Empty State ===== */
        .notification-empty {
            text-align: center;
            padding: 3.5rem 1rem;
            color: #adb5bd;
        }
        .notification-empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
            display: block;
        }
        .notification-empty p {
            margin: 0;
            font-size: 1rem;
        }

        /* ===== New Item Animation ===== */
        @keyframes newNotifPulse {
            0%   { box-shadow: 0 0 0 0 rgba(79, 125, 249, .45); }
            70%  { box-shadow: 0 0 0 14px rgba(79, 125, 249, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 125, 249, 0); }
        }
        .notification-item.new-notif {
            animation: newNotifPulse 1.5s ease-out;
        }

        /* ===== Responsive ===== */
        @media (max-width: 575.98px) {
            .notification-timeline li { padding: 1rem 3rem 1rem .5rem; }
            .notification-timeline .timeline-icon { width: 36px; height: 36px; }
        }
    </style>
@endpush

@section('user-content')
    <div id="messages-page" class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
        <div class="notifications-page">
            <div class="section-title text-sm-title title-wide mb-1 no-after-title-wide dt-sl mb-2 px-res-1">
                <h2>{{ trans('front::messages.profile.all-notifications') }}</h2>
            </div>

            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>

            <div class="content-body">
                <section id="statistics-card">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        @if($notifications->count())
                                            <ul class="notification-timeline">
                                                @foreach($notifications as $notification)
                                                    @php
                                                        $isRead = ! is_null($notification->read_at);
                                                    @endphp

                                                    @if($notification->type === 'SendMessage')
                                                        <li class="{{ $isRead ? 'read' : 'unread' }}">
                                                            <div class="timeline-icon bg-primary">
                                                                <i class="mdi mdi-comment-outline font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p>{{ $notification->data['title'] }}</p>
                                                                <span>{{ $notification->data['message'] }}</span>
                                                                <small class="timeline-time">
                                                                    <i class="mdi mdi-clock-outline"></i>
                                                                    {{ jdate($notification->created_at)->ago() }}
                                                                </small>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="notification-empty">
                                                <i class="mdi mdi-bell-off-outline"></i>
                                                <p>هیچ اعلانی برای نمایش وجود ندارد</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
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
