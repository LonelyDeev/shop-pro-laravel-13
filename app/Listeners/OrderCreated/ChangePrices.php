<?php

namespace App\Listeners\OrderCreated;

use App\Events\OrderCreated;

class ChangePrices
{
    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        if (!$event->order->hasPhysicalProduct()) {
            return;
        }

        foreach ($event->order->items as $item) {
            $price   = $item->get_price;
            $product = $price->product;

            if ($price) {
                $price->createChange($price->price, $price->discount);

                if ($product->isPhysical()) {
                    $price->update([
                        'stock' => $price->stock - $item->quantity
                    ]);
                }
            }
        }
    }
}
