@if(Auth::check())
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <audio id="alert-sound" src="{{ asset('back/app-assets/sounds/notification.ogg') }}" preload="auto"></audio>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

    @php
        $notifications      = auth()->user()->unreadNotifications;
        $unreadCount        = $notifications->count();
        $notificationsRoute = route('front.notifications.index');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pusherKey     = '{{ config('broadcasting.connections.pusher.key') }}';
            const pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster') }}';
            const userId        = '{{ Auth::id() }}';
            const link          = '{{ $notificationsRoute }}';

            if (!pusherKey || !pusherCluster) {
                console.warn('Pusher credentials are missing.');
                return;
            }

            const pusher  = new Pusher(pusherKey, { cluster: pusherCluster });
            const channel = pusher.subscribe('inbox-user-' + userId);

            channel.bind('send-message-user', function (data) {
                const wrapper   = document.querySelector('.front-dropdown-notification');
                if (!wrapper) return;

                const countBell = wrapper.querySelector('.notifications-count');
                const countHead = wrapper.querySelector('.header-count');
                const list      = wrapper.querySelector('.scrollable-container');
                if (!countBell || !list) return;

                // Update counters
                const currentCount = parseInt(countBell.dataset.count || 0) + 1;
                countBell.dataset.count = currentCount;
                countBell.textContent   = currentCount;
                countBell.style.display = '';
                if (countHead) countHead.textContent = currentCount;

                // Remove the "no notifications" placeholder
                const empty = list.querySelector('.empty-notifications');
                if (empty) empty.remove();

                // Escape user-supplied values to prevent XSS
                const escapeHtml = (str) => {
                    const div = document.createElement('div');
                    div.textContent = str ?? '';
                    return div.innerHTML;
                };

                const title       = escapeHtml(data.message.title);
                const description = escapeHtml(data.message.description);
                const createdAt   = escapeHtml(data.message.created_at);

                const html = `
                    <a class="d-flex justify-content-between notification-item new-notif" href="${link}">
                        <div class="media d-flex align-items-start">
                            <div class="media-left">
                                <div class="notif-icon"><i class="mdi mdi-comment-outline"></i></div>
                            </div>
                            <div class="media-body text-right">
                                <h6 class="primary media-heading">${title}</h6>
                                <small class="notification-text">${description}</small>
                                <time class="media-meta d-block">${createdAt}</time>
                            </div>
                        </div>
                    </a>`;

                list.insertAdjacentHTML('afterbegin', html);

                // Play alert sound (if exists)
                const audio = document.getElementById('alert-sound');
                if (audio) {
                    audio.play().catch(() => {});
                }
            });
        });
    </script>

@endif
