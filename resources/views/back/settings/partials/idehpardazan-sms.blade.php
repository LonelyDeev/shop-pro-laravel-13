@php($siteTitle = option('info_site_title'))
<div class="sms-panel-fields" id="idehpardazan-sms-fields" style="{!! option('sms_panel_provider', 'idehpardazan') != 'idehpardazan' ? 'display: none;' : '' !!}">

    <div class="sms-section-title mt-2">
        <i class="feather icon-server"></i>
        <span>اطلاعات اتصال به پنل ایده پردازان</span>
    </div>
    <div class="row">
        <div class="col-md-4">
            <label>کلید وب سرویس (Api key)</label>
            <div class="input-group mb-75">
                <input type="text" name="IDEHPARDAZAN_PANEL_APIKEY" class="form-control ltr" value="{{ option('IDEHPARDAZAN_PANEL_APIKEY') }}">
            </div>
        </div>
        <div class="col-md-4">
            <label>کد امنیتی (Security code)</label>
            <div class="input-group mb-75">
                <input type="text" name="IDEHPARDAZAN_PANEL_SECRET_KEY" class="form-control ltr" value="{{ option('IDEHPARDAZAN_PANEL_SECRET_KEY') }}">
            </div>
        </div>
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-user-check"></i>
        <span>پترن‌های ثبت‌نام و احراز هویت</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_register_pattern_code_idehpardazan', 'toggleClass' => 'sms_on_user_register', 'required' => true, 'icon' => 'icon-user-plus', 'label' => 'کد متن خوش آمدگویی', 'sample' => "[fullname] عزیز خوش آمدید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_verify_pattern_code_idehpardazan', 'icon' => 'icon-shield', 'label' => 'کد متن ارسال کد تایید', 'sample' => "کد تایید: [code] \r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-shopping-cart"></i>
        <span>پترن‌های پرداخت سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_paid_pattern_code_idehpardazan', 'toggleClass' => 'sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'کد متن پرداخت سفارش', 'sample' => "سفارش جدید با شماره سفارش [order_id] ثبت و پرداخت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_paid_pattern_code_idehpardazan', 'toggleClass' => 'seller_sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'کد متن پرداخت سفارش برای فروشنده', 'sample' => "سفارش شما با شماره سفارش [order_id] با موفقیت ثبت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_paid_pattern_code_idehpardazan', 'toggleClass' => 'user_sms_on_order_paid', 'required' => true, 'icon' => 'icon-check-circle', 'label' => 'کد متن پرداخت سفارش برای کاربر', 'sample' => "سفارش شما با شماره سفارش [order_id] با موفقیت ثبت شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-x-circle"></i>
        <span>پترن‌های لغو سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_cancelled_pattern_code_idehpardazan', 'toggleClass' => 'sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد متن لغو سفارش (ادمین)', 'sample' => "سفارش شماره [order_id] لغو شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_cancelled_pattern_code_idehpardazan', 'toggleClass' => 'seller_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد متن لغو سفارش (فروشنده)', 'sample' => "سفارش شماره [order_id] لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_cancelled_pattern_code_idehpardazan', 'toggleClass' => 'user_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد متن لغو سفارش (کاربر)', 'sample' => "سفارش شماره [order_id] به دلیل [reason] لغو شد. مبلغ [refund_amount] تومان به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-credit-card"></i>
        <span>پترن‌های کیف پول</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_refund_pattern_code_idehpardazan', 'toggleClass' => 'wallet_refund_sms', 'icon' => 'icon-rotate-ccw', 'label' => 'کد متن برگشت وجه به کیف پول', 'sample' => "مبلغ [amount] تومان بابت لغو سفارش [order_id] به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_increase_pattern_code_idehpardazan', 'toggleClass' => 'wallet_increase_sms', 'required' => true, 'icon' => 'icon-arrow-up-circle', 'label' => 'کد متن افزایش موجودی کیف پول', 'sample' => "مبلغ [amount] تومان به اعتبار کیف پول شما اضافه شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_decrease_pattern_code_idehpardazan', 'toggleClass' => 'wallet_decrease_sms', 'required' => true, 'icon' => 'icon-arrow-down-circle', 'label' => 'کد متن کاهش موجودی کیف پول', 'sample' => "مبلغ [amount] تومان از اعتبار کیف پول شما کسر شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-gift"></i>
        <span>پترن مناسبتی</span>
    </div>
    @include('back.settings.partials.sms-birthday-card', ['name' => 'happy_birthday_pattern_code_idehpardazan', 'sample' => "[fullname] عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید و با تکیه بر تجربه های سال های گذشته سال های آتی زندگی را به بهترین شکل ممکن بگذرانید تولدتان مبارک\r\n {$siteTitle}"])

</div>
