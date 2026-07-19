{{--
    Dropdown انتخاب نوع ویجت (Page-specific)
    استفاده: @include('back.widgets.partials.widget-select', ['page' => 'home'])
    یا برای posts: @include('back.widgets.partials.widget-select', ['page' => 'posts'])

    متغیرهای قابل تنظیم:
    - $page: نوع صفحه (home, posts, products) - پیش‌فرض 'home'
    - $widget: (اختیاری) مدل ویجت برای حالت ویرایش
--}}
@php
    $page = $page ?? 'home';
    $coreConfig = config("front.{$page}-widgets", []);
    $moduleWidgets = \App\Services\WidgetTemplateRegistry::allForPage($page);
@endphp

<select id="widget-key" name="key" required class="form-select">
    <option value="">انتخاب کنید</option>

    {{-- ویجت‌های اصلی پروژه برای این صفحه --}}
    @if (!empty($coreConfig))
        <optgroup label="─────── ویجت‌های {{ $page === 'home' ? 'صفحه اصلی' : ($page === 'posts' ? 'صفحه مقالات' : ($page === 'products' ? 'صفحه محصولات' : $page)) }} ───────">
            @foreach ($coreConfig as $key => $template_widget)
                <option value="{{ $key }}"
                        data-image="{{ isset($template_widget['image']) ? theme_asset($template_widget['image']) : '' }}"
                        data-title="{{ $template_widget['title'] }}"
                        data-action="{{ route('admin.widgets.template', ['key' => $key]) }}"
                        @if (isset($widget) && $widget->key === $key) selected @endif>
                    {{ $template_widget['title'] }}
                </option>
            @endforeach
        </optgroup>
    @endif

    {{-- ویجت‌های ثبت‌شده توسط ماژول‌ها برای این صفحه --}}
    @if (!empty($moduleWidgets))
        <optgroup label="─────── ویجت‌های ماژول‌ها ───────">
            @foreach ($moduleWidgets as $key => $widget_template)
                <option value="{{ $key }}"
                        data-image="{{ $widget_template['image_url'] ?? '' }}"
                        data-title="{{ $widget_template['title'] }}"
                        data-action="{{ route('admin.widgets.template', ['key' => $key]) }}"
                        @if (isset($widget) && $widget->key === $key) selected @endif>
                    {{ $widget_template['title'] }}
                    <small class="text-muted">({{ $widget_template['module'] }})</small>
                </option>
            @endforeach
        </optgroup>
    @endif
</select>
