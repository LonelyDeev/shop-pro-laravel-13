@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/products/details.css') }}">
@endpush
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
                            <a href="{{ Illuminate\Support\Facades\Route::has('front.products.show') ? route('front.products.show', $product) : '' }}" target="_blank" class="ps-btn ps-btn-outline">🔗 مشاهده در سایت</a>
                            <a href="{{ route('admin.orders.index', ['product_id' => $product->id]) }}" class="ps-btn ps-btn-outline">📋 سفارشات</a>
                        </div>

                        <span class="ps-status-badge ps-status-{{ $product->status === 'Accept' ? 'active' : 'draft' }}">
            {{ $product->status === 'Accept' ? '✅ منتشر شده' : '⏸ پیش‌نویس' }}
          </span>

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
