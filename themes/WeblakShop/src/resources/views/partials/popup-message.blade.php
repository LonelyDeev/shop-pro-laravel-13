@if(Auth::check())
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
    <script>
        Pusher.logToConsole = true;
        // Enable pusher logging - don't include this in production
        var pusher = new Pusher('{{env('PUSHER_APP_KEY')}}', {
            cluster: '{{env('PUSHER_APP_CLUSTER')}}'
        });

        var channel = pusher.subscribe('inbox-user-{{Auth::id()}}');
        channel.bind('send-message-user', function(data) {
            var notifications_count=$('.front-dropdown-notification .dropdown-header .notifications-count').attr('data-count');
            notifications_count=parseInt(notifications_count)+parseInt(1)
            $('.front-dropdown-notification .notifications-count').text(notifications_count);
            $('.front-dropdown-notification .dropdown-header .notifications-count').attr('data-count',notifications_count);
            var audio = document.getElementById("alert-sound");
            audio.play();


            $('.front-dropdown-notification .scrollable-container').append(` <a class="d-flex justify-content-between" href="{{ route('front.notifications.index') }}">
                    <div class="media d-flex align-items-start">
                        <div class="media-left"><i class="mdi mdi-comment-outline font-medium-5 primary"></i></div>
                        <div class="media-body">
                            <h6 class="primary media-heading">${data.message.title}</h6><small
                            class="notification-text">${data.message.description}</small>
                        </div><small>
                        <time class="media-meta">${data.message.created_at}</time></small>
                    </div>
                </a>`)

        });
    </script>

@endif
