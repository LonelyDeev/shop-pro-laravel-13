<?php

namespace App\Listeners\Wallet;

use App\Events\WalletAmountDecreased;
use App\Notifications\Wallet\WalletAmountDecreasedSms;
use App\Notifications\Wallet\WalletSellerAmountDecreasedSms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendWalletDecreasedSms
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\WalletAmountDecreased  $event
     * @return void
     */
    public function handle(WalletAmountDecreased $event)
    {

        $amount = $event->wallet
            ->histories()
            ->where('type', 'withdraw')
            ->latest()
            ->first();
        if (option('wallet_decrease_sms', 'off') == 'on') {
            if ($event->wallet->seller_id) {
                Notification::send($event->wallet->seller, new WalletSellerAmountDecreasedSms($event->wallet, $amount->amount));
            } elseif ($event->wallet->user_id) {
                Notification::send($event->wallet->user, new WalletAmountDecreasedSms($event->wallet, $amount->amount));
            }
        }
    }
}
