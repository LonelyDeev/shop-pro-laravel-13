<?php

namespace App\Listeners\User;

use App\Events\WalletAmountIncreased;
use App\Models\Discount;
use App\Models\Referral;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateReferralDiscounts
{
    public function handle(Registered $event)
    {
        if (option('user_referrals_enable', 0) == 0) return;

        $user = $event->user;
        $owner = $user->referral;

        if ($owner) {
            $discountType = option('user_referrals_gift_discount_type', 'percent');
            $fullName = $user->full_name ?? $user->username;

            // حالت کد تخفیف
            if (option('user_referrals_gift_type') == "discount_code") {
                // کد تخفیف برای معرف (owner)
                $owner_code = random_code();
                $owner_discount = Discount::create([
                    'title' => "تخفیف کد معرف",
                    'code' => $owner_code,
                    'type' => $discountType,
                    'amount' => option('owner_referrals_amount', 0),
                    'description' => "کد تخفیف برای معرفی کاربر $fullName",
                    'quantity' => 1,
                    'least_price' => option('minimum_amount_gift'),
                    'least_products_count' => option('minimum_product_gift'),
                    'start_date' => now(),
                    'end_date' => now()->addDays(90)
                ]);
                $owner_discount->users()->attach([$owner->id]);

                // کد تخفیف برای کاربر جدید
                $user_code = random_code();
                $user_discount = Discount::create([
                    'title' => "تخفیف کد معرف",
                    'code' => $user_code,
                    'type' => $discountType,
                    'amount' => option('user_referrals_amount', 0),
                    'description' => "کد تخفیف برای ثبت کد معرف",
                    'quantity' => 1,
                    'least_price' => option('minimum_amount_gift'),
                    'least_products_count' => option('minimum_product_gift'),
                    'start_date' => now(),
                    'end_date' => now()->addDays(90)
                ]);
                $user_discount->users()->attach([$user->id]);

                Referral::create([
                    'owner_discount_id' => $owner_discount->id,
                    'user_discount_id' => $user_discount->id,
                    'owner_id' => $owner->id,
                    'user_id' => $user->id
                ]);
            }

            // حالت کیف پول
            if (option('user_referrals_gift_type') == "wallet") {
                $owner_wallet_history = null;
                $user_wallet_history = null;

                $gift_credit_owner = option('owner_referrals_amount', 0);
                $gift_credit_user = option('user_referrals_amount', 0);

                DB::transaction(function () use ($owner, $user, $fullName, $gift_credit_owner, $gift_credit_user, &$owner_wallet_history, &$user_wallet_history) {

                    // ثبت برای معرف (owner) - کیف پول owner
                    if ($gift_credit_owner > 0) {
                        $owner_wallet = $owner->getWallet(); // کیف پول owner
                        $owner_wallet_history = $owner_wallet->histories()->create([
                            'type' => 'deposit',
                            'amount' => $gift_credit_owner,
                            'status' => 'success',
                            'description' => 'اعتبار هدیه برای معرفی کاربر: ' . $fullName
                        ]);

                        $owner_wallet->update([
                            'balance' => $owner_wallet->balance + $gift_credit_owner
                        ]);

                        event(new WalletAmountIncreased($owner_wallet));
                    }

                    // ثبت برای معرفی‌شونده (user) - کیف پول user
                    if ($gift_credit_user > 0) {
                        $user_wallet = $user->getWallet(); // کیف پول user
                        $user_wallet_history = $user_wallet->histories()->create([
                            'type' => 'deposit',
                            'amount' => $gift_credit_user,
                            'status' => 'success',
                            'description' => 'اعتبار هدیه برای استفاده از کد معرف'
                        ]);

                        $user_wallet->update([
                            'balance' => $user_wallet->balance + $gift_credit_user
                        ]);

                        event(new WalletAmountIncreased($user_wallet));
                    }
                });

                // ایجاد رکورد referral
                if ($owner_wallet_history && $user_wallet_history) {
                    Referral::firstOrCreate(
                        [
                            'owner_id' => $owner->id,
                            'user_id' => $user->id
                        ],
                        [
                            'owner_wallet_history_id' => $owner_wallet_history->id,
                            'user_wallet_history_id' => $user_wallet_history->id,
                        ]
                    );
                }
            }
        }
    }
}
