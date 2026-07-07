@if(!auth()->user()->isAdmin())
    @php
        $notifications      = auth()->user()->unreadNotifications;
        $unreadCount        = $notifications->count();
        $notificationsRoute = route('front.notifications.index');
    @endphp

    <div class="dropdown dropdown-notification front-dropdown-notification nav-item">
        <a class="nav-link nav-link-label"
           href="{{ $notificationsRoute }}"
           data-toggle="dropdown"
           aria-expanded="false"
           aria-label="{{ trans('front::messages.header.new-notification') }}">
            <i class="mdi mdi-bell-outline"></i>
            <span class="badge badge-pill badge-primary badge-up notifications-count"
                  data-count="{{ $unreadCount }}"
                  style="{{ $unreadCount ? '' : 'display:none' }}">
                {{ $unreadCount ?: '' }}
            </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-media">
            <li class="dropdown-menu-header">
                <div class="dropdown-header d-flex justify-content-between align-items-center m-0 p-2">
                    <h4 class="white mb-0">{{ trans('front::messages.header.new-notification') }}</h4>
                    <span class="badge badge-light-primary header-count">{{ $unreadCount }}</span>
                </div>
            </li>

            <li class="scrollable-container media-list">
                @forelse($notifications->take(5) as $notification)
                    @if($notification->type === 'SendMessage')
                        <a class="d-flex justify-content-between notification-item"
                           href="{{ $notificationsRoute }}">
                            <div class="media d-flex align-items-start">
                                <div class="media-left">
                                    <div class="notif-icon">
                                        <i class="mdi mdi-comment-outline"></i>
                                    </div>
                                </div>
                                <div class="media-body text-right">
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

            @if($unreadCount > 0)
                <li class="dropdown-menu-footer">
                    <a class="dropdown-item p-1 text-center" href="{{ $notificationsRoute }}">
                        {{ trans('front::messages.header.show-all-notifications') }}
                    </a>
                </li>
            @endif
        </ul>
    </div>

@endif
