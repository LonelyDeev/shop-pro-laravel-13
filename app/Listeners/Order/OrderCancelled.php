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

    public function __construct($order, $reason = null, $refundAmount = null)
    {
        $this->order = $order;
        $this->reason = $reason;
        $this->refundAmount = $refundAmount;
    }

    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable)
    {
        $message = "سفارش شماره {$this->order->id} لغو شد.";

        if ($this->reason) {
            $message .= " دلیل: {$this->reason}";
        }

        if ($this->refundAmount && $this->refundAmount > 0) {
            $message .= " مبلغ {$this->refundAmount} تومان برگشت داده شد.";
        }

        return [
            'message' => $message,
            'order_id' => $this->order->id,
            'reason' => $this->reason,
            'refund_amount' => $this->refundAmount,
            'type' => 'order_cancelled'
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

        $link = $notifiable instanceof \App\Models\Admin
            ? route('admin.orders.show', ['order' => $this->order])
            : route('front.orders.show', ['order' => $this->order]);

        return (new WebPushMessage)
            ->title('لغو سفارش')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($body)
            ->options(['TTL' => 1000])
            ->data(['link' => $link]);
    }
}
