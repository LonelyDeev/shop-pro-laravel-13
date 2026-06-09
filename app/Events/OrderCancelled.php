<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $reason;
    public $refundAmount;

    /**
     * Create a new event instance.
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

    public function broadcastOn()
    {
        return new Channel('orders');
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'status' => 'cancelled',
            'reason' => $this->reason,
            'refund_amount' => $this->refundAmount
        ];
    }
}
