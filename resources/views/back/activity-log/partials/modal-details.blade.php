@props(['activity'])

<div class="activity-detail-wrapper">
    <!-- بخش جزئیات پایه -->
    <div class="ad-section">
        <div class="ad-header">
            <div class="ad-header-icon">
                <i class="feather icon-activity"></i>
            </div>
            <div class="ad-header-text">
                <h6 class="ad-title">جزئیات فعالیت</h6>
                <span class="ad-badge {{ $activity['event_class'] }}">
                    <i class="bi {{ $activity['event_icon'] }}"></i>
                    {{ $activity['event'] }}
                </span>
            </div>
        </div>

        <!-- اطلاعات اصلی (گرید) -->
        <div class="ad-grid">
            <div class="ad-info-card">
                <div class="ad-info-icon">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="ad-info-content">
                    <span class="ad-label">شناسه فعالیت</span>
                    <span class="ad-value text-muted">#{{ $activity['id'] }}</span>
                </div>
            </div>

            <div class="ad-info-card">
                <div class="ad-info-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="ad-info-content">
                    <span class="ad-label">نوع مدل</span>
                    <span class="ad-value">
                        @if(isset($activity['subject_link']))
                            <a href="{{ $activity['subject_link'] }}" class="ad-link" target="_blank">
                                {{ $activity['subject_type'] }}
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @else
                            {{ $activity['subject_type'] }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="ad-info-card">
                <div class="ad-info-icon">
                    <i class="fas fa-barcode"></i>
                </div>
                <div class="ad-info-content">
                    <span class="ad-label">شناسه مدل</span>
                    <span class="ad-value text-muted">#{{ $activity['subject_id'] }}</span>
                </div>
            </div>

            <div class="ad-info-card">
                <div class="ad-info-icon">
                    <i class="fas fa-clock-rotate-left"></i>
                </div>
                <div class="ad-info-content">
                    <span class="ad-label">زمان ثبت</span>
                    <span class="ad-value">
                        {{ $activity['created_at'] }}
                        <small class="ad-time-ago">{{ $activity['created_at_diff'] }}</small>
                    </span>
                </div>
            </div>
        </div>

        <!-- انجام دهنده -->
        <div class="ad-profile-box">
            <div class="ad-avatar">
                @php
                    $initial = mb_substr($activity['causer_name'], 0, 1);
                @endphp
                {{ $initial }}
            </div>
            <div class="ad-profile-info">
                <span class="ad-label">انجام دهنده</span>
                <div class="ad-profile-name">{!! $activity['causer_html'] !!}</div>
                <span class="ad-role">{{ $activity['causer_type'] }}</span>
            </div>
        </div>

        <!-- موضوع مورد نظر -->
        @if(isset($activity['subject_html']) && $activity['subject_html'] != $activity['subject_type'])
            <div class="ad-profile-box ad-subject-box">
                <div class="ad-subject-icon">
                    <i class="feather icon-external-link"></i>
                </div>
                <div class="ad-profile-info">
                    <span class="ad-label">مربوط به</span>
                    <div class="ad-profile-name">{!! $activity['subject_html'] !!}</div>
                </div>
            </div>
        @endif

        <!-- توضیحات -->
        @if(isset($activity['description']) && $activity['description'])
            <div class="ad-description-box">
                <div class="ad-desc-icon">
                    <i class="fas fa-box-archive"></i>
                </div>
                <div class="ad-desc-content">
                    <span class="ad-label">توضیحات</span>
                    <p class="ad-desc-text">{{ $activity['description'] }}</p>
                </div>
            </div>
        @endif
    </div>

    @if($activity['event'] != "deleted" && $activity['event'] != "حذف")

        <!-- بخش تغییرات -->
        @php
            // ========== تعریف توابع کمکی برای پردازش JSON ==========

            // تابع بازگشتی برای فرمت کردن مقادیر آرایه‌ای
            function formatArrayValue($value, $translate, $depth = 0) {
                if (is_array($value)) {
                    $parts = [];
                    foreach ($value as $k => $v) {
                        $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
                        $translatedKey = is_callable($translate) ? $translate($k) : $k;

                        if (is_array($v)) {
                            $parts[] = $prefix . "<strong>{$translatedKey}</strong>:<br>" . formatArrayValue($v, $translate, $depth + 1);
                        } else {
                            $formattedValue = is_numeric($v) ? number_format((int)$v) : e($v);
                            $parts[] = $prefix . "<strong>{$translatedKey}</strong>: {$formattedValue}";
                        }
                    }
                    return implode('<br>', $parts);
                }
                return is_numeric($value) ? number_format((int)$value) : e($value);
            }

            // تابع پردازش مقدار JSON
            function processJsonValue($value, $translate) {
                if ($value === null || $value === '') {
                    return '—';
                }

                if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return formatArrayValue($decoded, $translate);
                    }
                }

                // اگر آرایه بود ولی JSON نبود
                if (is_array($value)) {
                    return formatArrayValue($value, $translate);
                }

                return is_numeric($value) ? number_format((int)$value) : e($value);
            }

            // دریافت تابع ترجمه
            $translate = $activity['translateFieldName'] ?? function($key) { return $key; };
        @endphp

        @if(isset($activity['properties']) && ($activity['properties']['old'] || $activity['properties']['attributes']))
            <div class="ad-section ad-changes-section">
                <div class="ad-header">
                    <div class="ad-header-icon ad-changes-icon">
                        <i class="fas fa-arrow-right-arrow-left"></i>
                    </div>
                    <div class="ad-header-text">
                        <h6 class="ad-title">تغییرات انجام شده</h6>
                        <span class="ad-badge info-badge">
                            <i class="bi bi-info-circle"></i>
                            جزئیات تغییرات فیلدها
                        </span>
                    </div>
                </div>

                @if(isset($activity['properties']['old']) && count($activity['properties']['old']) > 0)
                    <div class="ad-changes-list">
                        @foreach($activity['properties']['old'] as $key => $oldValue)
                            @php
                                $newValue = $activity['properties']['attributes'][$key] ?? null;
                                $translatedKey = is_callable($translate) ? $translate($key) : $key;
                                $displayOld = processJsonValue($oldValue, $translate);
                                $displayNew = processJsonValue($newValue, $translate);
                            @endphp
                            <div class="ad-change-item">
                                <div class="ad-change-field">
                                    <i class="fa-solid fa-pencil"></i>
                                    <strong>{{ $translatedKey }}</strong>
                                </div>
                                <div class="ad-change-values">
                                    <div class="ad-value-old">
                                        <span class="ad-change-label">قبلی:</span>
                                        <span class="ad-change-text">
                                            {!! $displayOld !!}
                                        </span>
                                    </div>
                                    <div class="ad-change-arrow">
                                        <i class="fas fa-arrow-left-long"></i>
                                    </div>
                                    <div class="ad-value-new">
                                        <span class="ad-change-label">جدید:</span>
                                        <span class="ad-change-text">
                                            {!! $displayNew !!}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif(isset($activity['properties']['attributes']) && count($activity['properties']['attributes']) > 0)
                    <div class="ad-create-alert">
                        <i class="fas fa-circle-plus"></i>
                        <div>
                            <strong>عملیات ایجاد</strong>
                            <p>مقادیر زیر برای این آیتم ثبت شده است</p>
                        </div>
                    </div>
                    <div class="ad-created-list">
                        @foreach($activity['properties']['attributes'] as $key => $value)
                            @php
                                $translatedKey = is_callable($translate) ? $translate($key) : $key;
                                $displayValue = processJsonValue($value, $translate);
                            @endphp
                            <div class="ad-created-item">
                                <span class="ad-created-key">{{ $translatedKey }}</span>
                                <span class="ad-created-value">{!! $displayValue !!}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- ========== بخش نمایش ویژگی‌های تنوع (برای create_variation) ========== --}}
        @if(isset($activity['properties']['action']) && $activity['properties']['action'] == 'create_variation')
            @if(isset($activity['properties']['attributes']) && count($activity['properties']['attributes']) > 0)
                <div class="ad-section ad-changes-section">
                    <div class="ad-header">
                        <div class="ad-header-icon ad-changes-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-cube"></i>
                        </div>
                        <div class="ad-header-text">
                            <h6 class="ad-title">جزئیات تنوع جدید</h6>
                            <span class="ad-badge info-badge" style="background: #e8f5e9; color: #2e7d32;">
                                <i class="bi bi-info-circle"></i>
                                اطلاعات کامل تنوع ایجاد شده
                            </span>
                        </div>
                    </div>
                    <div class="ad-created-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
                        @foreach($activity['properties']['attributes'] as $key => $value)
                            @php
                                $translatedKey = is_callable($translate) ? $translate($key) : $key;
                                $displayValue = processJsonValue($value, $translate);
                            @endphp
                            <div class="ad-created-item" style="background: #f8f9fa; border-radius: 10px; padding: 0.75rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e9ecef;">
                                <span class="ad-created-key" style="font-size: 0.8rem; font-weight: 600; color: #6c757d;">{{ $translatedKey }}</span>
                                <span class="ad-created-value" style="font-size: 0.85rem; color: #2c3e50; font-weight: 500;">{!! $displayValue !!}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- ========== بخش نمایش ویژگی‌های حذف تنوع (برای delete_variation) ========== --}}
        @if(isset($activity['properties']['action']) && $activity['properties']['action'] == 'delete_variation')
            @if(isset($activity['properties']['old']) && count($activity['properties']['old']) > 0)
                <div class="ad-section ad-changes-section">
                    <div class="ad-header">
                        <div class="ad-header-icon ad-changes-icon" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div class="ad-header-text">
                            <h6 class="ad-title">تنوع حذف شده</h6>
                            <span class="ad-badge info-badge" style="background: #fbe9e7; color: #c62828;">
                                <i class="bi bi-exclamation-triangle"></i>
                                اطلاعات تنوع حذف شده
                            </span>
                        </div>
                    </div>
                    <div class="ad-created-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
                        @foreach($activity['properties']['old'] as $key => $value)
                            @php
                                $translatedKey = is_callable($translate) ? $translate($key) : $key;
                                $displayValue = processJsonValue($value, $translate);
                            @endphp
                            <div class="ad-created-item" style="background: #fff5f5; border-radius: 10px; padding: 0.75rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #ffcdd2;">
                                <span class="ad-created-key" style="font-size: 0.8rem; font-weight: 600; color: #c62828;">{{ $translatedKey }}</span>
                                <span class="ad-created-value" style="font-size: 0.85rem; color: #c62828; font-weight: 500;">{!! $displayValue !!}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

    @endif
</div>
