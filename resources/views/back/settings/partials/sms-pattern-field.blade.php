{{--
    پارشیال مشترک: فیلد «کد پترن» به همراه «متن نمونه» و دکمه کپی
    ورودی‌ها:
    $name        -> نام فیلد اینپوت (name می‌شود)
    $label       -> برچسب فیلد
    $sample      -> متن نمونه داخل textarea (readonly)
    $toggleClass -> (اختیاری) کلاسی که با تیک مربوطه در بالای فرم نمایش/مخفی می‌شود (دقیقاً هم‌نام data-class)
    $required    -> (اختیاری) آیا فیلد الزامی است
    $icon        -> (اختیاری) آیکون کارت
--}}
@php($toggleClass = $toggleClass ?? '')
@php($icon = $icon ?? 'icon-hash')
<div class="col-md-6 sms-card-setting sms-pattern-card {{ $toggleClass }}">
    <div class="row">
        <div class="col-md-12 form-group mb-0">
            <label class="sms-pattern-label">
                <i class="feather {{ $icon }}"></i>
                {{ $label }}
            </label>
            <div class="input-group mb-75">
                <input type="text" name="{{ $name }}" class="form-control ltr {{ $toggleClass }}" value="{{ option($name) }}" {{ ($required ?? false) ? 'required' : '' }}>
            </div>
        </div>
        <div class="col-md-12 form-group">
            <div class="sms-sample-label">
                <small class="text-muted">متن نمونه ایجاد پترن</small>
                <button type="button" class="btn-copy-sample" title="کپی متن نمونه">
                    <i class="feather icon-copy"></i>
                </button>
            </div>
            <textarea readonly class="form-control sms-sample-text {{ $toggleClass }}" rows="4">{{ $sample }}</textarea>
        </div>
    </div>
</div>
