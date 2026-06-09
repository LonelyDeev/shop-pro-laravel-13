<?php

namespace App\Notifications\Seller;

use App\Channels\SmsChannel;
use App\Models\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SellerCreated extends Notification implements ShouldQueue
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

        return [
            'mobile'       => $notifiable->mobile,
            'data'         => [
                'fullname' => $notifiable->fullname,
                'username' => $notifiable->username
            ],
            'type'         => Sms::TYPES['SELLER_CREATED'],
            'seller_id'    => $notifiable->id
        ];
    }

    public function databaseType()
    {
        return 'SellerCreated';
    }
}
