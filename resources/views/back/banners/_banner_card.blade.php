{{--
    پارشال کارت بنر برای نمایش در ایندکس
    متغیرها:
      $banner       : مدل بنر
      $groupCatalog : کاتالوگ گروه‌ها (از Banner::availableGroups())
      $placeCatalog : کاتالوگ موقعیت‌ها (از Banner::availablePlaces())
--}}
@php
    $groups = $banner->groups ?: [];
    $places = $banner->places ?: [];
@endphp

<div class="sk-slider-card {{ $banner->published ? '' : 'is-draft' }}">
    {{-- تصویر + نشانگرها --}}
    <div class="sk-slider-card-image">
        @if ($banner->image)
            <img src="{{ asset($banner->image) }}" alt="{{ $banner->title ?: '' }}">
        @else
            <div class="sk-slider-card-image-placeholder">
                <i class="fa fa-image"></i>
            </div>
        @endif

        @if ($banner->ordering)
            <span class="sk-slider-card-order">{{ $banner->ordering }}</span>
        @endif

        @if ($banner->published)
            <span class="sk-slider-card-status sk-slider-card-status--published">
                <span class="dot"></span> منتشر
            </span>
        @else
            <span class="sk-slider-card-status sk-slider-card-status--draft">
                <span class="dot"></span> پیش‌نویس
            </span>
        @endif
    </div>

    {{-- بدنه کارت --}}
    <div class="sk-slider-card-body">
        <h5 class="sk-slider-card-title">{{ $banner->title ?: 'بدون عنوان' }}</h5>

        @if ($banner->link)
            <a href="{{ $banner->link }}" target="_blank" class="sk-slider-card-link">
                <i class="fa fa-link"></i> {{ $banner->link }}
            </a>
        @endif

        {{-- گروه‌ها --}}
        @if (count($groups))
            <div class="sk-slider-card-groups">
                <span class="sk-slider-card-groups-label">
                    <i class="fa fa-layer-group"></i> گروه‌ها
                </span>
                @foreach ($groups as $groupKey)
                    @php
                        $gItem  = $groupCatalog[$groupKey] ?? null;
                        $gLabel = $gItem ? $gItem['label'] : $groupKey;
                        $gSize  = $gItem ? $gItem['size']  : '';
                        $gIcon  = $gItem ? $gItem['icon']  : 'fa-image';
                    @endphp
                    <span class="sk-group-chip">
                        <i class="fa {{ $gIcon }}"></i>
                        {{ $gLabel }}
                        @if ($gSize)
                            <span class="size">— {{ $gSize }}</span>
                        @endif
                    </span>
                @endforeach
            </div>
        @endif

        {{-- موقعیت‌ها --}}
        @if (count($places))
            <div class="sk-slider-card-groups" style="border-top: none; padding-top: 0; margin-top: .5rem;">
                <span class="sk-slider-card-groups-label">
                    <i class="fa fa-location-dot"></i> موقعیت‌ها
                </span>
                @foreach ($places as $placeKey)
                    @php
                        $pLabel = $placeCatalog[$placeKey] ?? $placeKey;
                    @endphp
                    <span class="sk-chip sk-chip--place">
                        <i class="fa fa-location-dot"></i>
                        {{ $pLabel }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- اکشن‌ها --}}
    <div class="sk-slider-card-actions">
        <a href="{{ route('admin.banners.edit', $banner) }}"
           class="sk-action-btn sk-action-btn--edit"
           title="ویرایش">
            <i class="fa fa-pen"></i>
            <span class="ms-1">ویرایش</span>
        </a>
        <form action="{{ route('admin.banners.destroy', $banner) }}"
              method="POST"
              onsubmit="return confirm('این بنر حذف شود؟')">
            @csrf
            @method('DELETE')
            <button class="sk-action-btn sk-action-btn--delete" title="حذف">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    </div>
</div>
