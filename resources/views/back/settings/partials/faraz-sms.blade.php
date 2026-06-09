<div class="sms-panel-fields" id="farazsms-sms-fields" style="{!! option('sms_panel_provider', 'farazsms') != 'farazsms' ? 'display: none;' : '' !!}">
    <h5 class="my-2">اطلاعات پنل فراز اس ام اس</h5>
    <hr>
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

    <hr>

    <div class="row">
        <!-- قالب خوش آمدگویی فروشنده -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن خوش آمدگویی فروشنده</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_register_pattern_code_farazsms" class="form-control ltr sms_on_seller_register" value="{{ option('seller_register_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_seller_register" rows="4">%fullname% فروشنده عزیز خوش آمدید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب خوش آمدگویی کاربر -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن خوش آمدگویی کاربر</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_register_pattern_code_farazsms" class="form-control ltr sms_on_user_register" value="{{ option('user_register_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_user_register" rows="4">%fullname% عزیز خوش آمدید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب ارسال کد تایید -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن ارسال کد تایید</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_verify_pattern_code_farazsms" class="form-control ltr" value="{{ option('user_verify_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control" rows="4">کد تایید: %code% &#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب پرداخت سفارش -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن پرداخت سفارش (ادمین)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_paid_pattern_code_farazsms" class="form-control ltr sms_on_order_paid" value="{{ option('order_paid_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_paid" rows="4">سفارش جدید با شماره سفارش %order_id% ثبت و پرداخت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب پرداخت سفارش برای فروشنده -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن پرداخت سفارش (فروشنده)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_paid_pattern_code_farazsms" class="form-control ltr seller_sms_on_order_paid" value="{{ option('seller_order_paid_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب پرداخت سفارش برای کاربر -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن پرداخت سفارش (کاربر)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_paid_pattern_code_farazsms" class="form-control ltr user_sms_on_order_paid" value="{{ option('user_order_paid_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========== بخش جدید: قالب‌های لغو سفارش برای فراز اس ام اس ========== -->
        <div class="col-md-12">
            <hr>
            <h5 class="my-2">قالب‌های پیامکی لغو سفارش</h5>
            <hr>
        </div>

        <!-- قالب لغو سفارش برای ادمین -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن لغو سفارش (ادمین)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_cancelled_pattern_code_farazsms" class="form-control ltr sms_on_order_cancelled" value="{{ option('order_cancelled_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_cancelled" rows="4">سفارش شماره %order_id% لغو شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای فروشنده -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن لغو سفارش (فروشنده)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_cancelled_pattern_code_farazsms" class="form-control ltr seller_sms_on_order_cancelled" value="{{ option('seller_order_cancelled_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_cancelled" rows="4">سفارش شماره %order_id% لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای کاربر -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن لغو سفارش (کاربر)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_cancelled_pattern_code_farazsms" class="form-control ltr user_sms_on_order_cancelled" value="{{ option('user_order_cancelled_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_cancelled" rows="4">سفارش شماره %order_id% به دلیل %reason% لغو شد. مبلغ %refund_amount% تومان به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب برگشت وجه به کیف پول -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن برگشت وجه به کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_refund_pattern_code_farazsms" class="form-control ltr wallet_refund_sms" value="{{ option('wallet_refund_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_refund_sms" rows="4">مبلغ %amount% تومان بابت لغو سفارش %order_id% به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
        <!-- ========== پایان بخش جدید ========== -->

        <!-- قالب افزایش موجودی کیف پول -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن افزایش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_increase_pattern_code_farazsms" class="form-control ltr wallet_increase_sms" value="{{ option('wallet_increase_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_increase_sms" rows="4">مبلغ %amount% تومان به اعتبار کیف پول شما اضافه شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب کاهش موجودی کیف پول -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد پترن کاهش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_decrease_pattern_code_farazsms" class="form-control ltr wallet_decrease_sms" value="{{ option('wallet_decrease_pattern_code_farazsms') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_decrease_sms" rows="4">مبلغ %amount% تومان از اعتبار کیف پول شما کسر شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
