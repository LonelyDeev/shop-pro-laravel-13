<?php

namespace App\Channels;

use App\Services\Sms\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toSms($notifiable);

        if (isset($data['user_id'])){
            $user_id=$data['user_id'];
        }else{
            $user_id=null;
        }

        if (isset($data['seller_id'])){
            $seller_id=$data['seller_id'];
        }else{
            $seller_id=null;
        }

        $smsService = new SmsService($data['mobile'], $data['data'], $data['type'], $user_id,$seller_id);
        $smsService->sendSms();
    }
}
