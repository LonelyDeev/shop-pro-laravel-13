<?php

namespace App\Contracts;

interface SmsNotificationContract
{
    public function verifyCode();
    public function sellerCreated();
    public function userCreated();
    public function orderPaid();
    public function sellerOrderPaid();
    public function userOrderPaid();
    public function orderCancelled();
    public function sellerOrderCancelled();
    public function userOrderCancelled();
    public function walletRefund();
    public function walletAmountDecreased();
    public function walletAmountIncreased();
}
