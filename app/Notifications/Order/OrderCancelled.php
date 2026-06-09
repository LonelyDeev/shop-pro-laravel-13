<?php

namespace App\Notifications\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $reason;
    protected $refundAmount;

    /**
     * Create a new notification instance.
     *
     * @param $order
     * @param string|null $reason
     * @param float|null $refundAmount
     */
    public function __construct($order, $reason = null, $refundAmount = null)
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
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message = "سفارش شماره {$this->order->id} لغو شد.";

        if ($this->reason) {
            $message .= " دلیل: {$this->reason}";
        }

        if ($this->refundAmount && $this->refundAmount > 0) {
            $message .= " مبلغ {$this->refundAmount} تومان به کیف پول شما برگشت داده شد.";
        }

        return [
            'message' => $message,
            'order_id' => $this->order->id,
            'reason' => $this->reason,
            'refund_amount' => $this->refundAmount,
            'status' => 'cancelled',
        ];
    }

    public function databaseType()
    {
        return 'OrderCancelled';
    }

    public function toWebPush($notifiable, $notification)
    {
        $body = "سفارش شماره {$this->order->id} لغو شد";

        if ($this->reason) {
            $body .= " - دلیل: {$this->reason}";
        }

        return (new WebPushMessage)
            ->title('لغو سفارش')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($body)
            ->options(['TTL' => 1000])
            ->data(['link' => route('front.orders.show', ['order' => $this->order])]);
    }
}
