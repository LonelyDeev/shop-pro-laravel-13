{{--
    بخش انتخاب صفحات (هاردکد شده - ۳ صفحه ثابت)
    متغیرها:
      $selected : آرایه‌ای از کلیدهای انتخاب شده (در حالت edit)
--}}

@php
    $selected = $selected ?? [];
@endphp

<div class="sk-section sk-section--pages">
    <div class="sk-section-header">
        <span class="sk-section-icon">
            <i class="fa fa-file-lines"></i>
        </span>
        <div class="sk-section-titles">
            <h4 class="sk-section-title">نمایش در صفحه</h4>
            <small class="sk-section-subtitle">
                <span class="counter" data-counter="pages">0</span>
                مورد انتخاب شده — می‌توانید چند صفحه را همزمان انتخاب کنید
            </small>
        </div>
        <div class="sk-section-actions">
            <button type="button" class="sk-mini-btn sk-btn-select-all" data-target="pages">
                <i class="fa fa-check-double"></i> انتخاب همه
            </button>
            <button type="button" class="sk-mini-btn sk-btn-clear" data-target="pages">
                <i class="fa fa-eraser"></i> پاک کردن
            </button>
        </div>
    </div>

    <div class="sk-grid sk-grid--3">
        {{-- صفحه اصلی --}}
        <label class="sk-card {{ in_array('home', $selected) ? 'is-checked' : '' }}">
            <input type="checkbox" name="pages[]" value="home" class="sk-card-input" {{ in_array('home', $selected) ? 'checked' : '' }}>
            <span class="sk-card-body">
                <span class="sk-card-icon-wrap">
                    <i class="fa fa-house"></i>
                </span>
                <span class="sk-card-text">
                    <span class="sk-card-label">صفحه اصلی</span>
                    <span class="sk-card-meta"><code>home</code></span>
                </span>
                <span class="sk-card-check"><i class="fa fa-check"></i></span>
            </span>
        </label>

        {{-- صفحه اصلی مقالات --}}
        <label class="sk-card {{ in_array('posts', $selected) ? 'is-checked' : '' }}">
            <input type="checkbox" name="pages[]" value="posts" class="sk-card-input" {{ in_array('posts', $selected) ? 'checked' : '' }}>
            <span class="sk-card-body">
                <span class="sk-card-icon-wrap">
                    <i class="fa fa-newspaper"></i>
                </span>
                <span class="sk-card-text">
                    <span class="sk-card-label">صفحه اصلی مقالات</span>
                    <span class="sk-card-meta"><code>posts</code></span>
                </span>
                <span class="sk-card-check"><i class="fa fa-check"></i></span>
            </span>
        </label>

        {{-- صفحه اصلی فروشندگان --}}
        <label class="sk-card {{ in_array('sellers', $selected) ? 'is-checked' : '' }}">
            <input type="checkbox" name="pages[]" value="sellers" class="sk-card-input" {{ in_array('sellers', $selected) ? 'checked' : '' }}>
            <span class="sk-card-body">
                <span class="sk-card-icon-wrap">
                    <i class="fa fa-store"></i>
                </span>
                <span class="sk-card-text">
                    <span class="sk-card-label">صفحه اصلی فروشندگان</span>
                    <span class="sk-card-meta"><code>sellers</code></span>
                </span>
                <span class="sk-card-check"><i class="fa fa-check"></i></span>
            </span>
        </label>
    </div>

    @error('pages')
        <div class="sk-error-msg">
            <i class="fa fa-circle-exclamation"></i>
            {{ $message }}
        </div>
    @enderror
</div>
