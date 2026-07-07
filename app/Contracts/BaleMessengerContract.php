<?php

namespace App\Contracts;

interface BaleMessengerContract
{
    /**
     * ارسال پیام به کاربر
     */
    public function send();

    /**
     * ارسال پیام به ادمین
     */
    public function sendToAdmin();

    /**
     * کد تایید
     */
    public function verifyCode();

    /**
     * ایجاد فروشنده
     */
    public function sellerCreated();

    /**
     * ایجاد کاربر
     */
    public function userCreated();

    /**
     * پرداخت سفارش (اطلاع‌رسانی به ادمین)
     */
    public function orderPaid();

    /**
     * پرداخت سفارش (اطلاع‌رسانی به فروشنده)
     */
    public function sellerOrderPaid();

    /**
     * پرداخت سفارش (اطلاع‌رسانی به کاربر)
     */
    public function userOrderPaid();

    /**
     * لغو سفارش (اطلاع‌رسانی به ادمین)
     */
    public function orderCancelled();

    /**
     * لغو سفارش (اطلاع‌رسانی به فروشنده)
     */
    public function sellerOrderCancelled();

    /**
     * لغو سفارش (اطلاع‌رسانی به کاربر)
     */
    public function userOrderCancelled();

    /**
     * برگشت وجه به کیف پول
     */
    public function walletRefund();

    /**
     * کاهش موجودی کیف پول
     */
    public function walletAmountDecreased();

    /**
     * افزایش موجودی کیف پول
     */
    public function walletAmountIncreased();

    /**
     * پیام تبریک تولد
     */
    public function happyBirthday();

    /**
     * ارسال پیام گروهی به کاربران
     */
    public function sendMessageUsers();

    /**
     * پیام ساده
     */
    public function simpleMessage();
}
