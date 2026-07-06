{{--
    پارشیال مشترک: کارت روشن/خاموش برای هر گزینه پیامکی عمومی
    ورودی‌ها:
    $name  -> نام فیلد (هم به عنوان name و هم data-class استفاده می‌شود، دقیقاً مانند نسخه قبلی)
    $icon  -> کلاس آیکون feather ، مثل icon-user-plus
    $title -> عنوان گزینه
    $desc  -> (اختیاری) توضیح کوتاه زیر عنوان
    $accent-> (اختیاری) رنگ تاکیدی کارت: primary | warning | info | success | danger
--}}
@php($accent = $accent ?? 'primary')
<div class="sms-toggle-card accent-{{ $accent }}">
    <div class="sms-toggle-icon">
        <i class="feather {{ $icon }}"></i>
    </div>
    <div class="sms-toggle-content">
        <strong>{{ $title }}</strong>
        @isset($desc)
            <small>{{ $desc }}</small>
        @endisset
    </div>
    <fieldset class="checkbox sms-toggle-switch">
        <div class="vs-checkbox-con vs-checkbox-primary">
            <input data-class="{{ $name }}" type="checkbox" name="{{ $name }}" {{ option($name) == 'on' ? 'checked' : '' }}>
            <span class="vs-checkbox">
                <span class="vs-checkbox--check">
                    <i class="vs-icon feather icon-check"></i>
                </span>
            </span>
            <span class=""></span>
        </div>
    </fieldset>
</div>
