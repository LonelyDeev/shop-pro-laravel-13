<div class="sms-panel-fields" id="kavenegar-sms-fields" style="{!! option('sms_panel_provider', 'kavenegar') != 'kavenegar' ? 'display: none;' : '' !!}">
    <h5 class="my-2">اطلاعات پنل پیامک کاوه نگار</h5>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <label>کلید وب سرویس</label>
            <div class="input-group mb-75">
                <input type="text" name="KAVENEGAR_PANEL_APIKEY" class="form-control ltr" value="{{ option('KAVENEGAR_PANEL_APIKEY') }}">
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی خوش آمدگویی فروشنده</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_register_pattern_code_kavenegar" class="form-control ltr sms_on_seller_register" value="{{ option('seller_register_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_seller_register" rows="4"> فروشنده عزیز با عنوان فروشگاه %token2، خوش آمدید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی خوش آمدگویی کاربر</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_register_pattern_code_kavenegar" class="form-control ltr sms_on_user_register" value="{{ option('user_register_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_user_register" rows="4">%token2 عزیز خوش آمدید با شماره موبایل %token.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی ارسال کد تایید</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_verify_pattern_code_kavenegar" class="form-control ltr" value="{{ option('user_verify_pattern_code_kavenegar') }}" >
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control" rows="4">کد تایید: %token &#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی پرداخت سفارش</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_paid_pattern_code_kavenegar" class="form-control ltr sms_on_order_paid" value="{{ option('order_paid_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_paid" rows="4">سفارش جدید با شماره سفارش %token ثبت و پرداخت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی پرداخت سفارش برای فروشنده</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_paid_pattern_code_kavenegar" class="form-control ltr seller_sms_on_order_paid" value="{{ option('seller_order_paid_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش %token با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی پرداخت سفارش برای کاربر</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_paid_pattern_code_kavenegar" class="form-control ltr user_sms_on_order_paid" value="{{ option('user_order_paid_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_paid" rows="4">سفارش شما با شماره سفارش %token با موفقیت ثبت شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========== بخش جدید: قالب‌های لغو سفارش ========== -->
        <div class="col-md-12">
            <hr>
            <h5 class="my-2">قالب‌های پیامکی لغو سفارش</h5>
            <hr>
        </div>

        <!-- قالب لغو سفارش برای ادمین -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی لغو سفارش (ادمین)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="order_cancelled_pattern_code_kavenegar" class="form-control ltr sms_on_order_cancelled" value="{{ option('order_cancelled_pattern_code_kavenegar') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control sms_on_order_cancelled" rows="4">سفارش شماره %token لغو شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای فروشنده -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی لغو سفارش (فروشنده)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="seller_order_cancelled_pattern_code_kavenegar" class="form-control ltr seller_sms_on_order_cancelled" value="{{ option('seller_order_cancelled_pattern_code_kavenegar') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control seller_sms_on_order_cancelled" rows="4">سفارش شماره %token لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب لغو سفارش برای کاربر -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی لغو سفارش (کاربر)</label>
                    <div class="input-group mb-75">
                        <input type="text" name="user_order_cancelled_pattern_code_kavenegar" class="form-control ltr user_sms_on_order_cancelled" value="{{ option('user_order_cancelled_pattern_code_kavenegar') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control user_sms_on_order_cancelled" rows="4">سفارش شماره %token به دلیل %token2 لغو شد. مبلغ %token3 تومان به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <!-- قالب برگشت وجه به کیف پول -->
        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>الگوی برگشت وجه به کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_refund_pattern_code_kavenegar" class="form-control ltr wallet_refund_sms" value="{{ option('wallet_refund_pattern_code_kavenegar') }}">
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_refund_sms" rows="4">مبلغ %token تومان بابت لغو سفارش %token2 به کیف پول شما برگشت داده شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
        <!-- ========== پایان بخش جدید ========== -->

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن افزایش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_increase_pattern_code_kavenegar" class="form-control ltr wallet_increase_sms" value="{{ option('wallet_increase_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_increase_sms" rows="4">مبلغ %token تومان به اعتبار کیف پول شما اضافه شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 sms-card-setting">
            <div class="row">
                <div class="col-md-12 form-group mb-0">
                    <label>کد متن کاهش موجودی کیف پول</label>
                    <div class="input-group mb-75">
                        <input type="text" name="wallet_decrease_pattern_code_kavenegar" class="form-control ltr wallet_decrease_sms" value="{{ option('wallet_decrease_pattern_code_kavenegar') }}" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label>متن نمونه ایجاد پترن</label>
                    <textarea readonly class="form-control wallet_decrease_sms" rows="4">مبلغ %token تومان از اعتبار کیف پول شما کسر شد.&#13;&#10 {{ option('info_site_title') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
