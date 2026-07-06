@php($siteTitle = option('info_site_title'))
<div class="sms-panel-fields" id="farazsms-sms-fields" style="{!! option('sms_panel_provider', 'farazsms') != 'farazsms' ? 'display: none;' : '' !!}">

    <div class="sms-section-title mt-2">
        <i class="feather icon-server"></i>
        <span>اطلاعات اتصال به پنل فراز اس ام اس</span>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label>کلید API (Api-Key)</label>
            <div class="input-group mb-75">
                <input type="text" name="FARAZSMS_PANEL_API_KEY" class="form-control ltr" value="{{ option('FARAZSMS_PANEL_API_KEY') }}">
            </div>
            <small class="text-muted">کلید API دریافتی از پنل فراز اس ام اس</small>
        </div>
        <div class="col-md-6">
            <label>شماره خط ارسالی (line_number)</label>
            <div class="input-group mb-75">
                <input type="text" name="FARAZSMS_PANEL_FROM" class="form-control ltr" value="{{ option('FARAZSMS_PANEL_FROM') }}">
            </div>
            <small class="text-muted">شماره خطی که پیامک از آن ارسال می‌شود</small>
        </div>
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-user-check"></i>
        <span>پترن‌های ثبت‌نام و احراز هویت</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_register_pattern_code_farazsms', 'toggleClass' => 'sms_on_seller_register', 'icon' => 'icon-briefcase', 'label' => 'کد پترن خوش آمدگویی فروشنده', 'sample' => "%fullname% فروشنده عزیز خوش آمدید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_register_pattern_code_farazsms', 'toggleClass' => 'sms_on_user_register', 'icon' => 'icon-user-plus', 'label' => 'کد پترن خوش آمدگویی کاربر', 'sample' => "%fullname% عزیز خوش آمدید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_verify_pattern_code_farazsms', 'icon' => 'icon-shield', 'label' => 'کد پترن ارسال کد تایید', 'sample' => "کد تایید: %code% \r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-shopping-cart"></i>
        <span>پترن‌های پرداخت سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_paid_pattern_code_farazsms', 'toggleClass' => 'sms_on_order_paid', 'icon' => 'icon-check-circle', 'label' => 'کد پترن پرداخت سفارش (ادمین)', 'sample' => "سفارش جدید با شماره سفارش %order_id% ثبت و پرداخت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_paid_pattern_code_farazsms', 'toggleClass' => 'seller_sms_on_order_paid', 'icon' => 'icon-check-circle', 'label' => 'کد پترن پرداخت سفارش (فروشنده)', 'sample' => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_paid_pattern_code_farazsms', 'toggleClass' => 'user_sms_on_order_paid', 'icon' => 'icon-check-circle', 'label' => 'کد پترن پرداخت سفارش (کاربر)', 'sample' => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-x-circle"></i>
        <span>پترن‌های لغو سفارش</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'order_cancelled_pattern_code_farazsms', 'toggleClass' => 'sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد پترن لغو سفارش (ادمین)', 'sample' => "سفارش شماره %order_id% لغو شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'seller_order_cancelled_pattern_code_farazsms', 'toggleClass' => 'seller_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد پترن لغو سفارش (فروشنده)', 'sample' => "سفارش شماره %order_id% لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'user_order_cancelled_pattern_code_farazsms', 'toggleClass' => 'user_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'label' => 'کد پترن لغو سفارش (کاربر)', 'sample' => "سفارش شماره %order_id% به دلیل %reason% لغو شد. مبلغ %refund_amount% تومان به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-credit-card"></i>
        <span>پترن‌های کیف پول</span>
    </div>
    <div class="row">
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_refund_pattern_code_farazsms', 'toggleClass' => 'wallet_refund_sms', 'icon' => 'icon-rotate-ccw', 'label' => 'کد پترن برگشت وجه به کیف پول', 'sample' => "مبلغ %amount% تومان بابت لغو سفارش %order_id% به کیف پول شما برگشت داده شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_increase_pattern_code_farazsms', 'toggleClass' => 'wallet_increase_sms', 'icon' => 'icon-arrow-up-circle', 'label' => 'کد پترن افزایش موجودی کیف پول', 'sample' => "مبلغ %amount% تومان به اعتبار کیف پول شما اضافه شد.\r\n {$siteTitle}"])
        @include('back.settings.partials.sms-pattern-field', ['name' => 'wallet_decrease_pattern_code_farazsms', 'toggleClass' => 'wallet_decrease_sms', 'icon' => 'icon-arrow-down-circle', 'label' => 'کد پترن کاهش موجودی کیف پول', 'sample' => "مبلغ %amount% تومان از اعتبار کیف پول شما کسر شد.\r\n {$siteTitle}"])
    </div>

    <div class="sms-section-title mt-2">
        <i class="feather icon-gift"></i>
        <span>پترن مناسبتی</span>
    </div>
    @include('back.settings.partials.sms-birthday-card', ['name' => 'happy_birthday_pattern_code_farazsms', 'sample' => "%fullname% عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید و با تکیه بر تجربه های سال های گذشته سال های آتی زندگی را به بهترین شکل ممکن بگذرانید تولدتان مبارک\r\n {$siteTitle}"])

</div>
