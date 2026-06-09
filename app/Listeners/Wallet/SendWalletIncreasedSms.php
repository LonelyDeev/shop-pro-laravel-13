<?php

namespace App\Listeners\Wallet;

use App\Events\WalletAmountIncreased;
use App\Notifications\Wallet\WalletAmountDecreasedSms;
use App\Notifications\Wallet\WalletAmountIncreasedSms;
use App\Notifications\Wallet\WalletSellerAmountDecreasedSms;
use App\Notifications\Wallet\WalletSellerAmountIncreasedSms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendWalletIncreasedSms
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\WalletAmountIncreased  $event
     * @return void
     */
    public function handle(WalletAmountIncreased $event)
    {
        $amount = $event->wallet
            ->histories()
            ->where('type', 'deposit')
            ->latest()
            ->first();

        if (option('wallet_increase_sms', 'off') == 'on') {
            if ($event->wallet->seller_id) {
                Notification::send($event->wallet->seller, new WalletSellerAmountIncreasedSms($event->wallet, $amount->amount));
            } elseif ($event->wallet->user_id) {
                Notification::send($event->wallet->user, new WalletAmountIncreasedSms($event->wallet, $amount->amount));
            }
        }
    }
}
