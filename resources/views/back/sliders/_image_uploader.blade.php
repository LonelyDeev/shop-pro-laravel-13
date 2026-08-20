{{--
    دراپ‌زون تصویر اسلایدر (یکپارچه با file manager سایت)
    متغیرها:
      $currentImage : URL تصویر فعلی (در edit)
      $required     : آیا تصویر ضروری است؟
--}}

@php
    $currentImage = $currentImage ?? null;
    $required     = $required ?? true;
    $hasImage     = !empty($currentImage);
@endphp

<div class="sk-dropzone">
    <div class="sk-section-header" style="border-bottom: 1px dashed var(--sk-border-soft); padding-bottom: 1rem; margin-bottom: 1rem;">
        <span class="sk-section-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 10px 20px -5px rgba(99, 102, 241, .4);">
            <i class="fa fa-image"></i>
        </span>
        <div class="sk-section-titles">
            <h4 class="sk-section-title">تصویر اسلایدر</h4>
            <small class="sk-section-subtitle">
                تصویر را از رسانه‌های سایت انتخاب کنید
            </small>
        </div>
    </div>

    <div class="dropzone-wrapper" style="position: relative;">
        @if ($required)
            <span class="dz-required">
                <i class="fa fa-asterisk"></i> ضروری
            </span>
        @endif

        <button
            type="button"
            id="button-image"
            class="dz-area {{ $hasImage ? 'has-image' : '' }}"
            aria-label="انتخاب تصویر از رسانه‌ها"
        >
            {{-- حالت خالی --}}
            <div class="dz-empty" @if ($hasImage) style="display:none;" @endif>
                <div class="dz-icon">
                    <i class="fa fa-cloud-arrow-up"></i>
                </div>
                <h6 class="dz-title">تصویر اسلایدر را انتخاب کنید</h6>
                <p class="dz-hint">
                    برای انتخاب از رسانه‌های سایت کلیک کنید
                    <span class="badge"><i class="fa fa-folder-open"></i> File Manager</span>
                </p>
            </div>

            {{-- پیش‌نمایش --}}
            <div class="img-uploader" @if (!$hasImage and !$currentImage) style="display:none;" @endif>
                <img
                    src="{{ $hasImage ? asset($currentImage) : '' }}"
                    alt="preview"
                    class="dz-preview"
                />
                <div class="dz-overlay">
                    <span class="dz-btn dz-btn--change">
                        <i class="fa fa-arrows-rotate"></i> تغییر تصویر
                    </span>
                    <span
                        class="dz-btn dz-btn--remove remove-img-uploader"
                        role="button"
                    >
                        <i class="fa fa-trash"></i> حذف
                    </span>
                </div>
            </div>
        </button>

        <input
            type="hidden"
            name="image"
            id="image_label"
            value="{{ $currentImage ?? '' }}"
        />

        @error('image')
            <div class="sk-error-msg mt-2">
                <i class="fa fa-circle-exclamation"></i>
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
