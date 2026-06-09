<?php

namespace App\Notifications\Sms;

use App\Channels\SmsChannel;
use App\Models\Order;
use App\Models\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCancelledSms extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $reason;
    public $refundAmount;

    /**
     * Create a new notification instance.
     *
     * @param Order $order
     * @param string|null $reason
     * @param float|null $refundAmount
     */
    public function __construct(Order $order, $reason = null, $refundAmount = null)
    {
        $this->order = $order;
        $this->reason = $reason;
        $this->refundAmount = $refundAmount;
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

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toSms($notifiable)
    {
        $data = [
            'order_id' => $this->order->id,
            'reason' => $this->reason ?? '',
            'refund_amount' => $this->refundAmount ?? 0
        ];

        return [
            'mobile'  => $notifiable->username ?? $notifiable->mobile,
            'data'    => $data,
            'type'    => Sms::TYPES['USER_ORDER_CANCELLED'], // باید این مقدار را در مدل Sms تعریف کنید
            'user_id' => $notifiable->id
        ];
    }
}
