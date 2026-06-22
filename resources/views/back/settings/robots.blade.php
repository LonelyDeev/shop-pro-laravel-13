@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/settings-robots.css') }}">
@endpush

@section('content')
    <div class="app-content content" dir="rtl">
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row mb-1">
                <div class="col-12">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item">توسعه دهنده</li>
                        <li class="breadcrumb-item active">robots.txt</li>
                    </ol>
                </div>
            </div>

            <div class="content-body rb-wrap">

                {{-- هشدار بالای صفحه --}}
                <div class="rb-alert rb-alert-danger" style="background: #f8d7da; border-right: 4px solid #dc3545; padding: 18px 22px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 15px; box-shadow: 0 2px 10px rgba(220, 53, 69, 0.1);">
                    <div style="font-size: 28px; color: #721c24; flex-shrink: 0; line-height: 1;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 5px;">
                            <strong style="color: #721c24; font-size: 1.05rem;">⚠️ هشدار مهم!</strong>
                            <span style="background: #dc3545; color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;">نیازمند دقت</span>
                        </div>
                        <p style="margin: 5px 0 0 0; color: #721c24; font-size: 0.9rem; line-height: 1.7;">
                            تنظیمات <strong>robots.txt</strong> مستقیماً روی ایندکس شدن سایت توسط گوگل تأثیر می‌گذارد.
                        </p>
                        <ul style="margin: 8px 0 0 0; padding-right: 20px; color: #721c24; font-size: 0.85rem; line-height: 1.8;">
                            <li>❌ <strong>Disallow: /</strong> یعنی کل سایت از ایندکس خارج می‌شود</li>
                            <li>✅ مسیرهای پیش‌فرض برای سایت‌های فروشگاهی بهینه هستند</li>
                            <li>📋 قبل از ذخیره، حتماً <strong>پیش‌نمایش</strong> را بررسی کنید</li>
                            <li>🔄 پس از تغییر، گوگل ممکن است چند روز طول بکشد تا تغییرات را اعمال کند</li>
                        </ul>
                        <div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap; font-size: 0.8rem; color: #721c24; background: rgba(220, 53, 69, 0.08); padding: 10px 14px; border-radius: 8px;">
                            <span><i class="fas fa-check-circle" style="color: #28a745;"></i> مسیرهای پیش‌فرض: <code style="background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 4px;">/admin/</code> <code style="background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 4px;">/panel/</code> <code style="background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 4px;">/login</code></span>
                        </div>
                    </div>
                </div>

                {{-- ── Hero Header ── --}}
                <div class="rb-header">
                    <div class="rb-header-info">
                        <h2>🤖 مدیریت robots.txt</h2>
                        <p>کنترل دسترسی موتورهای جستجو به بخش‌های مختلف سایت</p>
                    </div>
                    <div style="display:flex;gap:.7rem;flex-wrap:wrap;align-items:center">
                        <div class="rb-header-badge">
                            <div class="rb-live-dot"></div>
                            <span id="current-mode-badge">
              {{ $settings['mode'] === 'production' ? '🟢 حالت تولید' : ($settings['mode'] === 'development' ? '🟡 حالت توسعه' : '🔴 غیرفعال') }}
            </span>
                        </div>
                        <a href="{{ config('app.url') }}/robots.txt" target="_blank" class="rb-header-badge" style="text-decoration:none">
                            🔗 مشاهده فایل زنده
                        </a>
                    </div>
                </div>

                <form id="robots-form" action="{{ route('admin.robots.update') }}" method="POST">
                    @csrf

                    {{-- ── انتخاب حالت ── --}}
                    <div class="rb-card" style="margin-bottom:1.2rem">
                        <div class="rb-card-header">⚙️ حالت عملکرد</div>
                        <div class="rb-card-body">
                            <div class="rb-mode-grid">

                                <label class="rb-mode-card {{ $settings['mode'] === 'production' ? 'selected' : '' }}" onclick="selectMode(this,'production')">
                                    <input type="radio" name="mode" value="production" {{ $settings['mode'] === 'production' ? 'checked' : '' }}>
                                    <div class="rb-mode-check">✓</div>
                                    <span class="rb-mode-badge ok">پیش‌فرض</span>
                                    <span class="rb-mode-icon">🟢</span>
                                    <div class="rb-mode-title">تولید (Production)</div>
                                    <div class="rb-mode-desc">موتورهای جستجو اجازه کراول دارند. مسیرهای ممنوع احترام گذاشته می‌شوند.</div>
                                </label>

                                <label class="rb-mode-card {{ $settings['mode'] === 'development' ? 'selected-warn' : '' }}" onclick="selectMode(this,'development')">
                                    <input type="radio" name="mode" value="development" {{ $settings['mode'] === 'development' ? 'checked' : '' }}>
                                    <div class="rb-mode-check">✓</div>
                                    <span class="rb-mode-badge warn">توسعه</span>
                                    <span class="rb-mode-icon">🟡</span>
                                    <div class="rb-mode-title">توسعه (Development)</div>
                                    <div class="rb-mode-desc">همه بات‌ها مسدود می‌شوند. برای محیط‌های staging و dev مناسب است.</div>
                                </label>

                                <label class="rb-mode-card {{ $settings['mode'] === 'disabled' ? 'selected-danger' : '' }}" onclick="selectMode(this,'disabled')">
                                    <input type="radio" name="mode" value="disabled" {{ $settings['mode'] === 'disabled' ? 'checked' : '' }}>
                                    <div class="rb-mode-check">✓</div>
                                    <span class="rb-mode-badge bad">هشدار</span>
                                    <span class="rb-mode-icon">🔴</span>
                                    <div class="rb-mode-title">غیرفعال (Disabled)</div>
                                    <div class="rb-mode-desc">تمام سایت از ایندکس خارج می‌شود. فقط در موارد ضروری استفاده کنید.</div>
                                </label>

                            </div>
                        </div>
                    </div>

                    {{-- ── تنظیمات اصلی ── --}}
                    <div class="rb-two-col" style="margin-bottom:1.2rem">

                        {{-- Disallow --}}
                        <div class="rb-card">
                            <div class="rb-card-header">
                                🚫 مسیرهای ممنوع
                                <span style="font-size:.7rem;font-weight:500;color:var(--rb-muted)">Disallow</span>
                            </div>
                            <div class="rb-card-body">
                                <div id="disallow-list" class="rb-paths-wrap">
                                    @foreach($settings['disallow'] as $path)
                                        <div class="rb-path-row">
                                            <span class="rb-path-prefix">Disallow:</span>
                                            <input type="text" name="disallow[]" class="rb-path-input" value="{{ $path }}"
                                                   placeholder="/admin/" oninput="livePreview()">
                                            <button type="button" class="rb-path-del" onclick="removePath(this)" title="حذف">✕</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="rb-add-btn" onclick="addPath('disallow-list','disallow[]','Disallow:')">
                                    ＋ افزودن مسیر ممنوع
                                </button>
                                <div class="rb-suggest-chips">
                                    @foreach(['/admin/','/panel/','/login','/register','/cart','/checkout','/api/','/storage/'] as $s)
                                        <span class="rb-chip" onclick="quickAdd('disallow-list','disallow[]','Disallow:','{{ $s }}')">{{ $s }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Allow --}}
                        <div class="rb-card">
                            <div class="rb-card-header">
                                ✅ مسیرهای استثنا (اختیاری)
                                <span style="font-size:.7rem;font-weight:500;color:var(--rb-muted)">Allow</span>
                            </div>
                            <div class="rb-card-body">
                                <div id="allow-list" class="rb-paths-wrap">
                                    @if(!empty($settings['allow']))
                                        @foreach($settings['allow'] as $path)
                                            <div class="rb-path-row">
                                                <span class="rb-path-prefix">Allow:</span>
                                                <input type="text" name="allow[]" class="rb-path-input" value="{{ $path }}"
                                                       placeholder="/admin/images/" oninput="livePreview()">
                                                <button type="button" class="rb-path-del" onclick="removePath(this)" title="حذف">✕</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="rb-empty-hint">
                    <span style="color:var(--rb-muted);font-size:0.85rem;">
                        <i class="fas fa-info-circle"></i>
                        نیازی به تنظیم Allow نیست. فقط برای استثناهای Disallow استفاده کنید.
                    </span>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="rb-add-btn" onclick="addPath('allow-list','allow[]','Allow:')">
                                    ＋ افزودن استثنا
                                </button>
                                <div class="rb-suggest-chips">
                                    <span class="rb-chip" onclick="quickAdd('allow-list','allow[]','Allow:','/admin/images/')">/admin/images/</span>
                                    <span class="rb-chip" onclick="quickAdd('allow-list','allow[]','Allow:','/admin/css/')">/admin/css/</span>
                                    <span class="rb-chip" onclick="quickAdd('allow-list','allow[]','Allow:','/admin/js/')">/admin/js/</span>
                                </div>
                                <div class="rb-note" style="margin-top:10px;padding:8px 12px;background:#f8f9fa;border-radius:8px;font-size:0.8rem;color:#6c757d;">
                                    <i class="fas fa-lightbulb"></i>
                                    <strong>نکته:</strong> Allow فقط برای استثناهای Disallow استفاده می‌شود.
                                    گوگل همه آدرس‌ها را می‌بیند و Disallow مسیرهای ممنوع را مشخص می‌کند.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Crawl Delay + Sitemap ── --}}
                    <div class="rb-two-col" style="margin-bottom:1.2rem">

                        <div class="rb-card">
                            <div class="rb-card-header">⏱ Crawl Delay</div>
                            <div class="rb-card-body">
                                <div class="rb-input-wrap" style="margin-bottom:1rem">
                                    <label>تأخیر کراول برای همه ربات‌ها (ثانیه)</label>
                                    <div class="rb-slider-wrap">
                                        <input type="range" name="crawl_delay" id="crawl-delay-slider"
                                               class="rb-slider" min="0" max="60" value="{{ $settings['crawl_delay'] }}"
                                               style="--slider-pct: {{ round($settings['crawl_delay'] / 60 * 100) }}%"
                                               oninput="updateSlider(this)">
                                        <div class="rb-slider-val" id="slider-val">{{ $settings['crawl_delay'] }}s</div>
                                    </div>
                                    <div class="rb-input-hint">
                                        ۰ = بدون محدودیت &nbsp;·&nbsp; ۱–۵ = توصیه‌شده &nbsp;·&nbsp; بیش از ۱۰ = کند شدن کراول
                                    </div>
                                </div>

                                {{-- ربات‌های خاص --}}
                                <table class="rb-bots-table">
                                    <thead><tr><th>ربات</th><th>تأخیر پیش‌فرض</th><th>وضعیت</th></tr></thead>
                                    <tbody>
                                    @foreach([
                                      ['Googlebot','1s','ok'],
                                      ['Bingbot','2s','ok'],
                                      ['Yandexbot','3s','warn'],
                                      ['DuckDuckBot','2s','ok'],
                                    ] as $bot)
                                        <tr>
                                            <td><span class="rb-bot-chip">{{ $bot[0] }}</span></td>
                                            <td style="font-weight:700;color:var(--rb-text)">{{ $bot[1] }}</td>
                                            <td>
                      <span style="font-size:.7rem;font-weight:700;color:{{ $bot[2]==='ok'?'var(--rb-success)':'var(--rb-warning)' }}">
                        {{ $bot[2]==='ok'?'✅ موجود در فایل':'⚠️ اضافه می‌شود' }}
                      </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rb-card">
                            <div class="rb-card-header">🗺 Sitemap</div>
                            <div class="rb-card-body">
                                <div class="rb-input-wrap" style="margin-bottom:1rem">
                                    <label>آدرس فایل Sitemap</label>
                                    <input type="url" name="sitemap" class="rb-input" id="sitemap-input"
                                           value="{{ $settings['sitemap'] }}" placeholder="https://example.com/sitemap.xml"
                                           oninput="livePreview()">
                                    <div class="rb-input-hint">این آدرس در robots.txt معرفی می‌شود تا گوگل آن را کشف کند.</div>
                                </div>

                                <div style="margin-bottom:.8rem">
                                    <label style="font-size:.75rem;font-weight:700;color:var(--rb-muted);display:block;margin-bottom:.4rem">آدرس‌های رایج</label>
                                    @foreach([
                                      config('app.url').'/sitemap.xml',
                                      config('app.url').'/sitemap_index.xml',
                                      config('app.url').'/sitemap/products.xml',
                                      config('app.url').'/sitemap/posts.xml',
                                    ] as $sm)
                                        <div style="font-size:.72rem;font-family:monospace;color:var(--rb-primary);cursor:pointer;padding:2px 0"
                                             onclick="document.getElementById('sitemap-input').value='{{ $sm }}';livePreview()">
                                            ↗ {{ $sm }}
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ $settings['sitemap'] }}" target="_blank" class="rb-btn rb-btn-outline" style="font-size:.76rem;padding:.38rem .9rem">
                                    🔗 تست دسترسی Sitemap
                                </a>
                            </div>
                        </div>

                    </div>

                    {{-- ── Live Preview ── --}}
                    <div class="rb-card">
                        <div class="rb-card-header">
                            👁 پیش‌نمایش زنده robots.txt
                            <button type="button" onclick="copyPreview()" class="rb-btn rb-btn-ghost" style="padding:.28rem .8rem;font-size:.72rem">
                                📋 کپی
                            </button>
                        </div>
                        <div class="rb-preview-wrap">
                            <div class="rb-preview-toolbar">
                                <div class="rb-preview-dot" style="background:#ff5f57"></div>
                                <div class="rb-preview-dot" style="background:#febc2e"></div>
                                <div class="rb-preview-dot" style="background:#28c840"></div>
                                <div class="rb-preview-filename">robots.txt</div>
                            </div>
                            <div class="rb-preview-code" id="robots-preview">{{ $preview }}</div>
                        </div>
                    </div>

                    {{-- ── Action Bar ── --}}
                    <div class="rb-card">
                        <div class="rb-action-bar">
                            <button type="submit" class="rb-btn rb-btn-primary">💾 ذخیره و اعمال</button>
                            <button type="button" onclick="livePreview()" class="rb-btn rb-btn-outline">🔄 بروزرسانی پیش‌نمایش</button>
                            <a href="{{ config('app.url') }}/robots.txt" target="_blank" class="rb-btn rb-btn-ghost">🔗 فایل زنده</a>
                            <div class="rb-save-status" id="save-status"></div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="rb-toast" id="rb-toast">
        <span id="rb-toast-icon">✅</span>
        <span id="rb-toast-msg">ذخیره شد</span>
    </div>
@endsection

@push('scripts')
    <script>
        // تعریف متغیرهای سراسری
        var ROBOTS_URLS = {
            preview: '{{ route("admin.robots.preview") }}',
            update: '{{ route("admin.robots.update") }}',
        };
    </script>
    <script src="{{ asset('back/assets/js/pages/settings/robots.js') }}"></script>
@endpush
