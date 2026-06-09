<?php

namespace App\Notifications\User;

use App\Channels\SmsChannel;
use App\Models\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [SmsChannel::class];
    }

    public function toSms($notifiable)
    {
        $fullname=$notifiable->fullname;
        if ($notifiable->fullname==null or $notifiable->fullname==" "){
            $fullname="کاربر";
        }
        return [
            'mobile'       => $notifiable->username,
            'data'         => [
                'fullname' => $fullname,
                'username' => $notifiable->username
            ],
            'type'         => Sms::TYPES['USER_CREATED'],
            'user_id'      => $notifiable->id
        ];
    }

    public function databaseType()
    {
        return 'UserCreated';
    }
}
