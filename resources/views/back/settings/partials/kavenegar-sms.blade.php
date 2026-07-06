@php($siteTitle = option('info_site_title'))
<div class="sms-panel-fields" id="kavenegar-sms-fields" style="{!! option('sms_panel_provider', 'kavenegar') != 'kavenegar' ? 'display: none;' : '' !!}">

    <div class="sms-section-title mt-2">
        <i class="feather icon-server"></i>
        <span>اطلاعات اتصال به پنل کاوه نگار</span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <label>کلید وب سرویس</label>
            <div class="input-group mb-75">
                <input type="text" name="KAVENEGAR_PANEL_APIKEY" class="form-control ltr" value="{{ option('KAVENEGAR_PANEL_APIKEY') }}">
            </div>
        </div>
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-user-check"></i>
        <span>پترن‌های ثبت‌نام و احراز هویت</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_register_pattern_code_kavenegar', 'toggleClass' => 'sms_on_seller_register', 'required' => true, 'icon' => 'icon-briefcase', 'label' => 'الگوی خوش آمدگویی فروشنده', 'sample' => "فروشنده عزیز با عنوان فروشگاه %token2، خوش آمدید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_register_pattern_code_kavenegar', 'toggleClass' => 'sms_on_user_register', 'required' => true, 'icon' => 'icon-user-plus', 'label' => 'الگوی خوش آمدگویی کاربر', 'sample' => "%token2 عزیز خوش آمدید با شماره موبایل %token.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_verify_pattern_code_kavenegar', 'icon' => 'icon-shield', 'label' => 'الگوی ارسال کد تایید', 'sample' => "کد تایید: %token \r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-shopping-cart"></i>
        <span>پترن‌های پرداخت سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_paid_pattern_code_kavenegar', 'toggleClass' => 'sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'الگوی پرداخت سفارش', 'sample' => "سفارش جدید با شماره سفارش %token ثبت و پرداخت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_paid_pattern_code_kavenegar', 'toggleClass' => 'seller_sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'الگوی پرداخت سفارش برای فروشنده', 'sample' => "سفارش شما با شماره سفارش %token با موفقیت ثبت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_paid_pattern_code_kavenegar', 'toggleClass' => 'user_sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'الگوی پرداخت سفارش برای کاربر', 'sample' => "سفارش شما با شماره سفارش %token با موفقیت ثبت شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-x-circle"></i>
        <span>پترن‌های لغو سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_cancelled_pattern_code_kavenegar', 'toggleClass' => 'sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'الگوی لغو سفارش (ادمین)', 'sample' => "سفارش شماره %token لغو شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_cancelled_pattern_code_kavenegar', 'toggleClass' => 'seller_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'الگوی لغو سفارش (فروشنده)', 'sample' => "سفارش شماره %token لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_cancelled_pattern_code_kavenegar', 'toggleClass' => 'user_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'الگوی لغو سفارش (کاربر)', 'sample' => "سفارش شماره %token به دلیل %token2 لغو شد. مبلغ %token3 تومان به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-credit-card"></i>
        <span>پترن‌های کیف پول</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_refund_pattern_code_kavenegar', 'toggleClass' => 'wallet_refund_sms', 'icon' => 'icon-rotate-ccw', 'label' => 'الگوی برگشت وجه به کیف پول', 'sample' => "مبلغ %token تومان بابت لغو سفارش %token2 به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_increase_pattern_code_kavenegar', 'toggleClass' => 'wallet_increase_sms', 'required' => true, 'icon' => 'icon-arrow-up-circle', 'label' => 'کد متن افزایش موجودی کیف پول', 'sample' => "مبلغ %token تومان به اعتبار کیف پول شما اضافه شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_decrease_pattern_code_kavenegar', 'toggleClass' => 'wallet_decrease_sms', 'required' => true, 'icon' => 'icon-arrow-down-circle', 'label' => 'کد متن کاهش موجودی کیف پول', 'sample' => "مبلغ %token تومان از اعتبار کیف پول شما کسر شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-gift"></i>
        <span>پترن مناسبتی</span>
    </div>
    @include('back.settings.partials.sms-birthday-card', ['name' => 'happy_birthday_pattern_code_kavenegar', 'sample' => "%token عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید و با تکیه بر تجربه های سال های گذشته سال های آتی زندگی را به بهترین شکل ممکن بگذرانید تولدتان مبارک\r\n {$siteTitle}"])

</div>
