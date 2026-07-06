<?php

namespace App\Notifications\Message;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SendMessage extends  Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $tries = 3;
    public function __construct(Message $message)
    {
        $this->message=$message;
    }

    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }


    public function toArray($notifiable)
    {
        return [
            'message'       => $this->message->description,
            'title'       => $this->message->title,

        ];
    }

    public function databaseType()
    {
        return 'SendMessage';
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('تماس جدید در فروشگاه')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body('g')
            ->options(['TTL' => 1000])
            ->data(['link' => '']);
    }
}
