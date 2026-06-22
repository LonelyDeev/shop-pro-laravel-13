@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/pages/details.css') }}">
@endpush

@section('content')
    <div class="app-content content" dir="rtl">
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row mb-1">
                <div class="col-12">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item">صفحات</li>
                        <li class="breadcrumb-item active">{{ Str::limit($page->title, 45) }}</li>
                    </ol>
                </div>
            </div>

            <div class="content-body">

                {{-- ══ HERO ══ --}}
                <div class="ar-hero">
                    <div class="ar-hero-info">
                        <div class="ar-eyebrow">
                            #{{ $page->id }}
                        </div>

                        <h1 class="ar-post-title">{{ $page->title }}</h1>

                        <div class="ar-meta-row">
                            @if($contentStats['reading_time'])
                                <span class="ar-pill accent">⏱ {{ $contentStats['reading_time'] }} دقیقه مطالعه</span>
                            @endif
                            <span class="ar-pill">📅 ایجاد: {{ jdate($page->created_at)->format('Y/m/d') }}</span>
                            <span class="ar-pill">🔄 آخرین بروزرسانی: {{ jdate($page->updated_at)->format('Y/m/d') }}</span>
                            <span class="ar-pill {{ $page->published ? 'accent' : '' }}">
                                {{ $page->published ? '✅ منتشر شده' : '⏸ پیش‌نویس' }}
                            </span>
                        </div>

                        <div class="ar-hero-actions">
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="ar-btn ar-btn-primary">✏️ ویرایش</a>
                            @if($page->published)
                                <a href="{{ url('/page/' . $page->slug) }}" target="_blank" class="ar-btn ar-btn-outline">🔗 مشاهده در سایت</a>
                            @endif
                            @if(!$page->published)
                                <a href="{{ route('admin.pages.publish', $page->id) }}" class="ar-btn ar-btn-success">🚀 انتشار</a>
                            @endif
                        </div>
                    </div>
                </div><!-- /hero -->

                {{-- ══ Stats ══ --}}
                <div class="ar-stats-grid">
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#00cfe815;color:#00cfe8">📝</div>
                        <div><div class="ar-stat-value" style="color:#00cfe8">{{ number_format($contentStats['word_count']) }}</div><div class="ar-stat-label">کلمه</div></div>
                    </div>
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#ff9f4315;color:#ff9f43">⏱</div>
                        <div><div class="ar-stat-value" style="color:#ff9f43">{{ $contentStats['reading_time'] }} دقیقه</div><div class="ar-stat-label">زمان مطالعه</div></div>
                    </div>
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#ea545515;color:#ea5455">🖼</div>
                        <div><div class="ar-stat-value">{{ $contentStats['images_in_body'] }}</div><div class="ar-stat-label">تصویر در متن</div></div>
                    </div>
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#7367f015;color:#7367f0">🔗</div>
                        <div><div class="ar-stat-value">{{ $contentStats['links_in_body'] }}</div><div class="ar-stat-label">لینک در متن</div></div>
                    </div>
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#28c76f15;color:#28c76f">📊</div>
                        <div><div class="ar-stat-value">{{ number_format($contentStats['char_count']) }}</div><div class="ar-stat-label">کاراکتر</div></div>
                    </div>
                </div>

                {{-- ══ اطلاعات صفحه ══ --}}
                <div class="ar-card">
                    <div class="ar-card-header">📋 اطلاعات صفحه</div>
                    <div class="ar-card-body">
                        <table class="ar-info-table">
                            <tr><td style="width:160px">شناسه</td><td><code>#{{ $page->id }}</code></td></tr>
                            <tr><td>عنوان</td><td>{{ $page->title }}</td></tr>
                            <tr><td>Slug</td><td><code>{{ $page->slug }}</code></td></tr>
                            <tr><td>وضعیت</td><td>{{ $page->published ? '✅ منتشر شده' : '⏸ پیش‌نویس' }}</td></tr>
                            <tr><td>تاریخ ایجاد</td><td>{{ jdate($page->created_at)->format('Y/m/d H:i') }}</td></tr>
                            <tr><td>آخرین بروزرسانی</td><td>{{ jdate($page->updated_at)->format('Y/m/d H:i') }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- ══ محتوای صفحه ══ --}}
                <div class="ar-card">
                    <div class="ar-card-header">📄 محتوای صفحه</div>
                    <div class="ar-card-body">
                        @if($page->content)
                            <div class="ar-content-preview">
                                {!! Str::limit(strip_tags($page->content), 500) !!}
                                @if(strlen(strip_tags($page->content)) > 500)
                                    <a href="#" onclick="event.preventDefault();document.getElementById('full-content').style.display='block';this.style.display='none'">[نمایش کامل]</a>
                                    <div id="full-content" style="display:none; margin-top:1rem">
                                        {!! $page->content !!}
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="ar-empty-note">محتوایی برای این صفحه تعریف نشده است.</p>
                        @endif
                    </div>
                </div>

                {{-- ══ پنل SEO ══ --}}
                <div class="ar-seo-panel">
                    <div class="ar-seo-header">
                        <div class="ar-seo-title-wrap">
                            <span>🔍 تحلیل سئوی صفحه</span>
                            <span class="ar-seo-subtitle">
                                {{ count($seoIssues) }} مشکل &nbsp;·&nbsp;
                                {{ count($seoWarnings) }} هشدار &nbsp;·&nbsp;
                                {{ count($seoGood) }} تأیید
                            </span>
                        </div>
                        <div class="ar-score-wrap">
                            <div class="ar-score-ring {{ $seoScore >= 75 ? 'good' : ($seoScore >= 45 ? 'warn' : 'bad') }}">
                                {{ $seoScore }}
                            </div>
                            <div class="ar-score-label">امتیاز سئو</div>
                        </div>
                    </div>

                    <div class="ar-seo-tabs">
                        <div class="ar-seo-tab active" onclick="arSeoTab(this,'issues')">⚠️ مشکلات</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'tags')">🏷 تگ‌های HTML</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'links')">🔗 لینک‌ها</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'meta')">📄 متا</div>
                    </div>

                    {{-- Tab: مشکلات --}}
                    <div class="ar-seo-tab-content active" id="seo-tab-issues">
                        @if(count($seoIssues))
                            @foreach($seoIssues as $issue)
                                <div class="ar-seo-row seo-issue">
                                    <div class="ar-seo-field">{{ $issue['field'] }}</div>
                                    <div class="ar-seo-msg">{{ $issue['msg'] }}</div>
                                    <div class="ar-seo-fix">💡 {{ $issue['fix'] }}</div>
                                </div>
                            @endforeach
                        @endif

                        @if(count($seoWarnings))
                            @foreach($seoWarnings as $w)
                                <div class="ar-seo-row seo-warning">
                                    <div class="ar-seo-field">{{ $w['field'] }}</div>
                                    <div class="ar-seo-msg">{{ $w['msg'] }}</div>
                                    <div class="ar-seo-fix">💡 {{ $w['fix'] }}</div>
                                </div>
                            @endforeach
                        @endif

                        @if(count($seoGood))
                            @foreach($seoGood as $g)
                                <div class="ar-seo-row seo-good">✔ {{ $g }}</div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Tab: تگ‌های HTML --}}
                    <div class="ar-seo-tab-content" id="seo-tab-tags">
                        @if(count($missingTags))
                            @foreach($missingTags as $mt)
                                <div class="ar-missing-tag">
                                    <code>&lt;{{ $mt['tag'] }}&gt;</code>
                                    <span>{{ $mt['reason'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div style="color:var(--ar-success);padding:1rem">✅ همه تگ‌های ضروری وجود دارند.</div>
                        @endif
                    </div>

                    {{-- Tab: لینک‌ها --}}
                    <div class="ar-seo-tab-content" id="seo-tab-links">
                        <h4>🔗 لینک‌های داخلی ({{ count($internalLinks) }})</h4>
                        @forelse($internalLinks as $link)
                            <div class="ar-link-row">
                                <span>{{ $link['text'] ?: 'بدون متن' }}</span>
                                <a href="{{ $link['url'] }}" target="_blank">{{ Str::limit($link['url'], 70) }}</a>
                            </div>
                        @empty
                            <p>لینک داخلی یافت نشد.</p>
                        @endforelse

                        <h4 style="margin-top:1rem">🌐 لینک‌های خارجی ({{ count($externalLinks) }})</h4>
                        @forelse($externalLinks as $link)
                            <div class="ar-link-row">
                                <span>{{ $link['text'] ?: 'بدون متن' }}</span>
                                <a href="{{ $link['url'] }}" target="_blank">{{ Str::limit($link['url'], 70) }}</a>
                                @if($link['nofollow'])<span class="ar-nofollow-badge">nofollow</span>@endif
                            </div>
                        @empty
                            <p>لینک خارجی یافت نشد.</p>
                        @endforelse
                    </div>

                    {{-- Tab: متا --}}
                    <div class="ar-seo-tab-content" id="seo-tab-meta">
                        <table class="ar-info-table">
                            <tr><td>Meta Title</td><td>{{ $page->meta_title ?: '—' }} @php $mtl = mb_strlen($page->meta_title ?? '') @endphp <span class="ar-char">{{ $mtl }} کاراکتر</span></td></tr>
                            <tr><td>Meta Description</td><td>{{ $page->meta_description ?: '—' }} @php $mdl = mb_strlen($page->meta_description ?? '') @endphp <span class="ar-char">{{ $mdl }} کاراکتر</span></td></tr>
                            <tr><td>Slug</td><td><code>{{ $page->slug ?: '—' }}</code></td></tr>
                            <tr><td>تاریخ بروزرسانی</td><td>{{ jdate($page->updated_at)->format('Y/m/d H:i') }}</td></tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function arSeoTab(el, tab) {
            document.querySelectorAll('.ar-seo-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.ar-seo-tab-content').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('seo-tab-' + tab).classList.add('active');
        }
    </script>
@endpush
