<?php

namespace App\Listeners\Seller;

use App\Models\Admin;
use App\Models\User;
use App\Events\SellerCreated;
use App\Notifications\Seller\SellerRegistered;
use Illuminate\Auth\Events\Registered as RegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class Registered
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(SellerCreated $event)
    {
        // send notification for admins
        $admins = Admin::whereIn('level', ['admin', 'creator'])->get();
        Notification::send($admins, new SellerRegistered($event->seller));

        if (option('sms_on_seller_register', 'off') == 'on') {
            // send sms notification to user
            Notification::send($event->seller, new SellerCreated($event->seller));
        }
    }
}
