@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/posts/details.css') }}">
@endpush


@section('content')
    <div class="app-content content" dir="rtl">
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row mb-1">
                <div class="col-12">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item">مقالات</li>
                        <li class="breadcrumb-item active">{{ Str::limit($post->title, 45) }}</li>
                    </ol>
                </div>
            </div>

            <div class="content-body">

                {{-- ══ HERO ══ --}}
                <div class="ar-hero">

                    <div class="ar-hero-thumb">
                        @if($post->image)
                            <img src="{{ asset($post->image) }}" alt="{{ $post->title }}">
                        @else
                            <div class="ar-hero-no-img">📄</div>
                        @endif
                        <span class="ar-post-type-badge">
            {{ match($post->post_type) {
              'text'    => '📝 مقاله',
              'video'   => '🎬 ویدیو',
              'podcast' => '🎙 پادکست',
              default   => $post->post_type
            } }}
          </span>
                        @php
                            $statusMap = [
                              'end'     => ['label'=>'✅ منتشر شده',  'cls'=>'ar-status-end'],
                              'draft'   => ['label'=>'⏸ پیش‌نویس',   'cls'=>'ar-status-draft'],
                              'pending' => ['label'=>'⏳ در انتظار',   'cls'=>'ar-status-pending'],
                            ];
                            $st = $statusMap[$post->status] ?? ['label'=>$post->status,'cls'=>'ar-status-draft'];
                        @endphp
                        <span class="ar-status-badge {{ $st['cls'] }}">{{ $st['label'] }}</span>
                    </div>

                    <div class="ar-hero-info">
                        <div class="ar-eyebrow">
                            #{{ $post->id }}
                            &middot; {{ $post->lang === 'fa' ? '🇮🇷 فارسی' : '🌐 ' . strtoupper($post->lang) }}
                            @if($post->is_editor_pick) &middot; <span style="color:#ff9f43">⭐ انتخاب سردبیر</span> @endif
                            @if($post->allow_comments) &middot; 💬 نظرات فعال @else &middot; 🔇 نظرات غیرفعال @endif
                        </div>

                        <h1 class="ar-post-title">{{ $post->title }}</h1>

                        @if($post->summary)
                            <div class="ar-content-preview">{{ Str::limit($post->summary, 200) }}</div>
                        @endif

                        <div class="ar-meta-row">
                            @if($post->category)
                                <span class="ar-pill">📂 {{ $post->category->name }}</span>
                            @endif
                            @if($post->admin)
                                <span class="ar-pill">✍️ {{ $post->admin->name }}</span>
                            @endif
                            @if($contentStats['reading_time'])
                                <span class="ar-pill accent">⏱ {{ $contentStats['reading_time'] }} دقیقه مطالعه</span>
                            @endif
                            @if($post->publish_date)
                                <span class="ar-pill">📅 {{ jdate($post->publish_date)->format('Y/m/d') }}</span>
                            @endif
                            @if($post->source)
                                <span class="ar-pill">🔗 منبع: {{ $post->source }}</span>
                            @endif
                        </div>

                        {{-- رسانه‌ها --}}
                        @if($post->video_url || $post->podcast_url)
                            <div style="margin-bottom:.8rem">
                                @if($post->video_url)
                                    <a href="{{ $post->video_url }}" target="_blank" class="ar-media-chip">🎬 مشاهده ویدیو</a>
                                @endif
                                @if($post->podcast_url)
                                    <a href="{{ $post->podcast_url }}" target="_blank" class="ar-media-chip">🎙 پادکست</a>
                                @endif
                            </div>
                        @endif

                        <div class="ar-hero-actions">
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="ar-btn ar-btn-primary">✏️ ویرایش</a>
                            <a href="{{ url('/blog/' . $post->slug) }}" target="_blank" class="ar-btn ar-btn-outline">🔗 مشاهده در سایت</a>
                            @if($post->status !== 'end')
                                <a href="{{ route('admin.posts.publish', $post->id) }}" class="ar-btn ar-btn-success">🚀 انتشار</a>
                            @endif
                        </div>
                    </div>

                </div><!-- /hero -->


                {{-- ══ Stats ══ --}}
                <div class="ar-stats-grid">
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#7367f015;color:#7367f0">👁</div>
                        <div><div class="ar-stat-value">{{ number_format($contentStats['total_views']) }}</div><div class="ar-stat-label">بازدید</div></div>
                    </div>
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#28c76f15;color:#28c76f">💬</div>
                        <div><div class="ar-stat-value" style="color:#28c76f">{{ $contentStats['comments_count'] }}</div><div class="ar-stat-label">نظر</div></div>
                    </div>
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
                    <div class="ar-stat-box">
                        <div class="ar-stat-icon" style="background:#ff9f4315;color:#ff9f43">🎬</div>
                        <div>
                            <div class="ar-stat-value" style="color:{{ $contentStats['has_video'] ? '#28c76f' : '#ea5455' }}">
                                {{ $contentStats['has_video'] ? 'دارد' : 'ندارد' }}
                            </div>
                            <div class="ar-stat-label">ویدیو</div>
                        </div>
                    </div>
                </div>


                {{-- ══ دو ستون: اطلاعات + خوانایی ══ --}}
                <div class="ar-two-col">

                    <div class="ar-card">
                        <div class="ar-card-header">📋 اطلاعات مقاله</div>
                        <div class="ar-card-body">
                            <table class="ar-info-table">
                                <tr><td>شناسه</td><td><code>#{{ $post->id }}</code></td></tr>
                                <tr><td>نوع محتوا</td><td>{{ $post->post_type }}</td></tr>
                                <tr><td>زبان</td><td>{{ $post->lang }}</td></tr>
                                <tr><td>نویسنده</td><td>{{ $post->admin->name ?? '—' }}</td></tr>
                                <tr><td>ایجادشده توسط</td><td>{{ $post->created_by }}</td></tr>
                                <tr><td>وضعیت</td><td>{{ $st['label'] }}</td></tr>
                                <tr><td>انتخاب سردبیر</td><td>{{ $post->is_editor_pick ? '✅ بله' : '❌ خیر' }}</td></tr>
                                <tr><td>اجازه نظر</td><td>{{ $post->allow_comments ? '✅ فعال' : '❌ غیرفعال' }}</td></tr>
                                <tr><td>منبع</td><td>{{ $post->source ?? '—' }}</td></tr>
                                <tr><td>تاریخ ایجاد</td><td>{{ jdate($post->created_at)->format('Y/m/d H:i') }}</td></tr>
                                <tr><td>آخرین بروزرسانی</td><td>{{ jdate($post->updated_at)->format('Y/m/d H:i') }}</td></tr>
                                @if($post->publish_date)
                                    <tr><td>تاریخ انتشار</td><td>{{ jdate($post->publish_date)->format('Y/m/d') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="ar-card">
                        <div class="ar-card-header">📊 تحلیل محتوا</div>
                        <div class="ar-card-body">

                            <div class="ar-reading-stats">
                                <div class="ar-rstat">کلمات: <span>{{ number_format($contentStats['word_count']) }}</span></div>
                                <div class="ar-rstat">کاراکتر: <span>{{ number_format($contentStats['char_count']) }}</span></div>
                                <div class="ar-rstat">زمان مطالعه: <span>{{ $contentStats['reading_time'] }} دقیقه</span></div>
                            </div>

                            {{-- نوار خوانایی --}}
                            @php
                                $wc = $contentStats['word_count'];
                                $wcPct = min(100, (int)(($wc / 1500) * 100));
                                $wcColor = $wc >= 800 ? '#28c76f' : ($wc >= 300 ? '#ff9f43' : '#ea5455');
                            @endphp
                            <div style="margin-bottom:1rem">
                                <div style="display:flex;justify-content:space-between;font-size:.74rem;color:var(--ar-muted);margin-bottom:.3rem">
                                    <span>تعداد کلمات</span>
                                    <span style="color:{{ $wcColor }};font-weight:700">{{ $wc }} / ۱۵۰۰+ ایده‌آل</span>
                                </div>
                                <div class="ar-readability-bar">
                                    <div class="ar-readability-fill" style="width:{{ $wcPct }}%;background:{{ $wcColor }}"></div>
                                </div>
                            </div>

                            <table class="ar-info-table">
                                <tr>
                                    <td>تصاویر در متن</td>
                                    <td>
                  <span style="color:{{ $contentStats['images_in_body'] > 0 ? '#28c76f' : '#ea5455' }};font-weight:700">
                    {{ $contentStats['images_in_body'] }}
                      {{ $contentStats['images_in_body'] === 0 ? '⚠️ حداقل ۱ تصویر اضافه کنید' : '' }}
                  </span>
                                    </td>
                                </tr>
                                <tr><td>لینک‌ها</td><td>{{ $contentStats['links_in_body'] }} لینک</td></tr>
                                <tr>
                                    <td>ویدیو</td>
                                    <td>{{ $contentStats['has_video'] ? '✅ دارد' : '— ندارد' }}</td>
                                </tr>
                                <tr>
                                    <td>پادکست</td>
                                    <td>{{ $contentStats['has_podcast'] ? '✅ دارد' : '— ندارد' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>


                {{-- ══ تگ‌ها ══ --}}
                @if($post->tags && $post->tags->count())
                    <div class="ar-card">
                        <div class="ar-card-header">🏷 تگ‌ها ({{ $post->tags->count() }})</div>
                        <div class="ar-card-body ar-tags-wrap">
                            @foreach($post->tags as $tag)
                                <span class="ar-tag">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="ar-card">
                        <div class="ar-card-header" style="color:var(--ar-warning)">🏷 تگ‌ها — ⚠️ بدون تگ</div>
                        <div class="ar-card-body">
                            <p class="ar-empty-note">هیچ تگی تعریف نشده. اضافه کردن تگ‌های مرتبط به SEO کمک می‌کند.</p>
                        </div>
                    </div>
                @endif


                {{-- ══ نظرات ══ --}}
                @if($post->comments && $post->comments->count())
                    <div class="ar-card">
                        <div class="ar-card-header">
                            💬 آخرین نظرات
                            <a href="{{ route('admin.comments.posts') }}" class="ar-btn ar-btn-outline" style="padding:.25rem .7rem;font-size:.74rem">همه نظرات</a>
                        </div>
                        <div class="ar-card-body">
                            @foreach($post->comments as $comment)
                                <div class="ar-comment">
                                    <div class="ar-comment-head">
                                        <span class="ar-comment-author">{{ $comment->user->name ?? 'ناشناس' }}</span>
                                        <span class="ar-comment-date">{{ jdate($comment->created_at)->format('Y/m/d') }}</span>
                                    </div>
                                    <p class="ar-comment-body">{{ Str::limit($comment->body, 150) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- ══════════════════════════════════════════════════════════
                     🔍 پنل SEO کامل
                ══════════════════════════════════════════════════════════ --}}
                <div class="ar-seo-panel">

                    <div class="ar-seo-header">
                        <div class="ar-seo-title-wrap">
                            <span>🔍 تحلیل کامل سئو مقاله</span>
                            <span class="ar-seo-subtitle">
              {{ count($seoIssues) }} مشکل بحرانی &nbsp;·&nbsp;
              {{ count($seoWarnings) }} هشدار &nbsp;·&nbsp;
              {{ count($seoGood) }} مورد تأیید
            </span>
                        </div>
                        <div class="ar-score-wrap">
                            <div class="ar-score-ring {{ $seoScore >= 75 ? 'good' : ($seoScore >= 45 ? 'warn' : 'bad') }}">
                                {{ $seoScore }}
                            </div>
                            <div>
                                <div class="ar-score-label">امتیاز سئو</div>
                                <div class="ar-score-label">از ۱۰۰</div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="ar-seo-tabs">
                        <div class="ar-seo-tab active" onclick="arSeoTab(this,'issues')">⚠️ مشکلات</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'tags')">🏷 تگ‌های HTML</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'links')">🔗 لینک‌ها</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'schema')">📐 Schema & OG</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'serp')">👁 SERP Preview</div>
                        <div class="ar-seo-tab" onclick="arSeoTab(this,'meta')">📄 متا</div>
                    </div>

                    {{-- Tab: مشکلات --}}
                    <div class="ar-seo-tab-content active" id="seo-tab-issues">
                        @if(count($seoIssues))
                            <div class="ar-seo-section">
                                <div class="ar-seo-section-title err">🔴 مشکلات بحرانی ({{ count($seoIssues) }})</div>
                                @foreach($seoIssues as $issue)
                                    <div class="ar-seo-row seo-issue">
                                        <div class="ar-seo-field">{{ $issue['field'] }}</div>
                                        <div class="ar-seo-msg">{{ $issue['msg'] }}</div>
                                        <div class="ar-seo-fix">💡 {{ $issue['fix'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($seoWarnings))
                            <div class="ar-seo-section">
                                <div class="ar-seo-section-title warn">🟡 هشدارها ({{ count($seoWarnings) }})</div>
                                @foreach($seoWarnings as $w)
                                    <div class="ar-seo-row seo-warning">
                                        <div class="ar-seo-field">{{ $w['field'] }}</div>
                                        <div class="ar-seo-msg">{{ $w['msg'] }}</div>
                                        <div class="ar-seo-fix">💡 {{ $w['fix'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($seoGood))
                            <div class="ar-seo-section">
                                <div class="ar-seo-section-title ok">🟢 موارد تأیید شده ({{ count($seoGood) }})</div>
                                @foreach($seoGood as $g)
                                    <div class="ar-seo-row seo-good">✔ {{ $g }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if(!count($seoIssues) && !count($seoWarnings))
                            <div style="text-align:center;padding:2rem;color:var(--ar-success)">
                                <div style="font-size:2rem">🎉</div>
                                <p style="font-weight:700">مشکل SEO پیدا نشد!</p>
                            </div>
                        @endif
                    </div>

                    {{-- Tab: تگ‌های HTML --}}
                    <div class="ar-seo-tab-content" id="seo-tab-tags">
                        @if(count($missingTags))
                            <div class="ar-seo-section">
                                <div class="ar-seo-section-title warn">تگ‌های ضروری که در محتوا استفاده نشده‌اند</div>
                                <div class="ar-missing-tags">
                                    @foreach($missingTags as $mt)
                                        <div class="ar-missing-tag">
                                            <code>&lt;{{ $mt['tag'] }}&gt;</code>
                                            <span>{{ $mt['reason'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div style="color:var(--ar-success);padding:1rem;font-weight:700">✅ همه تگ‌های ضروری در محتوا وجود دارند.</div>
                        @endif

                        <div class="ar-seo-section" style="margin-top:1rem">
                            <div class="ar-seo-section-title">راهنمای تگ‌های HTML برای مقاله سئو‌پسند</div>
                            <table class="ar-info-table">
                                <tr><td><code>&lt;H1&gt;</code></td><td>فقط یکی — عنوان اصلی مقاله باید شامل کلیدواژه اصلی باشد</td></tr>
                                <tr><td><code>&lt;H2&gt;</code></td><td>بخش‌های اصلی مقاله — هر بخش یک H2</td></tr>
                                <tr><td><code>&lt;H3&gt;</code></td><td>زیرعنوان‌های هر بخش</td></tr>
                                <tr><td><code>&lt;UL/OL&gt;</code></td><td>لیست‌ها — شانس نمایش در Featured Snippet</td></tr>
                                <tr><td><code>&lt;STRONG&gt;</code></td><td>کلیدواژه‌های مهم را bold کنید</td></tr>
                                <tr><td><code>&lt;IMG alt=""&gt;</code></td><td>هر تصویر باید alt توصیفی داشته باشد</td></tr>
                                <tr><td><code>&lt;BLOCKQUOTE&gt;</code></td><td>نقل‌قول‌های معتبر اعتبار محتوا را بالا می‌برد</td></tr>
                                <tr><td><code>&lt;TABLE&gt;</code></td><td>جداول مقایسه‌ای — Rich Snippet</td></tr>
                            </table>
                        </div>
                    </div>

                    {{-- Tab: لینک‌ها --}}
                    <div class="ar-seo-tab-content" id="seo-tab-links">
                        <div class="ar-seo-section">
                            <div class="ar-seo-section-title">🔗 لینک‌های داخلی ({{ count($internalLinks) }})</div>
                            @if(count($internalLinks))
                                @foreach($internalLinks as $link)
                                    <div class="ar-link-row internal">
                                        <span class="ar-link-text">{{ $link['text'] ?: 'بدون متن anchor' }}</span>
                                        <a href="{{ $link['url'] }}" target="_blank" class="ar-link-url">{{ Str::limit($link['url'], 70) }}</a>
                                    </div>
                                @endforeach
                            @else
                                <p class="ar-empty-note">لینک داخلی پیدا نشد. حداقل ۲–۳ لینک به مقالات و صفحات مرتبط اضافه کنید.</p>
                            @endif
                        </div>

                        <div class="ar-seo-section">
                            <div class="ar-seo-section-title">🌐 لینک‌های خارجی ({{ count($externalLinks) }})</div>
                            @if(count($externalLinks))
                                @foreach($externalLinks as $link)
                                    <div class="ar-link-row external {{ $link['nofollow'] ? 'nofollow' : '' }}">
                                        <span class="ar-link-text">{{ $link['text'] ?: 'بدون متن' }}</span>
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="ar-link-url">{{ Str::limit($link['url'], 65) }}</a>
                                        @if($link['nofollow'])
                                            <span class="ar-nofollow-badge">nofollow</span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="ar-empty-note">لینک خارجی معتبر اضافه کنید (ویکی‌پدیا، .gov، .edu یا سایت سازنده محتوا).</p>
                            @endif
                        </div>
                    </div>

                    {{-- Tab: Schema & OG --}}
                    <div class="ar-seo-tab-content" id="seo-tab-schema">
                        <div class="ar-seo-section">
                            <div class="ar-seo-section-title">📐 Schema Article / BlogPosting</div>
                            <div class="ar-check-grid">
                                @foreach($schemaChecks as $sc)
                                    <div class="ar-check-item {{ $sc['ok'] ? 'ok' : 'missing' }}">
                                        {{ $sc['ok'] ? '✅' : '❌' }} {{ $sc['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="ar-seo-section">
                            <div class="ar-seo-section-title">📣 Open Graph & Twitter Card</div>
                            <div class="ar-check-grid">
                                @foreach($ogChecks as $og)
                                    <div class="ar-check-item {{ $og['ok'] ? 'ok' : 'missing' }}">
                                        {{ $og['ok'] ? '✅' : '❌' }} {{ $og['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tab: SERP Preview --}}
                    <div class="ar-seo-tab-content" id="seo-tab-serp">
                        <p style="font-size:.78rem;color:var(--ar-muted);margin-bottom:.8rem">پیش‌نمایش تقریبی نمایش این مقاله در نتایج جستجوی گوگل:</p>
                        <div class="ar-serp">
                            <div class="ar-serp-url">{{ url('/blog/' . $post->slug) }}</div>
                            <div class="ar-serp-title">{{ Str::limit($post->meta_title ?: $post->title, 60) }}</div>
                            <div class="ar-serp-desc">
                                {{ Str::limit($post->meta_description ?: $post->summary ?: 'توضیحات متا تعریف نشده — گوگل خودش متنی از محتوا انتخاب می‌کند که ممکن است مناسب نباشد.', 160) }}
                            </div>
                        </div>
                        <p style="font-size:.72rem;color:var(--ar-muted);margin-top:.5rem">* این پیش‌نمایش تقریبی است. نمایش واقعی در گوگل ممکن است متفاوت باشد.</p>
                    </div>

                    {{-- Tab: متا --}}
                    <div class="ar-seo-tab-content" id="seo-tab-meta">
                        <table class="ar-info-table">
                            <tr>
                                <td>Meta Title</td>
                                <td>
                                    {{ $post->meta_title ?: '—' }}
                                    @php $mtl = mb_strlen($post->meta_title ?? '') @endphp
                                    <span class="ar-char {{ $mtl >= 50 && $mtl <= 65 ? 'ok' : ($mtl > 0 ? 'warn' : 'bad') }}">{{ $mtl }} کاراکتر</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Meta Description</td>
                                <td>
                                    {{ $post->meta_description ?: '—' }}
                                    @php $mdl = mb_strlen($post->meta_description ?? '') @endphp
                                    <span class="ar-char {{ $mdl >= 130 && $mdl <= 165 ? 'ok' : ($mdl > 0 ? 'warn' : 'bad') }}">{{ $mdl }} کاراکتر</span>
                                </td>
                            </tr>
                            <tr><td>Slug</td><td><code>{{ $post->slug ?: '—' }}</code></td></tr>
                            <tr><td>تصویر شاخص</td><td>{{ $post->image ? '✅ دارد' : '❌ ندارد' }}</td></tr>
                            <tr><td>تاریخ انتشار (Schema)</td><td>{{ $post->publish_date ?? '—' }}</td></tr>
                            <tr><td>منبع</td><td>{{ $post->source ?? '—' }}</td></tr>
                        </table>
                    </div>

                </div><!-- /seo-panel -->

            </div><!-- /content-body -->
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
