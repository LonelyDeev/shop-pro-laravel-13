<div class="sms-panel-fields" id="idehpardazan-sms-fields" style="{!! option('sms_panel_provider', 'idehpardazan') != 'idehpardazan' ? 'display: none;' : '' !!}">
    <h5 class="my-2">اطلاعات پنل ایده پردازان</h5>
    <hr>
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

    <hr>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن خوش آمدگویی</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_register_pattern_code_idehpardazan" class="form-control ltr sms_on_user_register" value="{{ option('user_register_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_user_register" rows="4"> [fullname] عزیز خوش آمدید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن ارسال کد تایید</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_verify_pattern_code_idehpardazan" class="form-control ltr" value="{{ option('user_verify_pattern_code_idehpardazan') }}" >
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control" rows="4">کد تایید: [code] &#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن پرداخت سفارش</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_paid_pattern_code_idehpardazan" class="form-control ltr sms_on_order_paid" value="{{ option('order_paid_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_paid" rows="4">سفارش جدید با شماره سفارش [order_id] ثبت و پرداخت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن پرداخت سفارش برای فروشنده</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_paid_pattern_code_idehpardazan" class="form-control ltr seller_sms_on_order_paid" value="{{ option('seller_order_paid_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش [order_id] با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن پرداخت سفارش برای کاربر</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_paid_pattern_code_idehpardazan" class="form-control ltr user_sms_on_order_paid" value="{{ option('user_order_paid_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش [order_id] با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========== بخش جدید: قالب‌های لغو سفارش برای ایده پردازان ========== -->
        <div class="col-md-12">
            <hr>
            <h5 class="my-2">قالب‌های پیامکی لغو سفارش</h5>
            <hr>
        </div>

        <!-- قالب لغو سفارش برای ادمین -->
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن لغو سفارش (ادمین)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_cancelled_pattern_code_idehpardazan" class="form-control ltr sms_on_order_cancelled" value="{{ option('order_cancelled_pattern_code_idehpardazan') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_cancelled" rows="4">سفارش شماره [order_id] لغو شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای فروشنده -->
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن لغو سفارش (فروشنده)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_cancelled_pattern_code_idehpardazan" class="form-control ltr seller_sms_on_order_cancelled" value="{{ option('seller_order_cancelled_pattern_code_idehpardazan') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_cancelled" rows="4">سفارش شماره [order_id] لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای کاربر -->
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن لغو سفارش (کاربر)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_cancelled_pattern_code_idehpardazan" class="form-control ltr user_sms_on_order_cancelled" value="{{ option('user_order_cancelled_pattern_code_idehpardazan') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_cancelled" rows="4">سفارش شماره [order_id] به دلیل [reason] لغو شد. مبلغ [refund_amount] تومان به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب برگشت وجه به کیف پول -->
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن برگشت وجه به کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_refund_pattern_code_idehpardazan" class="form-control ltr wallet_refund_sms" value="{{ option('wallet_refund_pattern_code_idehpardazan') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_refund_sms" rows="4">مبلغ [amount] تومان بابت لغو سفارش [order_id] به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
        <!-- ========== پایان بخش جدید ========== -->

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن افزایش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_increase_pattern_code_idehpardazan" class="form-control ltr wallet_increase_sms" value="{{ option('wallet_increase_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_increase_sms" rows="4">مبلغ [amount] تومان به اعتبار کیف پول شما اضافه شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن کاهش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_decrease_pattern_code_idehpardazan" class="form-control ltr wallet_decrease_sms" value="{{ option('wallet_decrease_pattern_code_idehpardazan') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_decrease_sms" rows="4">مبلغ [amount] تومان از اعتبار کیف پول شما کسر شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
