{{--
    پارشال چک‌باکس‌های چند انتخابی (بنر)
    متغیرها:
      $name      : pages, groups یا places
      $options   : آرایه گزینه‌ها
      $selected  : آرایه کلیدهای انتخاب شده
      $title     : عنوان بخش
      $icon      : آیکون هدر
      $variant   : pages, groups یا places
--}}

@php
    $selected = $selected ?? [];
    $variant  = $variant ?? 'pages';

    // آیکون‌های اختصاصی برای صفحات بنر
    $pageIcons = [
        'home'  => 'fa-house',
        'posts' => 'fa-newspaper',
    ];

    // آیکون‌های اختصاصی برای موقعیت‌ها
    $placeIcons = [
        'index_banners_place1' => 'fa-location-dot',
        'index_banners_place2' => 'fa-location-dot',
        'index_banners_place3' => 'fa-location-dot',
        'index_banners_place4' => 'fa-location-dot',
    ];

    // ساخت map آیکون‌ها بر اساس variant
    if ($variant === 'groups') {
        $iconMap = array_map(fn($g) => $g['icon'] ?? 'fa-image', $options);
    } elseif ($variant === 'places') {
        $iconMap = $placeIcons;
    } else {
        $iconMap = $pageIcons;
    }
@endphp

<div class="sk-section sk-section--{{ $variant }}">
    <div class="sk-section-header">
        <span class="sk-section-icon">
            <i class="fa {{ $icon }}"></i>
        </span>
        <div class="sk-section-titles">
            <h4 class="sk-section-title">{{ $title }}</h4>
            <small class="sk-section-subtitle">
                <span class="counter" data-counter="{{ $name }}">0</span>
                مورد انتخاب شده — می‌توانید چند مورد را همزمان انتخاب کنید
            </small>
        </div>
        <div class="sk-section-actions">
            <button type="button" class="sk-mini-btn sk-btn-select-all" data-target="{{ $name }}">
                <i class="fa fa-check-double"></i> انتخاب همه
            </button>
            <button type="button" class="sk-mini-btn sk-btn-clear" data-target="{{ $name }}">
                <i class="fa fa-eraser"></i> پاک کردن
            </button>
        </div>
    </div>

    <div class="sk-grid">
        @foreach ($options as $key => $item)
            @php
                $checked  = in_array($key, $selected, true);
                $label    = is_array($item) ? ($item['label'] ?? $key) : $item;
                $size     = is_array($item) ? ($item['size'] ?? null) : null;
                $itemIcon = $iconMap[$key] ?? 'fa-circle-dot';
            @endphp
            <label class="sk-card {{ $checked ? 'is-checked' : '' }}">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $key }}"
                    class="sk-card-input"
                    {{ $checked ? 'checked' : '' }}
                >
                <span class="sk-card-body">
                    <span class="sk-card-icon-wrap">
                        <i class="fa {{ $itemIcon }}"></i>
                    </span>
                    <span class="sk-card-text">
                        <span class="sk-card-label">{{ $label }}</span>
                        @if ($size)
                            <span class="sk-card-meta">
                                <i class="fa fa-ruler-combined"></i> ابعاد: {{ $size }}
                            </span>
                        @else
                            <span class="sk-card-meta">
                                <code>{{ $key }}</code>
                            </span>
                        @endif
                    </span>
                    <span class="sk-card-check">
                        <i class="fa fa-check"></i>
                    </span>
                </span>
            </label>
        @endforeach
    </div>

    @error($name)
        <div class="sk-error-msg">
            <i class="fa fa-circle-exclamation"></i>
            {{ $message }}
        </div>
    @enderror
</div>
