@if(Auth::check())
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <audio id="alert-sound" src="{{ asset('back/app-assets/sounds/notification.ogg') }}" preload="auto"></audio>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
    <script>
        // Pusher.logToConsole = true; // فقط برای دیباگ، در production خاموش باشه

        var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}'
        });

        var channel = pusher.subscribe('inbox-user-{{ Auth::id() }}');

        channel.bind('send-message-user', function (data) {
            var $wrapper   = $('.front-dropdown-notification');
            var $countBell = $wrapper.find('.notifications-count');
            var $countHead = $wrapper.find('.header-count');
            var $list      = $wrapper.find('.scrollable-container');

            // آپدیت شمارنده‌ها
            var currentCount = parseInt($countBell.attr('data-count') || 0) + 1;
            $countBell.attr('data-count', currentCount).text(currentCount).show();
            $countHead.text(currentCount);

            // حذف پیام "اعلانی وجود ندارد" در صورت نمایش
            $list.find('.empty-notifications').remove();

            // escape کردن مقادیر ورودی برای جلوگیری از XSS
            var title       = $('<div>').text(data.message.title).html();
            var description = $('<div>').text(data.message.description).html();
            var createdAt   = $('<div>').text(data.message.created_at).html();
            var link        = "{{ route('front.notifications.index') }}";

            var html = `
            <a class="d-flex justify-content-between notification-item new-notif" href="${link}">
                <div class="media d-flex align-items-start">
                    <div class="media-left">
                        <div class="notif-icon"><i class="mdi mdi-comment-outline"></i></div>
                    </div>
                    <div class="media-body">
                        <h6 class="primary media-heading">${title}</h6>
                        <small class="notification-text">${description}</small>
                        <time class="media-meta d-block">${createdAt}</time>
                    </div>
                </div>
            </a>`;

            $list.prepend(html);

            // پخش صدا در صورت وجود المنت
            var audio = document.getElementById('alert-sound');
            if (audio) {
                audio.play().catch(function () {
                    // در بعضی مرورگرها بدون تعامل کاربر autoplay مسدود میشه
                });
            }
        });
    </script>

@endif
