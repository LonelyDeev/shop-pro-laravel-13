@if(!auth()->user()->isAdmin())
    @php
        $notifications = auth()->user()->unreadNotifications;
    @endphp
    <div class="dropdown dropdown-notification front-dropdown-notification nav-item show">
        <a class="nav-link nav-link-label" href="#" data-toggle="dropdown" aria-expanded="true">
            <i class="mdi mdi-bell-outline"></i>
            <span class="badge badge-pill badge-primary badge-up notifications-count"
                  data-count="{{ $notifications->count() }}"
                  style="{{ $notifications->count() ? '' : 'display:none' }}">
            {{ $notifications->count() ?: '' }}
        </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-media">
            <li class="dropdown-menu-header">
                <div class="dropdown-header d-flex justify-content-between align-items-center m-0 p-2">
                    <h4 class="white mb-0">{{ trans('front::messages.header.new-notification') }}</h4>
                    <span class="badge badge-light-primary header-count">{{ $notifications->count() }}</span>
                </div>
            </li>

            <li class="scrollable-container media-list">
                @forelse ($notifications as $notification)
                    @if($notification->type == 'SendMessage')
                        <a class="d-flex justify-content-between notification-item" href="{{ route('front.user.notifications.index') }}">
                            <div class="media d-flex align-items-start">
                                <div class="media-left">
                                    <div class="notif-icon">
                                        <i class="mdi mdi-comment-outline"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="primary media-heading">{{ $notification->data['title'] }}</h6>
                                    <small class="notification-text">{{ $notification->data['message'] }}</small>
                                    <time class="media-meta d-block">{{ jdate($notification->created_at)->ago() }}</time>
                                </div>
                            </div>
                        </a>
                    @endif
                @empty
                    <div class="empty-notifications text-center py-4">
                        <i class="mdi mdi-bell-off-outline font-medium-5 d-block mb-1"></i>
                        <span>{{ trans('front::messages.header.no-notification') ?? 'اعلانی وجود ندارد' }}</span>
                    </div>
                @endforelse
            </li>

            <li class="dropdown-menu-footer">
                <a class="dropdown-item p-1 text-center" href="{{ route('front.notifications.index') }}">
                    {{ trans('front::messages.header.show-all-notifications') }}
                </a>
            </li>
        </ul>
    </div>

    <style>
        .front-dropdown-notification .dropdown-menu-media {
            width: 340px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,.15);
            overflow: hidden;
            padding: 0;
        }
        .front-dropdown-notification .dropdown-header {
            background: linear-gradient(135deg, #7367f0, #9e95f5);
        }
        .front-dropdown-notification .scrollable-container {
            max-height: 320px;
            overflow-y: auto;
        }
        .front-dropdown-notification .scrollable-container::-webkit-scrollbar { width: 5px; }
        .front-dropdown-notification .scrollable-container::-webkit-scrollbar-thumb {
            background: #d5d5d5;
            border-radius: 10px;
        }
        .front-dropdown-notification .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f1f1;
            transition: background .2s ease;
        }
        .front-dropdown-notification .notification-item:hover {
            background: #f8f8ff;
        }
        .front-dropdown-notification .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ede9ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
        .front-dropdown-notification .notif-icon i { color: #7367f0; }
        .front-dropdown-notification .media-heading { margin-bottom: 2px; font-size: 14px; }
        .front-dropdown-notification .notification-text {
            display: block;
            color: #6e6b7b;
            font-size: 12.5px;
        }
        .front-dropdown-notification .media-meta {
            font-size: 11px;
            color: #b9b9c3;
            margin-top: 3px;
        }
        .front-dropdown-notification .empty-notifications {
            color: #b9b9c3;
        }
        .front-dropdown-notification .empty-notifications i { font-size: 32px; }
        .front-dropdown-notification .notification-item.new-notif {
            animation: highlightNew 1.2s ease;
        }
        @keyframes highlightNew {
            0% { background: #ede9ff; }
            100% { background: transparent; }
        }
    </style>
@endif
