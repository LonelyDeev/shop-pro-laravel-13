@extends('back.layouts.master')

@section('content')
    <div class="app-content content" dir="rtl">
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row mb-1">
                <div class="col-12">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item">محصولات</li>
                        <li class="breadcrumb-item active">{{ Str::limit($product->title, 40) }}</li>
                    </ol>
                </div>
            </div>

            <div class="content-body">

                {{-- ══════════════════════════════════════════════════════════
                     HERO: تصویر + اطلاعات پایه + دکمه‌ها
                ══════════════════════════════════════════════════════════ --}}
                <div class="ps-hero-card">

                    <div class="ps-hero-img-wrap">
                        <img src="{{ asset($product->image) }}"
                             alt="{{ $product->image_alt ?: $product->title }}"
                             onerror="this.src='{{ asset('back/assets/images/pages/no-image.jpg') }}'">
                        <span class="ps-status-badge ps-status-{{ $product->status === 'Accept' ? 'active' : 'draft' }}">
            {{ $product->status === 'Accept' ? '✅ منتشر شده' : '⏸ پیش‌نویس' }}
          </span>
                    </div>

                    <div class="ps-hero-info">
                        <div class="ps-eyebrow">{{ $product->product_id }} &middot; {{ $product->type === 'physical' ? '📦 محصول فیزیکی' : '💾 دیجیتال' }}</div>
                        <h1 class="ps-product-title">{{ $product->title }}</h1>
                        @if($product->title_en)
                            <p class="ps-product-title-en">{{ $product->title_en }}</p>
                        @endif

                        <div class="ps-meta-row">
                            @if($product->brand)
                                <span class="ps-meta-pill">🏷 {{ $product->brand->name }}</span>
                            @endif
                            @if($product->category)
                                <span class="ps-meta-pill">📂 {{ $product->category->name }}</span>
                            @endif
                            <span class="ps-meta-pill">⚖️ {{ number_format($product->weight) }} گرم</span>
                            <span class="ps-meta-pill">📦 {{ $product->shipping_nature === 'small' ? 'پسنده کوچک' : $product->shipping_nature }}</span>
                        </div>

                        {{-- امتیاز و بازدید --}}
                        <div class="ps-rating-row">
                            <div class="ps-star-score">
                                @for($i = 1; $i <= 5; $i++)
                                    <span style="color: {{ $i <= round($salesStats['avg_rating']) ? '#ff9f43' : '#ddd' }}">★</span>
                                @endfor
                                <span class="ps-rating-num">{{ number_format($salesStats['avg_rating'], 1) }}</span>
                                <span class="ps-rating-count">({{ $salesStats['reviews_count'] }} نظر)</span>
                            </div>
                            <span class="ps-meta-pill">👁 {{ number_format($product->view) }} بازدید</span>
                        </div>

                        <div class="ps-hero-actions">
                            <a href="{{ route('admin.products.edit', $product->slug) }}" class="ps-btn ps-btn-primary">✏️ ویرایش محصول</a>
                            <a href="{{ url('/products/' . $product->slug) }}" target="_blank" class="ps-btn ps-btn-outline">🔗 مشاهده در سایت</a>
                            <a href="{{ route('admin.orders.index', ['product_id' => $product->id]) }}" class="ps-btn ps-btn-outline">📋 سفارشات</a>
                        </div>
                    </div>

                </div><!-- /hero -->


                {{-- ══════════════════════════════════════════════════════════
                     ردیف آمار سریع
                ══════════════════════════════════════════════════════════ --}}
                <div class="ps-stats-grid">
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#7367f015; color:#7367f0">📦</div>
                        <div>
                            <div class="ps-stat-value">{{ number_format($salesStats['total_orders']) }}</div>
                            <div class="ps-stat-label">کل سفارشات</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#28c76f15; color:#28c76f">✅</div>
                        <div>
                            <div class="ps-stat-value" style="color:#28c76f">{{ number_format($salesStats['paid_orders']) }}</div>
                            <div class="ps-stat-label">سفارش موفق</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#00cfe815; color:#00cfe8">💰</div>
                        <div>
                            <div class="ps-stat-value" style="color:#00cfe8">{{ number_format($salesStats['total_revenue']) }}</div>
                            <div class="ps-stat-label">درآمد (تومان)</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#ff9f4315; color:#ff9f43">📅</div>
                        <div>
                            <div class="ps-stat-value" style="color:#ff9f43">{{ $salesStats['today_orders'] }}</div>
                            <div class="ps-stat-label">سفارش امروز</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#ea545515; color:#ea5455">📆</div>
                        <div>
                            <div class="ps-stat-value">{{ $salesStats['week_orders'] }}</div>
                            <div class="ps-stat-label">سفارش این هفته</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#7367f015; color:#7367f0">🗓</div>
                        <div>
                            <div class="ps-stat-value">{{ $salesStats['month_orders'] }}</div>
                            <div class="ps-stat-label">سفارش این ماه</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#28c76f15; color:#28c76f">🏪</div>
                        <div>
                            <div class="ps-stat-value">{{ number_format($salesStats['total_stock']) }}</div>
                            <div class="ps-stat-label">موجودی انبار</div>
                        </div>
                    </div>
                    <div class="ps-stat-box">
                        <div class="ps-stat-icon" style="background:#ff9f4315; color:#ff9f43">⭐</div>
                        <div>
                            <div class="ps-stat-value">{{ number_format($salesStats['avg_rating'], 1) }}</div>
                            <div class="ps-stat-label">میانگین امتیاز</div>
                        </div>
                    </div>
                </div>


                {{-- ══════════════════════════════════════════════════════════
                     دو ستون: قیمت‌ها + اطلاعات اضافی
                ══════════════════════════════════════════════════════════ --}}
                <div class="ps-two-col">

                    {{-- قیمت‌ها --}}
                    <div class="ps-card">
                        <div class="ps-card-header">💲 قیمت‌ها و تنوع‌ها</div>
                        <div class="ps-card-body">
                            @forelse($product->prices as $price)
                                <div class="ps-price-row">
                                    <div class="ps-price-info">
                                        <div class="ps-attributes-list">
                                            @php
                                                $attributes = $price->attributes;
                                            @endphp

                                            @if($attributes && $attributes->count() > 0)
                                                <div class="ps-attributes">
                                                    @foreach($attributes as $attr)
                                                        <span class="ps-attr-badge">
                                        @if($attr->group && $attr->group->type == 'color')
                                                                <span class="ps-color-dot" style="background: {{ $attr->value ?? '#cccccc' }};"></span>
                                                            @endif
                                                            {{ $attr->name }}
                                                            @if($attr->value && $attr->group && $attr->group->type != 'color')
                                                                : {{ $attr->value }}
                                                            @endif
                                    </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="ps-attr-default">قیمت پایه</span>
                                            @endif
                                        </div>

                                        @if($price->sku || $price->product_id)
                                            <div class="ps-sku">
                                                SKU: {{ $price->sku ?? $price->product_id ?? '---' }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ps-price-details">
                                        <div class="ps-price-values">
                                            @if($price->discount_price && $price->discount_price < $price->price)
                                                <del class="ps-old-price">{{ number_format($price->price) }}</del>
                                                <span class="ps-new-price">{{ number_format($price->discount_price) }} تومان</span>
                                                @if($price->discount)
                                                    <span class="ps-discount-badge">{{ $price->discount }}% تخفیف</span>
                                                @endif
                                            @else
                                                <span class="ps-new-price">{{ number_format($price->price) }} تومان</span>
                                            @endif
                                        </div>

                                        <div class="ps-stock-info">
                                            <div class="ps-stock-chip {{ $price->stock > 5 ? 'in' : ($price->stock > 0 ? 'low' : 'out') }}">
                                                @if($price->stock > 5)
                                                    ✓ موجود ({!! number_format($price->stock) !!})
                                                @elseif($price->stock > 0)
                                                    ⚠️ موجودی محدود ({!! number_format($price->stock) !!})
                                                @else
                                                    ✗ ناموجود
                                                @endif
                                            </div>

                                            @if($price->sold_count > 0)
                                                <div class="ps-sold-count">
                                                    <i class="fas fa-chart-line"></i>
                                                    {{ number_format($price->sold_count) }} فروش
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="ps-empty">قیمتی تعریف نشده</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- اطلاعات پایه --}}
                    <div class="ps-card">
                        <div class="ps-card-header">📋 اطلاعات محصول</div>
                        <div class="ps-card-body">
                            <table class="ps-info-table">
                                <tr><td>شناسه</td><td><code>{{ $product->product_id }}</code></td></tr>
                                <tr><td>نوع قیمت‌گذاری</td><td>{{ $product->price_type }}</td></tr>
                                <tr><td>واحد</td><td>{{ $product->unit }}</td></tr>
                                <tr><td>وزن</td><td>{{ number_format($product->weight) }} گرم</td></tr>
                                <tr><td>نوع ارسال</td><td>{{ $product->shipping_nature }}</td></tr>
                                <tr><td>ویژه</td><td>{{ $product->special ? '✅ بله' : '❌ خیر' }}</td></tr>
                                @if($product->special_end_date)
                                    <tr><td>پایان ویژه</td><td>{{ $product->special_end_date }}</td></tr>
                                @endif
                                <tr><td>تاریخ ایجاد</td><td>{{ jdate($product->created_at)->format('Y/m/d H:i') }}</td></tr>
                                <tr><td>آخرین بروزرسانی</td><td>{{ jdate($product->updated_at)->format('Y/m/d H:i') }}</td></tr>
                            </table>
                        </div>
                    </div>

                </div><!-- /two-col -->


                {{-- ══════════════════════════════════════════════════════════
                     گالری تصاویر
                ══════════════════════════════════════════════════════════ --}}
                @if($product->images && $product->images->count())
                    <div class="ps-card mt-2">
                        <div class="ps-card-header">🖼 گالری تصاویر ({{ $product->images->count() }} عکس)</div>
                        <div class="ps-card-body">
                            <div class="ps-gallery">
                                @foreach($product->images as $img)
                                    <a href="{{ asset($img->path) }}" target="_blank" class="ps-gallery-thumb">
                                        <img src="{{ asset($img->path) }}" alt="{{ $img->alt ?? $product->title }}">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif


                {{-- ══════════════════════════════════════════════════════════
                     تگ‌ها
                ══════════════════════════════════════════════════════════ --}}
                @if($product->tags && $product->tags->count())
                    <div class="ps-card mt-2">
                        <div class="ps-card-header">🏷 تگ‌ها</div>
                        <div class="ps-card-body ps-tags-wrap">
                            @foreach($product->tags as $tag)
                                <span class="ps-tag">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- ══════════════════════════════════════════════════════════
                     آخرین نظرات
                ══════════════════════════════════════════════════════════ --}}
                @if($product->reviews && $product->reviews->count())
                    <div class="ps-card mt-2">
                        <div class="ps-card-header">
                            💬 آخرین نظرات ({{ $salesStats['reviews_count'] }} نظر)
                            <a href="{{ route('admin.reviews.index') }}" class="ar-btn ar-btn-outline" style="padding:.25rem .7rem;font-size:.74rem">همه نظرات</a>
                        </div>
                        <div class="ps-card-body">
                            @foreach($product->reviews as $review)
                                <div class="ps-review-item">
                                    <div class="ps-review-head">
                                        <span class="ps-review-author">{{ $review->user->name ?? 'ناشناس' }}</span>
                                        <span class="ps-review-stars">
                @for($i=1;$i<=5;$i++)<span style="color:{{ $i<=$review->rating ? '#ff9f43':'#ddd' }}">★</span>@endfor
              </span>
                                        <span class="ps-review-date">{{ jdate($review->created_at)->format('Y/m/d') }}</span>
                                    </div>
                                    <p class="ps-review-body">{{ $review->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- ══════════════════════════════════════════════════════════
                     🔍 پنل SEO — تب‌بندی کامل
                ══════════════════════════════════════════════════════════ --}}
                <div class="ps-seo-panel mt-2">

                    {{-- هدر --}}
                    <div class="ps-seo-header">
                        <div class="ps-seo-title-wrap">
                            <span>🔍 تحلیل کامل سئو محصول</span>
                            <span class="ps-seo-subtitle">
              {{ count($seoIssues) }} مشکل بحرانی &nbsp;·&nbsp;
              {{ count($seoWarnings) }} هشدار &nbsp;·&nbsp;
              {{ count($seoGood) }} مورد تأیید
            </span>
                        </div>
                        <div class="ps-seo-score-wrap">
                            <div class="ps-seo-score-ring {{ $seoScore >= 75 ? 'good' : ($seoScore >= 45 ? 'warn' : 'bad') }}">
                                {{ $seoScore }}
                            </div>
                            <div>
                                <div class="ps-seo-score-label">امتیاز سئو</div>
                                <div class="ps-seo-score-label">از ۱۰۰</div>
                            </div>
                        </div>
                    </div>

                    {{-- تب‌ها --}}
                    <div class="ps-seo-tabs">
                        <div class="ps-seo-tab active" onclick="arSeoTab(this,'issues')">⚠️ مشکلات</div>
                        <div class="ps-seo-tab" onclick="arSeoTab(this,'tags')">🏷 تگ‌های HTML</div>
                        <div class="ps-seo-tab" onclick="arSeoTab(this,'links')">🔗 لینک‌ها</div>
                        <div class="ps-seo-tab" onclick="arSeoTab(this,'schema')">📐 Schema & OG</div>
                        <div class="ps-seo-tab" onclick="arSeoTab(this,'serp')">👁 SERP Preview</div>
                        <div class="ps-seo-tab" onclick="arSeoTab(this,'meta')">📄 متا</div>
                    </div>

                    {{-- ── Tab: مشکلات ── --}}
                    <div class="ps-seo-tab-content active" id="ps-seo-tab-issues">

                        @if(count($seoIssues))
                            <div class="ps-seo-section">
                                <div class="ps-seo-section-title seo-err">🔴 مشکلات بحرانی ({{ count($seoIssues) }})</div>
                                @foreach($seoIssues as $issue)
                                    <div class="ps-seo-row seo-issue">
                                        <div class="ps-seo-field">{{ $issue['field'] }}</div>
                                        <div class="ps-seo-msg">{{ $issue['msg'] }}</div>
                                        <div class="ps-seo-fix">💡 {{ $issue['fix'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($seoWarnings))
                            <div class="ps-seo-section">
                                <div class="ps-seo-section-title seo-warn">🟡 هشدارها ({{ count($seoWarnings) }})</div>
                                @foreach($seoWarnings as $w)
                                    <div class="ps-seo-row seo-warning">
                                        <div class="ps-seo-field">{{ $w['field'] }}</div>
                                        <div class="ps-seo-msg">{{ $w['msg'] }}</div>
                                        <div class="ps-seo-fix">💡 {{ $w['fix'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($seoGood))
                            <div class="ps-seo-section">
                                <div class="ps-seo-section-title seo-ok">🟢 موارد تأیید شده ({{ count($seoGood) }})</div>
                                @foreach($seoGood as $g)
                                    <div class="ps-seo-row seo-good">✔ {{ $g }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if(!count($seoIssues) && !count($seoWarnings))
                            <div style="text-align:center;padding:2rem;color:var(--ps-success)">
                                <div style="font-size:2rem">🎉</div>
                                <p style="font-weight:700">مشکل SEO پیدا نشد!</p>
                            </div>
                        @endif

                    </div>

                    {{-- ── Tab: تگ‌های HTML ── --}}
                    <div class="ps-seo-tab-content" id="ps-seo-tab-tags">

                        @if(count($missingTags))
                            <div class="ps-seo-section">
                                <div class="ps-seo-section-title seo-warn">تگ‌های ضروری که در توضیحات محصول استفاده نشده‌اند</div>
                                <div class="ps-missing-tags">
                                    @foreach($missingTags as $mt)
                                        <div class="ps-missing-tag">
                                            <code>&lt;{{ $mt['tag'] }}&gt;</code>
                                            <span>{{ $mt['reason'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div style="color:var(--ps-success);padding:1rem;font-weight:700">✅ همه تگ‌های ضروری در توضیحات وجود دارند.</div>
                        @endif

                        <div class="ps-seo-section" style="margin-top:1rem">
                            <div class="ps-seo-section-title">راهنمای تگ‌های HTML برای توضیحات محصول سئو‌پسند</div>
                            <table class="ps-info-table">
                                <tr><td><code>&lt;H1/H2&gt;</code></td><td>هدینگ اصلی — عنوان بخش‌های اصلی توضیحات را با H2 مشخص کنید</td></tr>
                                <tr><td><code>&lt;H3/H4&gt;</code></td><td>زیرعنوان‌ها — تقسیم‌بندی محتوای فنی</td></tr>
                                <tr><td><code>&lt;UL/OL&gt;</code></td><td>لیست ویژگی‌ها — خوانایی و شانس Featured Snippet</td></tr>
                                <tr><td><code>&lt;STRONG/B&gt;</code></td><td>کلیدواژه‌های مهم را برجسته کنید</td></tr>
                                <tr><td><code>&lt;TABLE&gt;</code></td><td>جدول مشخصات فنی — بسیار مهم برای محصولات فیزیکی</td></tr>
                                <tr><td><code>&lt;IMG alt=""&gt;</code></td><td>تصویر داخل توضیحات با alt مناسب</td></tr>
                            </table>
                        </div>

                    </div>

                    {{-- ── Tab: لینک‌ها ── --}}
                    <div class="ps-seo-tab-content" id="ps-seo-tab-links">

                        <div class="ps-seo-section">
                            <div class="ps-seo-section-title">🔗 لینک‌های داخلی در توضیحات ({{ count($internalLinks) }})</div>
                            @if(count($internalLinks))
                                @foreach($internalLinks as $link)
                                    <div class="ps-link-row internal">
                                        <span class="ps-link-text">{{ $link['text'] ?: 'بدون متن anchor' }}</span>
                                        <a href="{{ $link['url'] }}" target="_blank" class="ps-link-url">{{ Str::limit($link['url'], 70) }}</a>
                                    </div>
                                @endforeach
                            @else
                                <p class="ps-seo-empty-note">لینک داخلی پیدا نشد. حداقل ۲–۳ لینک به دسته‌بندی، برند یا محصولات مشابه اضافه کنید.</p>
                            @endif
                        </div>

                        <div class="ps-seo-section">
                            <div class="ps-seo-section-title">🌐 لینک‌های خارجی در توضیحات ({{ count($externalLinks) }})</div>
                            @if(count($externalLinks))
                                @foreach($externalLinks as $link)
                                    <div class="ps-link-row external {{ isset($link['nofollow']) && $link['nofollow'] ? 'nofollow' : '' }}">
                                        <span class="ps-link-text">{{ $link['text'] ?: 'بدون متن' }}</span>
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="ps-link-url">{{ Str::limit($link['url'], 65) }}</a>
                                        @if(isset($link['nofollow']) && $link['nofollow'])
                                            <span class="ps-nofollow-badge">nofollow</span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="ps-seo-empty-note">لینک خارجی معتبر اضافه کنید (سایت سازنده، ویکی‌پدیا، منابع فنی).</p>
                            @endif
                        </div>

                    </div>

                    {{-- ── Tab: Schema & OG ── --}}
                    <div class="ps-seo-tab-content" id="ps-seo-tab-schema">

                        <div class="ps-seo-section">
                            <div class="ps-seo-section-title">📐 Schema Product / Offer</div>
                            <div class="ps-schema-grid">
                                @foreach($schemaChecks as $sc)
                                    <div class="ps-schema-item {{ $sc['ok'] ? 'ok' : 'missing' }}">
                                        {{ $sc['ok'] ? '✅' : '❌' }} {{ $sc['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="ps-seo-section">
                            <div class="ps-seo-section-title">📣 Open Graph & Twitter Card</div>
                            @php
                                $ogChecks = [
                                  ['label' => 'og:title',         'ok' => !empty($product->meta_title ?: $product->title)],
                                  ['label' => 'og:description',   'ok' => !empty($product->meta_description ?: $product->short_description)],
                                  ['label' => 'og:image',         'ok' => !empty($product->image)],
                                  ['label' => 'og:type (product)','ok' => true],
                                  ['label' => 'og:url',           'ok' => !empty($product->slug)],
                                  ['label' => 'twitter:card',     'ok' => !empty($product->image)],
                                  ['label' => 'product:price',    'ok' => $product->prices->isNotEmpty()],
                                  ['label' => 'product:brand',    'ok' => !empty($product->brand_id)],
                                ];
                            @endphp
                            <div class="ps-schema-grid">
                                @foreach($ogChecks as $og)
                                    <div class="ps-schema-item {{ $og['ok'] ? 'ok' : 'missing' }}">
                                        {{ $og['ok'] ? '✅' : '❌' }} {{ $og['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- ── Tab: SERP Preview ── --}}
                    <div class="ps-seo-tab-content" id="ps-seo-tab-serp">

                        <p style="font-size:.78rem;color:var(--ps-muted);margin-bottom:.8rem">پیش‌نمایش تقریبی نمایش این محصول در نتایج جستجوی گوگل:</p>
                        <div class="ps-serp-preview">
                            <div class="ps-serp-url">{{ url('/products/' . $product->slug) }}</div>
                            <div class="ps-serp-title">{{ Str::limit($product->meta_title ?: $product->title, 60) }}</div>
                            <div class="ps-serp-desc">
                                {{ Str::limit($product->meta_description ?: ($product->short_description ?? 'توضیحات متا تعریف نشده — گوگل خودش متنی انتخاب می‌کند که ممکن است مناسب نباشد.'), 160) }}
                            </div>
                        </div>

                        {{-- Rich Snippet پیش‌نمایش --}}
                        <p style="font-size:.78rem;color:var(--ps-muted);margin-top:1.2rem;margin-bottom:.5rem">پیش‌نمایش Rich Snippet (Product Schema):</p>
                        <div class="ps-serp-preview">
                            <div class="ps-serp-url">{{ url('/products/' . $product->slug) }}</div>
                            <div class="ps-serp-title">{{ Str::limit($product->meta_title ?: $product->title, 60) }}</div>
                            <div style="display:flex;align-items:center;gap:.5rem;margin:.3rem 0;font-size:.8rem">
                                <span style="color:#ff9f43">★★★★☆</span>
                                <span style="color:#555">{{ number_format($salesStats['avg_rating'],1) }} — {{ $salesStats['reviews_count'] }} نظر</span>
                            </div>
                            @if($product->prices->isNotEmpty())
                                <div style="font-size:.8rem;color:#555">
                                    قیمت: <strong style="color:#1a1a1a">{{ number_format($product->prices->first()->price) }} تومان</strong>
                                    @if($product->prices->first()->stock > 0)
                                        — <span style="color:#28c76f">✅ موجود در انبار</span>
                                    @else
                                        — <span style="color:#ea5455">❌ ناموجود</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <p style="font-size:.72rem;color:var(--ps-muted);margin-top:.5rem">* پیش‌نمایش تقریبی است. نمایش واقعی در گوگل ممکن است متفاوت باشد.</p>

                    </div>

                    {{-- ── Tab: متا ── --}}
                    <div class="ps-seo-tab-content" id="ps-seo-tab-meta">

                        <table class="ps-info-table">
                            <tr>
                                <td>Meta Title</td>
                                <td>
                                    {{ $product->meta_title ?: '—' }}
                                    @php $mtl = mb_strlen($product->meta_title ?? '') @endphp
                                    <span class="ps-char-count {{ $mtl >= 50 && $mtl <= 65 ? 'ok' : ($mtl > 0 ? 'warn' : 'warn') }}">{{ $mtl }} کاراکتر</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Meta Description</td>
                                <td>
                                    {{ $product->meta_description ?: '—' }}
                                    @php $mdl = mb_strlen($product->meta_description ?? '') @endphp
                                    <span class="ps-char-count {{ $mdl >= 130 && $mdl <= 165 ? 'ok' : ($mdl > 0 ? 'warn' : 'warn') }}">{{ $mdl }} کاراکتر</span>
                                </td>
                            </tr>
                            <tr><td>Slug</td><td><code>{{ $product->slug ?: '—' }}</code></td></tr>
                            <tr><td>Image Alt</td><td>{{ $product->image_alt ?: '—' }}</td></tr>
                            <tr><td>عنوان انگلیسی</td><td>{{ $product->title_en ?: '—' }}</td></tr>
                            <tr><td>تاریخ انتشار (Schema)</td><td>{{ $product->publish_date ?? '—' }}</td></tr>
                        </table>

                    </div>

                </div><!-- /seo-panel -->

            </div><!-- /content-body -->
        </div>
    </div>
@endsection


@push('styles')
    <style>
        /* ─── Variables ─── */
        :root {
            --ps-radius: 12px;
            --ps-shadow: 0 2px 14px rgba(0,0,0,0.07);
            --ps-primary: #7367f0;
            --ps-success: #28c76f;
            --ps-danger:  #ea5455;
            --ps-warning: #ff9f43;
            --ps-info:    #00cfe8;
            --ps-border:  #ebebeb;
            --ps-text:    #3d3d3d;
            --ps-muted:   #8a8a8a;
        }

        /* ─── Hero Card ─── */
        .ps-hero-card {
            display: flex;
            gap: 1.8rem;
            background: #fff;
            border-radius: var(--ps-radius);
            box-shadow: var(--ps-shadow);
            padding: 1.6rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }
        .ps-hero-img-wrap {
            position: relative;
            flex-shrink: 0;
            width: 220px;
            height: 220px;
            border-radius: 10px;
            overflow: hidden;
            background: #f5f5f5;
        }
        .ps-hero-img-wrap img {
            width:100%; height:100%; object-fit:cover;
        }
        .ps-status-badge {
            position: absolute; bottom:8px; right:8px;
            font-size: .72rem; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
        }
        .ps-status-active { background: #28c76f22; color: var(--ps-success); }
        .ps-status-draft  { background: #ff9f4322; color: var(--ps-warning); }

        .ps-hero-info { flex: 1; min-width: 0; }
        .ps-eyebrow { font-size:.75rem; color:var(--ps-muted); margin-bottom:.4rem; font-weight:600; letter-spacing:.5px; }
        .ps-product-title { font-size:1.25rem; font-weight:800; color:var(--ps-text); line-height:1.5; margin-bottom:.25rem; }
        .ps-product-title-en { font-size:.82rem; color:var(--ps-muted); margin-bottom:.6rem; font-style:italic; }

        .ps-meta-row { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:.8rem; }
        .ps-meta-pill {
            background:#f4f4f8; color:#555; border-radius:20px;
            padding:3px 12px; font-size:.75rem; font-weight:600;
        }

        .ps-rating-row { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; }
        .ps-star-score { display:flex; align-items:center; gap:3px; font-size:1.15rem; }
        .ps-rating-num { font-weight:800; font-size:.95rem; color:var(--ps-text); margin-right:4px; }
        .ps-rating-count { font-size:.75rem; color:var(--ps-muted); }

        .ps-hero-actions { display:flex; flex-wrap:wrap; gap:.6rem; }
        .ps-btn {
            padding:.45rem 1rem; border-radius:8px; font-size:.82rem;
            font-weight:700; text-decoration:none; transition:opacity .15s;
            cursor:pointer; border:none; display:inline-block;
        }
        .ps-btn:hover { opacity:.82; }
        .ps-btn-primary { background:var(--ps-primary); color:#fff !important; }
        .ps-btn-outline { background:#fff; color:var(--ps-primary) !important; border:1.5px solid var(--ps-primary); }

        /* ─── Stats Grid ─── */
        .ps-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .ps-stat-box {
            background:#fff; border-radius:var(--ps-radius);
            box-shadow: var(--ps-shadow); padding:1rem 1.1rem;
            display:flex; align-items:center; gap:.9rem;
        }
        .ps-stat-icon {
            width:42px; height:42px; border-radius:10px; font-size:1.1rem;
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .ps-stat-value { font-size:1.1rem; font-weight:800; color:var(--ps-text); }
        .ps-stat-label { font-size:.72rem; color:var(--ps-muted); font-weight:500; }

        /* ─── Cards ─── */
        .ps-card {
            background:#fff; border-radius:var(--ps-radius);
            box-shadow:var(--ps-shadow); overflow:hidden;
        }
        .ps-card-header {
            padding:.85rem 1.2rem; font-size:.9rem; font-weight:800;
            color:var(--ar-text); border-bottom:1px solid var(--ar-border);
            background:#fafafa; display:flex; align-items:center; justify-content:space-between;
        }
        .ps-card-body { padding:1rem 1.2rem; }
        .ps-two-col { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; margin-bottom:1.2rem; }

        /* ─── Prices ─── */
        .ps-price-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:.6rem 0; border-bottom:1px solid var(--ps-border);
        }
        .ps-price-row:last-child { border:none; }
        .ps-price-label { font-size:.82rem; font-weight:600; color:var(--ps-text); }
        .ps-old-price { font-size:.78rem; color:var(--ps-muted); text-decoration:line-through; margin-left:.4rem; }
        .ps-new-price { font-size:.9rem; font-weight:800; color:var(--ps-primary); }
        .ps-stock-chip {
            display:inline-block; font-size:.7rem; font-weight:700;
            padding:2px 8px; border-radius:12px; margin-top:3px;
        }
        .ps-stock-chip.in  { background:#28c76f18; color:var(--ps-success); }
        .ps-stock-chip.low { background:#ff9f4318; color:var(--ps-warning); }
        .ps-stock-chip.out { background:#ea545518; color:var(--ps-danger);  }
        .ps-color-dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:4px; border:1px solid #ccc; }

        /* ─── Info Table ─── */
        .ps-info-table { width:100%; border-collapse:collapse; font-size:.82rem; }
        .ps-info-table td { padding:.45rem .6rem; border-bottom:1px solid var(--ps-border); vertical-align:top; }
        .ps-info-table td:first-child { color:var(--ps-muted); font-weight:600; width:45%; }
        .ps-info-table tr:last-child td { border:none; }

        /* ─── Gallery ─── */
        .ps-gallery { display:flex; flex-wrap:wrap; gap:.6rem; }
        .ps-gallery-thumb {
            width:80px; height:80px; border-radius:8px; overflow:hidden;
            border:2px solid var(--ps-border); display:block; transition:border-color .15s;
        }
        .ps-gallery-thumb:hover { border-color:var(--ps-primary); }
        .ps-gallery-thumb img { width:100%; height:100%; object-fit:cover; }

        /* ─── Tags ─── */
        .ps-tags-wrap { display:flex; flex-wrap:wrap; gap:.5rem; }
        .ps-tag {
            background:var(--ps-primary)18; color:var(--ps-primary);
            border-radius:20px; padding:3px 12px; font-size:.78rem; font-weight:700;
        }

        /* ─── Reviews ─── */
        .ps-review-item { padding:.7rem 0; border-bottom:1px solid var(--ps-border); }
        .ps-review-item:last-child { border:none; }
        .ps-review-head { display:flex; align-items:center; gap:.6rem; margin-bottom:.3rem; }
        .ps-review-author { font-weight:700; font-size:.82rem; }
        .ps-review-date { font-size:.72rem; color:var(--ps-muted); margin-right:auto; }
        .ps-review-body { font-size:.82rem; color:#555; margin:0; }

        /* ─── SEO Panel ─── */
        .ps-seo-panel {
            background:#fff; border-radius:var(--ps-radius);
            box-shadow:var(--ps-shadow); overflow:hidden;
        }
        .ps-seo-header {
            display:flex; justify-content:space-between; align-items:center;
            padding:1rem 1.4rem; background:linear-gradient(135deg,#1a1a2e,#16213e);
            color:#fff; font-size:1rem; font-weight:800;
        }
        .ps-seo-score-wrap { display:flex; align-items:center; gap:.7rem; }
        .ps-seo-score-ring {
            width:52px; height:52px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; font-weight:900; border:3px solid;
        }
        .ps-seo-score-ring.good { border-color:var(--ps-success); color:var(--ps-success); }
        .ps-seo-score-ring.warn { border-color:var(--ps-warning); color:var(--ps-warning); }
        .ps-seo-score-ring.bad  { border-color:var(--ps-danger);  color:var(--ps-danger);  }
        .ps-seo-score-label { font-size:.72rem; color:#aaa; }

        .ps-seo-body { padding:1.2rem 1.4rem; }
        .ps-seo-section { margin-bottom:1.4rem; }
        .ps-seo-section-title {
            font-size:.82rem; font-weight:800; margin-bottom:.6rem;
            padding-bottom:.35rem; border-bottom:2px solid var(--ps-border);
            color:var(--ps-text);
        }
        .ps-seo-section-title.seo-err  { border-color:#ea545560; color:var(--ps-danger);  }
        .ps-seo-section-title.seo-warn { border-color:#ff9f4360; color:var(--ps-warning); }
        .ps-seo-section-title.seo-ok   { border-color:#28c76f60; color:var(--ps-success); }

        .ps-seo-row {
            padding:.6rem .8rem; border-radius:8px; margin-bottom:.4rem; font-size:.8rem;
        }
        .seo-issue   { background:#ea545510; border-right:3px solid var(--ps-danger); }
        .seo-warning { background:#ff9f4310; border-right:3px solid var(--ps-warning); }
        .seo-good    { background:#28c76f10; border-right:3px solid var(--ps-success); color:#333; }

        .ps-seo-field { font-weight:800; font-size:.72rem; color:var(--ps-muted); margin-bottom:2px; }
        .ps-seo-msg   { font-weight:600; color:var(--ps-text); }
        .ps-seo-fix   { font-size:.75rem; color:#666; margin-top:3px; }
        .ps-seo-empty-note { font-size:.8rem; color:var(--ps-muted); font-style:italic; }

        /* Missing Tags */
        .ps-missing-tags { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
        .ps-missing-tag {
            background:#fff9f0; border:1px solid #ff9f4340; border-radius:8px;
            padding:.5rem .7rem; font-size:.78rem;
        }
        .ps-missing-tag code { display:block; font-weight:800; color:var(--ps-warning); margin-bottom:2px; }

        /* Links */
        .ps-link-row {
            display:flex; align-items:center; gap:.7rem;
            padding:.4rem .5rem; border-radius:6px; margin-bottom:.3rem;
            font-size:.78rem;
        }
        .ps-link-row.internal { background:#7367f010; }
        .ps-link-row.external { background:#00cfe810; }
        .ps-link-text { font-weight:700; color:var(--ps-text); min-width:120px; }
        .ps-link-url  { color:var(--ps-primary); text-decoration:none; word-break:break-all; }
        .ps-link-url:hover { text-decoration:underline; }

        /* Schema Grid */
        .ps-schema-grid {
            display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:.5rem;
        }
        .ps-schema-item {
            padding:.45rem .7rem; border-radius:7px; font-size:.78rem; font-weight:600;
        }
        .ps-schema-item.ok      { background:#28c76f12; color:#1a7a44; }
        .ps-schema-item.missing { background:#ea545512; color:#a33; }

        /* SERP Preview */
        .ps-serp-preview {
            border:1px solid var(--ps-border); border-radius:8px; padding:1rem 1.2rem;
            background:#fafbff; font-family:Arial,sans-serif;
        }
        .ps-serp-url   { font-size:.72rem; color:#598845; margin-bottom:.25rem; }
        .ps-serp-title { font-size:1rem; color:#1a0dab; font-weight:600; margin-bottom:.2rem; cursor:pointer; }
        .ps-serp-title:hover { text-decoration:underline; }
        .ps-serp-desc  { font-size:.8rem; color:#555; line-height:1.5; }

        /* Char count badge */
        .ps-char-count {
            display:inline-block; font-size:.7rem; font-weight:700;
            padding:1px 7px; border-radius:10px; margin-right:6px;
        }
        .ps-char-count.ok   { background:#28c76f18; color:var(--ps-success); }
        .ps-char-count.warn { background:#ff9f4318; color:var(--ps-warning); }

        .ps-empty { color:var(--ps-muted); font-size:.82rem; font-style:italic; }
        .mt-2 { margin-top:1.2rem !important; }

        @media (max-width:768px) {
            .ps-hero-card { flex-direction:column; }
            .ps-hero-img-wrap { width:100%; height:200px; }
            .ps-two-col { grid-template-columns:1fr; }
            .ps-missing-tags { grid-template-columns:1fr; }
        }

        /* SEO tabs */
        .ps-seo-tabs { display:flex; gap:0; border-bottom:2px solid var(--ar-border); padding:0 1.2rem; }
        .ps-seo-tab {
            padding:.6rem 1rem; font-size:.8rem; font-weight:700;
            cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px;
            color:var(--ar-muted); transition:color .15s, border-color .15s;
        }
        .ps-seo-tab.active { color:var(--ar-primary); border-bottom-color:var(--ar-primary); }
        .ps-seo-tab-content { display:none; padding:1.2rem; }
        .ps-seo-tab-content.active { display:block; }
        .ps-attributes-vertical {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .ps-attr-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .ps-attr-name {
            color: #6c757d;
        }

        .ps-attr-value {
            font-weight: 500;
            color: #2c3e50;
        }
        /* استایل‌های تنوع و قیمت */
        .ps-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }

        .ps-price-row:hover {
            background-color: #f8f9fa;
        }

        .ps-price-info {
            flex: 2;
        }

        .ps-attributes-list {
            margin-bottom: 0.5rem;
        }

        .ps-attributes {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .ps-attr-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: #f1f3f5;
            border-radius: 20px;
            font-size: 0.75rem;
            color: #495057;
        }

        .ps-color-dot {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1px solid #ddd;
        }

        .ps-attr-default {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .ps-sku {
            font-size: 0.7rem;
            color: #adb5bd;
            font-family: monospace;
        }

        .ps-price-details {
            flex: 1;
            text-align: left;
        }

        .ps-price-values {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .ps-old-price {
            text-decoration: line-through;
            color: #adb5bd;
            font-size: 0.8rem;
        }

        .ps-new-price {
            font-weight: 700;
            font-size: 1rem;
            color: #2c3e50;
        }

        .ps-discount-badge {
            background: #dc3545;
            color: white;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .ps-stock-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .ps-stock-chip {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .ps-stock-chip.in {
            background: #d4edda;
            color: #155724;
        }

        .ps-stock-chip.low {
            background: #fff3cd;
            color: #856404;
        }

        .ps-stock-chip.out {
            background: #f8d7da;
            color: #721c24;
        }

        .ps-sold-count {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .ps-sold-count i {
            margin-left: 0.25rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .ps-price-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .ps-price-details {
                width: 100%;
            }

            .ps-price-values,
            .ps-stock-info {
                justify-content: flex-start;
            }
        }
        .ar-btn {
            padding:.42rem .95rem; border-radius:8px; font-size:.8rem;
            font-weight:700; text-decoration:none; transition:opacity .15s;
            border:none; cursor:pointer; display:inline-block;
        }
        .ar-btn:hover { opacity:.82; }
        .ar-btn-primary { background:var(--ar-primary); color:#fff !important; }
        .ar-btn-success { background:var(--ar-success); color:#fff !important; }
        .ar-btn-outline { background:#fff; color:var(--ar-primary) !important; border:1.5px solid var(--ar-primary); }
        .ar-btn-danger  { background:#fff; color:var(--ar-danger) !important; border:1.5px solid var(--ar-danger); }

    </style>
@endpush
@push('scripts')
    <script>
        function arSeoTab(el, tab) {
            document.querySelectorAll('.ps-seo-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.ps-seo-tab-content').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('ps-seo-tab-' + tab).classList.add('active');
        }
    </script>
@endpush
