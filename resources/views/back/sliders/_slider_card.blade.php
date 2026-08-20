{{--
    پارشال کارت اسلایدر برای نمایش در ایندکس
    متغیرها:
      $slider       : مدل اسلایدر
      $groupCatalog : کاتالوگ گروه‌ها (از Slider::availableGroups())
--}}
@php
    $groups = $slider->groups ?: [];
@endphp

<div class="sk-slider-card {{ $slider->published ? '' : 'is-draft' }}">
    {{-- تصویر + نشانگرها --}}
    <div class="sk-slider-card-image">
        @if ($slider->image)
            <img src="{{ asset($slider->image) }}" alt="{{ $slider->title ?: '' }}">
        @else
            <div class="sk-slider-card-image-placeholder">
                <i class="fa fa-image"></i>
            </div>
        @endif

        @if ($slider->ordering)
            <span class="sk-slider-card-order">{{ $slider->ordering }}</span>
        @endif

        @if ($slider->published)
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
        <h5 class="sk-slider-card-title">{{ $slider->title ?: 'بدون عنوان' }}</h5>

        @if ($slider->motionTitle)
            <div class="sk-slider-card-motion">
                <i class="fa fa-bolt"></i> {{ $slider->motionTitle }}
            </div>
        @endif

        @if ($slider->link)
            <a href="{{ $slider->link }}" target="_blank" class="sk-slider-card-link">
                <i class="fa fa-link"></i> {{ $slider->link }}
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
    </div>

    {{-- اکشن‌ها --}}
    <div class="sk-slider-card-actions">
        <a href="{{ route('admin.sliders.edit', $slider) }}"
           class="sk-action-btn sk-action-btn--edit"
           title="ویرایش">
            <i class="fa fa-pen"></i>
            <span class="ms-1">ویرایش</span>
        </a>
        <form action="{{ route('admin.sliders.destroy', $slider) }}"
              method="POST"
              onsubmit="return confirm('این اسلایدر حذف شود؟')">
            @csrf
            @method('DELETE')
            <button class="sk-action-btn sk-action-btn--delete" title="حذف">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    </div>
</div>
