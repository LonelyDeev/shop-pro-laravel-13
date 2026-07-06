{{--
    پارشیال مشترک: کارت مجزای «پیامک تبریک تولد»
    ورودی‌ها:
    $name   -> نام فیلد کد پترن تبریک تولد (مخصوص هر درگاه)
    $sample -> متن نمونه پترن تبریک تولد
--}}
<div class="sms-birthday-card">
    <div class="sms-birthday-head">
        <div class="sms-birthday-icon">
            <i class="feather icon-gift"></i>
        </div>
        <div class="sms-birthday-title">
            <h6 class="mb-0">پیامک تبریک تولد</h6>
            <small class="text-muted">کاملاً خودکار — بدون نیاز به هیچ رویدادی</small>
        </div>
        <fieldset class="checkbox sms-toggle-switch mr-auto">
            <div class="vs-checkbox-con vs-checkbox-primary">
                <input data-class="happy_birthday_sms" type="checkbox" name="happy_birthday_sms" {{ option('happy_birthday_sms') == 'on' ? 'checked' : '' }}>
                <span class="vs-checkbox">
                    <span class="vs-checkbox--check">
                        <i class="vs-icon feather icon-check"></i>
                    </span>
                </span>
                <span class=""></span>
            </div>
        </fieldset>
    </div>

    <div class="sms-birthday-alert">
        <i class="feather icon-info"></i>
        <span>
            این پیامک نیازمند هیچ رویداد خاصی مثل ثبت سفارش یا ورود کاربر نیست. سامانه به‌صورت خودکار و روزانه (توسط زمان‌بند سیستم) تاریخ تولد کاربران را بررسی می‌کند و در صورت تطابق با تاریخ روز جاری، پیامک تبریک به‌طور خودکار برای همان کاربر ارسال می‌شود. کافی است پترن زیر را در پنل پیامک بسازید و کد آن را این‌جا وارد کنید.
        </span>
    </div>

    <div class="row">
        <div class="col-md-6 form-group mb-0">
            <label>کد پترن تبریک تولد</label>
            <div class="input-group mb-75">
                <input type="text" name="{{ $name }}" class="form-control ltr happy_birthday_sms" value="{{ option($name) }}" required>
            </div>
        </div>
        <div class="col-md-6 form-group">
            <div class="sms-sample-label">
                <small class="text-muted">متن نمونه ایجاد پترن</small>
                <button type="button" class="btn-copy-sample" title="کپی متن نمونه">
                    <i class="feather icon-copy"></i>
                </button>
            </div>
            <textarea readonly class="form-control happy_birthday_sms sms-sample-text" rows="4">{{ $sample }}</textarea>
        </div>
    </div>
</div>
