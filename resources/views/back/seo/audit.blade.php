@extends('back.layouts.master')

@push('styles')
    <style>
        :root {
            --sa-primary: #7367f0;
            --sa-success: #28c76f;
            --sa-danger:  #ea5455;
            --sa-warning: #ff9f43;
            --sa-info:    #00cfe8;
            --sa-border:  #ebebeb;
            --sa-text:    #3d3d3d;
            --sa-muted:   #8a8a8a;
            --sa-bg:      #f8f8fb;
            --sa-radius:  12px;
            --sa-shadow:  0 2px 16px rgba(0,0,0,0.07);
        }

        /* ── Layout ── */
        .sa-wrap { padding: 0 0 2rem; }

        /* ── Header Hero ── */
        .sa-hero {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            border-radius: var(--sa-radius);
            padding: 1.8rem 2rem;
            color: #fff;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .sa-hero-title { font-size: 1.4rem; font-weight: 900; }
        .sa-hero-sub   { font-size: .8rem; color: #aab; margin-top: 4px; }

        .sa-overall-score {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .sa-score-big {
            width: 80px; height: 80px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; font-weight: 900;
            border: 4px solid;
        }
        .sa-score-big.good { border-color: var(--sa-success); color: var(--sa-success); }
        .sa-score-big.warn { border-color: var(--sa-warning); color: var(--sa-warning); }
        .sa-score-big.bad  { border-color: var(--sa-danger);  color: var(--sa-danger); }
        .sa-score-label { font-size: .78rem; color: #aab; }

        /* ── Tabs ── */
        .sa-main-tabs {
            display: flex;
            gap: 0;
            background: #fff;
            border-radius: var(--sa-radius) var(--sa-radius) 0 0;
            box-shadow: var(--sa-shadow);
            overflow-x: auto;
            border-bottom: 2px solid var(--sa-border);
            padding: 0 1rem;
        }
        .sa-tab {
            padding: .7rem 1.1rem;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            color: var(--sa-muted);
            white-space: nowrap;
            transition: color .15s, border-color .15s;
        }
        .sa-tab.active { color: var(--sa-primary); border-bottom-color: var(--sa-primary); }
        .sa-tab-panel { display: none; }
        .sa-tab-panel.active { display: block; }

        /* ── Cards ── */
        .sa-card {
            background: #fff;
            border-radius: var(--sa-radius);
            box-shadow: var(--sa-shadow);
            overflow: hidden;
            margin-bottom: 1.2rem;
        }
        .sa-card-header {
            padding: .85rem 1.2rem;
            font-size: .9rem;
            font-weight: 800;
            color: var(--sa-text);
            border-bottom: 1px solid var(--sa-border);
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sa-card-body { padding: 1.1rem 1.2rem; }

        /* ── Stats Grid ── */
        .sa-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: .9rem;
            margin-bottom: 1.3rem;
        }
        .sa-stat {
            background: #fff;
            border-radius: var(--sa-radius);
            box-shadow: var(--sa-shadow);
            padding: .9rem 1rem;
            display: flex;
            align-items: center;
            gap: .8rem;
        }
        .sa-stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .sa-stat-value { font-size: 1.2rem; font-weight: 900; color: var(--sa-text); }
        .sa-stat-label { font-size: .68rem; color: var(--sa-muted); font-weight: 500; }

        /* ── Issue Rows ── */
        .sa-issue-row {
            padding: .55rem .8rem;
            border-radius: 8px;
            margin-bottom: .4rem;
            font-size: .79rem;
        }
        .sa-issue-row.err  { background: #ea545510; border-right: 3px solid var(--sa-danger); }
        .sa-issue-row.warn { background: #ff9f4310; border-right: 3px solid var(--sa-warning); }
        .sa-issue-row.ok   { background: #28c76f10; border-right: 3px solid var(--sa-success); color: #333; }
        .sa-issue-field { font-weight: 800; font-size: .7rem; color: var(--sa-muted); margin-bottom: 2px; }
        .sa-issue-msg   { font-weight: 600; color: var(--sa-text); }
        .sa-issue-fix   { font-size: .74rem; color: #666; margin-top: 2px; }

        /* ── Section Title ── */
        .sa-section-title {
            font-size: .8rem;
            font-weight: 800;
            color: var(--sa-text);
            margin-bottom: .6rem;
            padding-bottom: .35rem;
            border-bottom: 2px solid var(--sa-border);
        }
        .sa-section-title.err  { border-color: #ea545550; color: var(--sa-danger); }
        .sa-section-title.warn { border-color: #ff9f4350; color: var(--sa-warning); }
        .sa-section-title.ok   { border-color: #28c76f50; color: var(--sa-success); }

        /* ── Table ── */
        .sa-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
        .sa-table th {
            background: #f5f5f8; font-weight: 700;
            padding: .5rem .7rem; text-align: right;
            border-bottom: 2px solid var(--sa-border);
            font-size: .75rem; color: var(--sa-muted);
        }
        .sa-table td { padding: .45rem .7rem; border-bottom: 1px solid var(--sa-border); vertical-align: middle; }
        .sa-table tr:last-child td { border: none; }
        .sa-table tr:hover td { background: #f9f9fc; }

        /* ── Badge ── */
        .sa-badge {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 12px;
        }
        .sa-badge.err  { background: #ea545520; color: var(--sa-danger); }
        .sa-badge.warn { background: #ff9f4320; color: var(--sa-warning); }
        .sa-badge.ok   { background: #28c76f20; color: var(--sa-success); }

        /* ── Crawler Section ── */
        .sa-crawler-form {
            display: flex;
            gap: .7rem;
            margin-bottom: 1.2rem;
        }
        .sa-crawler-form input {
            flex: 1;
            padding: .55rem 1rem;
            border: 1.5px solid var(--sa-border);
            border-radius: 8px;
            font-size: .85rem;
            outline: none;
            transition: border-color .2s;
        }
        .sa-crawler-form input:focus { border-color: var(--sa-primary); }
        .sa-crawl-btn {
            padding: .55rem 1.4rem;
            background: var(--sa-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .15s;
            white-space: nowrap;
        }
        .sa-crawl-btn:hover { opacity: .85; }
        .sa-crawl-btn:disabled { opacity: .5; cursor: not-allowed; }

        #crawl-result { display: none; }

        /* Score Ring small */
        .sa-score-sm {
            width: 52px; height: 52px;
            border-radius: 50%;
            border: 3px solid;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; font-weight: 900;
        }
        .sa-score-sm.good { border-color: var(--sa-success); color: var(--sa-success); }
        .sa-score-sm.warn { border-color: var(--sa-warning); color: var(--sa-warning); }
        .sa-score-sm.bad  { border-color: var(--sa-danger);  color: var(--sa-danger); }

        /* Crawl Result Header */
        .crawl-result-header {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1rem 1.2rem;
            background: #f8f8fb;
            border-bottom: 1px solid var(--sa-border);
            flex-wrap: wrap;
        }
        .crawl-meta { display: flex; flex-direction: column; gap: 2px; }
        .crawl-meta-url  { font-size: .82rem; font-weight: 700; color: var(--sa-text); }
        .crawl-meta-info { font-size: .72rem; color: var(--sa-muted); }
        .crawl-pills { display: flex; flex-wrap: wrap; gap: .4rem; margin-right: auto; }
        .crawl-pill {
            background: #f0f0f8; color: #555;
            border-radius: 20px; padding: 3px 10px;
            font-size: .72rem; font-weight: 700;
        }

        /* robots/sitemap result */
        .sa-mono {
            background: #1e1e2e;
            color: #cdd6f4;
            border-radius: 8px;
            padding: 1rem;
            font-size: .75rem;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            line-height: 1.6;
        }

        /* Progress Bar */
        .sa-progress { height: 8px; background: #eee; border-radius: 4px; overflow: hidden; margin-bottom: 4px; }
        .sa-progress-fill { height: 100%; border-radius: 4px; transition: width .5s; }

        /* Donut chart */
        .sa-donut-row {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .sa-donut-legend { flex: 1; min-width: 180px; }
        .sa-legend-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            margin-bottom: .4rem;
            color: var(--sa-text);
            font-weight: 600;
        }
        .sa-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* Two Col */
        .sa-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }

        /* Empty */
        .sa-empty { color: var(--sa-muted); font-size: .8rem; font-style: italic; padding: 1rem 0; }

        /* Loading spinner */
        .sa-spinner {
            width: 32px; height: 32px;
            border: 3px solid var(--sa-border);
            border-top-color: var(--sa-primary);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media(max-width: 768px) {
            .sa-two-col { grid-template-columns: 1fr; }
            .sa-hero { flex-direction: column; }
            .sa-main-tabs { padding: 0 .5rem; }
        }
    </style>
@endpush

@section('content')
    <div class="app-content content" dir="rtl">
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row mb-1">
                <div class="col-12">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item active">آدیت سئو سایت</li>
                    </ol>
                </div>
            </div>

            <div class="content-body sa-wrap">

                {{-- ══ Hero ══ --}}
                <div class="sa-hero">
                    <div>
                        <div class="sa-hero-title">🔍 داشبورد آدیت سئو سایت</div>
                        <div class="sa-hero-sub">بررسی جامع محصولات، مقالات، دسته‌بندی‌ها، کراول زنده و فایل‌های فنی</div>
                        <div style="margin-top:.8rem;font-size:.75rem;color:#aab">
                            آخرین بروزرسانی: {{ now()->format('Y/m/d H:i') }}
                            &nbsp;·&nbsp; {{ $dbStats['productStats']['total'] }} محصول
                            &nbsp;·&nbsp; {{ $dbStats['postStats']['total'] }} مقاله
                            &nbsp;·&nbsp; {{ $dbStats['categoryStats']['total'] }} دسته‌بندی
                        </div>
                    </div>
                    <div class="sa-overall-score">
                        @php $score = $dbStats['overallScore'] @endphp
                        <div class="sa-score-big {{ $score >= 75 ? 'good' : ($score >= 45 ? 'warn' : 'bad') }}">
                            {{ $score }}
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:800;color:#fff">امتیاز کلی سئو</div>
                            <div class="sa-score-label">{{ $dbStats['totalIssues'] }} مشکل شناسایی شده</div>
                        </div>
                    </div>
                </div>

                {{-- ══ آمار سریع ══ --}}
                <div class="sa-stats-grid">
                    @php
                        $ps = $dbStats['productStats'];
                        $po = $dbStats['postStats'];
                        $cs = $dbStats['categoryStats'];
                    @endphp
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#ea545515;color:#ea5455">🔴</div>
                        <div>
                            <div class="sa-stat-value" style="color:#ea5455">{{ $ps['no_meta_title'] + $po['no_meta_title'] + $cs['no_meta_title'] }}</div>
                            <div class="sa-stat-label">بدون Meta Title</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#ea545515;color:#ea5455">📝</div>
                        <div>
                            <div class="sa-stat-value" style="color:#ea5455">{{ $ps['no_meta_desc'] + $po['no_meta_desc'] + $cs['no_meta_desc'] }}</div>
                            <div class="sa-stat-label">بدون Meta Desc</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#ff9f4315;color:#ff9f43">🖼</div>
                        <div>
                            <div class="sa-stat-value" style="color:#ff9f43">{{ $ps['no_image_alt'] }}</div>
                            <div class="sa-stat-label">تصویر بدون Alt</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#ea545515;color:#ea5455">📄</div>
                        <div>
                            <div class="sa-stat-value" style="color:#ea5455">{{ $ps['no_description'] + $po['no_content'] }}</div>
                            <div class="sa-stat-label">بدون محتوا</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#ff9f4315;color:#ff9f43">🔗</div>
                        <div>
                            <div class="sa-stat-value" style="color:#ff9f43">{{ $ps['long_slug'] + $ps['no_slug'] }}</div>
                            <div class="sa-stat-label">Slug مشکل‌دار</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#7367f015;color:#7367f0">📂</div>
                        <div>
                            <div class="sa-stat-value">{{ $ps['no_category'] }}</div>
                            <div class="sa-stat-label">محصول بدون دسته</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#00cfe815;color:#00cfe8">📅</div>
                        <div>
                            <div class="sa-stat-value">{{ $ps['no_publish_date'] + $po['no_publish_date'] }}</div>
                            <div class="sa-stat-label">بدون تاریخ انتشار</div>
                        </div>
                    </div>
                    <div class="sa-stat">
                        <div class="sa-stat-icon" style="background:#28c76f15;color:#28c76f">✅</div>
                        <div>
                            <div class="sa-stat-value" style="color:#28c76f">{{ $score }}%</div>
                            <div class="sa-stat-label">امتیاز سلامت</div>
                        </div>
                    </div>
                </div>


                {{-- ══ تب‌های اصلی ══ --}}
                <div class="sa-main-tabs">
                    <div class="sa-tab active" onclick="saTab(this,'products')">📦 محصولات</div>
                    <div class="sa-tab" onclick="saTab(this,'posts')">📝 مقالات</div>
                    <div class="sa-tab" onclick="saTab(this,'categories')">📂 دسته‌بندی‌ها</div>
                    <div class="sa-tab" onclick="saTab(this,'crawler')">🌐 کراول زنده</div>
                    <div class="sa-tab" onclick="saTab(this,'technical')">⚙️ فنی</div>
                    <div class="sa-tab" onclick="saTab(this,'duplicates')">♻️ محتوای تکراری</div>
                </div>

                <div style="background:#fff;border-radius:0 0 var(--sa-radius) var(--sa-radius);box-shadow:var(--sa-shadow);padding:1.2rem 1.2rem 1.4rem;margin-bottom:1.2rem">


                    {{-- ══════════════ TAB: محصولات ══════════════ --}}
                    <div class="sa-tab-panel active" id="panel-products">

                        {{-- Progress bars --}}
                        <div class="sa-two-col" style="margin-bottom:1.2rem">
                            <div>
                                @php
                                    $checks = [
                                      ['label'=>'Meta Title', 'missing'=>$ps['no_meta_title'], 'total'=>$ps['total'], 'color'=>'#ea5455'],
                                      ['label'=>'Meta Description', 'missing'=>$ps['no_meta_desc'], 'total'=>$ps['total'], 'color'=>'#ea5455'],
                                      ['label'=>'Alt تصویر', 'missing'=>$ps['no_image_alt'], 'total'=>$ps['total'], 'color'=>'#ff9f43'],
                                      ['label'=>'توضیحات محصول', 'missing'=>$ps['no_description'], 'total'=>$ps['total'], 'color'=>'#ff9f43'],
                                    ];
                                @endphp
                                @foreach($checks as $c)
                                    @php $pct = $c['total'] > 0 ? round((1 - $c['missing']/$c['total'])*100) : 100 @endphp
                                    <div style="margin-bottom:.8rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:var(--sa-muted);font-weight:600;margin-bottom:3px">
                                            <span>{{ $c['label'] }}</span>
                                            <span style="color:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}">{{ $pct }}% کامل ({{ $c['total']-$c['missing'] }}/{{ $c['total'] }})</span>
                                        </div>
                                        <div class="sa-progress">
                                            <div class="sa-progress-fill" style="width:{{ $pct }}%;background:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                @php
                                    $checks2 = [
                                      ['label'=>'تصویر شاخص', 'missing'=>$ps['no_image'], 'total'=>$ps['total'], 'color'=>'#ff9f43'],
                                      ['label'=>'دسته‌بندی', 'missing'=>$ps['no_category'], 'total'=>$ps['total'], 'color'=>'#7367f0'],
                                      ['label'=>'برند', 'missing'=>$ps['no_brand'], 'total'=>$ps['total'], 'color'=>'#00cfe8'],
                                      ['label'=>'تاریخ انتشار', 'missing'=>$ps['no_publish_date'], 'total'=>$ps['total'], 'color'=>'#28c76f'],
                                    ];
                                @endphp
                                @foreach($checks2 as $c)
                                    @php $pct = $c['total'] > 0 ? round((1 - $c['missing']/$c['total'])*100) : 100 @endphp
                                    <div style="margin-bottom:.8rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:var(--sa-muted);font-weight:600;margin-bottom:3px">
                                            <span>{{ $c['label'] }}</span>
                                            <span style="color:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}">{{ $pct }}% کامل</span>
                                        </div>
                                        <div class="sa-progress">
                                            <div class="sa-progress-fill" style="width:{{ $pct }}%;background:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Meta Title مشکل‌دار ──  --}}
                        @if($ps['items_no_meta_title']->count())
                            <div class="sa-card" style="margin-bottom:1rem">
                                <div class="sa-card-header" style="color:var(--sa-danger)">
                                    🔴 محصولات بدون Meta Title ({{ $ps['no_meta_title'] }} مورد)
                                    <a href="{{ route('admin.products.index') }}" class="sa-badge err">مشاهده همه</a>
                                </div>
                                <div class="sa-card-body" style="padding:0">
                                    <table class="sa-table">
                                        <thead><tr><th>#</th><th>عنوان محصول</th><th>عملیات</th></tr></thead>
                                        <tbody>
                                        @foreach($ps['items_no_meta_title'] as $p)
                                            <tr>
                                                <td style="color:var(--sa-muted);width:40px">#{{ $p->id }}</td>
                                                <td>{{ Str::limit($p->title, 60) }}</td>
                                                <td>
                                                    <a href="{{ route('admin.products.edit', $p->slug) }}" target="_blank"
                                                       style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Alt مشکل‌دار --}}
                        @if($ps['items_no_alt']->count())
                            <div class="sa-card" style="margin-bottom:1rem">
                                <div class="sa-card-header" style="color:var(--sa-warning)">
                                    🟡 محصولات بدون Alt تصویر ({{ $ps['no_image_alt'] }} مورد)
                                </div>
                                <div class="sa-card-body" style="padding:0">
                                    <table class="sa-table">
                                        <thead><tr><th>#</th><th>عنوان</th><th>تصویر</th><th>عملیات</th></tr></thead>
                                        <tbody>
                                        @foreach($ps['items_no_alt'] as $p)
                                            <tr>
                                                <td style="color:var(--sa-muted);width:40px">#{{ $p->id }}</td>
                                                <td>{{ Str::limit($p->title, 50) }}</td>
                                                <td>
                                                    @if($p->image)
                                                        <img src="{{ asset($p->image) }}" style="width:36px;height:36px;object-fit:cover;border-radius:4px"
                                                             onerror="this.style.display='none'">
                                                    @else
                                                        <span style="color:var(--sa-danger);font-size:.72rem">بدون تصویر</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.products.edit', $p->slug) }}" target="_blank"
                                                       style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- خلاصه وضعیت --}}
                        <div class="sa-two-col">
                            <div class="sa-card">
                                <div class="sa-card-header">📊 خلاصه وضعیت Meta Title</div>
                                <div class="sa-card-body">
                                    <div class="sa-issue-row err"><div class="sa-issue-msg">بدون عنوان متا: {{ $ps['no_meta_title'] }}</div></div>
                                    <div class="sa-issue-row warn"><div class="sa-issue-msg">عنوان کوتاه (زیر ۳۰ کاراکتر): {{ $ps['short_meta_title'] }}</div></div>
                                    <div class="sa-issue-row warn"><div class="sa-issue-msg">عنوان بلند (بیش از ۶۵ کاراکتر): {{ $ps['long_meta_title'] }}</div></div>
                                </div>
                            </div>
                            <div class="sa-card">
                                <div class="sa-card-header">📊 خلاصه وضعیت Meta Description</div>
                                <div class="sa-card-body">
                                    <div class="sa-issue-row err"><div class="sa-issue-msg">بدون توضیحات متا: {{ $ps['no_meta_desc'] }}</div></div>
                                    <div class="sa-issue-row warn"><div class="sa-issue-msg">توضیحات کوتاه (زیر ۱۰۰ کاراکتر): {{ $ps['short_meta_desc'] }}</div></div>
                                    <div class="sa-issue-row warn"><div class="sa-issue-msg">توضیحات بلند (بیش از ۱۶۵ کاراکتر): {{ $ps['long_meta_desc'] }}</div></div>
                                </div>
                            </div>
                        </div>

                    </div>


                    {{-- ══════════════ TAB: مقالات ══════════════ --}}
                    <div class="sa-tab-panel" id="panel-posts">

                        <div class="sa-two-col" style="margin-bottom:1.2rem">
                            <div>
                                @php
                                    $pChecks = [
                                      ['label'=>'Meta Title', 'missing'=>$po['no_meta_title'], 'total'=>$po['total']],
                                      ['label'=>'Meta Description', 'missing'=>$po['no_meta_desc'], 'total'=>$po['total']],
                                      ['label'=>'تصویر شاخص', 'missing'=>$po['no_image'], 'total'=>$po['total']],
                                      ['label'=>'خلاصه (Summary)', 'missing'=>$po['no_summary'], 'total'=>$po['total']],
                                    ];
                                @endphp
                                @foreach($pChecks as $c)
                                    @php $pct = $c['total'] > 0 ? round((1 - $c['missing']/$c['total'])*100) : 100 @endphp
                                    <div style="margin-bottom:.8rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:var(--sa-muted);font-weight:600;margin-bottom:3px">
                                            <span>{{ $c['label'] }}</span>
                                            <span style="color:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="sa-progress">
                                            <div class="sa-progress-fill" style="width:{{ $pct }}%;background:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                @php
                                    $pChecks2 = [
                                      ['label'=>'محتوا (Content)', 'missing'=>$po['no_content'], 'total'=>$po['total']],
                                      ['label'=>'دسته‌بندی', 'missing'=>$po['no_category'], 'total'=>$po['total']],
                                      ['label'=>'تاریخ انتشار', 'missing'=>$po['no_publish_date'], 'total'=>$po['total']],
                                      ['label'=>'محتوای کافی (۳۰۰+ کلمه)', 'missing'=>$po['short_content'], 'total'=>$po['total']],
                                    ];
                                @endphp
                                @foreach($pChecks2 as $c)
                                    @php $pct = $c['total'] > 0 ? round((1 - $c['missing']/$c['total'])*100) : 100 @endphp
                                    <div style="margin-bottom:.8rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:var(--sa-muted);font-weight:600;margin-bottom:3px">
                                            <span>{{ $c['label'] }}</span>
                                            <span style="color:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="sa-progress">
                                            <div class="sa-progress-fill" style="width:{{ $pct }}%;background:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($po['items_no_meta']->count())
                            <div class="sa-card" style="margin-bottom:1rem">
                                <div class="sa-card-header" style="color:var(--sa-danger)">🔴 مقالات بدون Meta Title ({{ $po['no_meta_title'] }} مورد)</div>
                                <div class="sa-card-body" style="padding:0">
                                    <table class="sa-table">
                                        <thead><tr><th>#</th><th>عنوان مقاله</th><th>عملیات</th></tr></thead>
                                        <tbody>
                                        @foreach($po['items_no_meta'] as $p)
                                            <tr>
                                                <td style="color:var(--sa-muted);width:40px">#{{ $p->id }}</td>
                                                <td>{{ Str::limit($p->title, 60) }}</td>
                                                <td><a href="{{ route('admin.posts.edit', $p) }}" target="_blank" style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if($po['items_short']->count())
                            <div class="sa-card">
                                <div class="sa-card-header" style="color:var(--sa-warning)">🟡 مقالات با محتوای کم (زیر ۳۰۰ کلمه — {{ $po['short_content'] }} مورد)</div>
                                <div class="sa-card-body" style="padding:0">
                                    <table class="sa-table">
                                        <thead><tr><th>#</th><th>عنوان مقاله</th><th>کلمات</th><th>عملیات</th></tr></thead>
                                        <tbody>
                                        @foreach($po['items_short'] as $p)
                                            <tr>
                                                <td style="color:var(--sa-muted);width:40px">#{{ $p->id }}</td>
                                                <td>{{ Str::limit($p->title, 55) }}</td>
                                                <td><span class="sa-badge warn">{{ str_word_count(strip_tags($p->content ?? '')) }} کلمه</span></td>
                                                <td><a href="{{ route('admin.posts.edit', $p) }}" target="_blank" style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>


                    {{-- ══════════════ TAB: دسته‌بندی‌ها ══════════════ --}}
                    <div class="sa-tab-panel" id="panel-categories">

                        <div class="sa-two-col" style="margin-bottom:1.2rem">
                            @php
                                $catChecks = [
                                  ['label'=>'Meta Title', 'missing'=>$cs['no_meta_title'], 'total'=>$cs['total']],
                                  ['label'=>'Meta Description', 'missing'=>$cs['no_meta_desc'], 'total'=>$cs['total']],
                                  ['label'=>'تصویر', 'missing'=>$cs['no_image'], 'total'=>$cs['total']],
                                  ['label'=>'Slug', 'missing'=>$cs['no_slug'], 'total'=>$cs['total']],
                                ];
                            @endphp
                            <div>
                                @foreach($catChecks as $c)
                                    @php $pct = $c['total'] > 0 ? round((1 - $c['missing']/$c['total'])*100) : 100 @endphp
                                    <div style="margin-bottom:.8rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:var(--sa-muted);font-weight:600;margin-bottom:3px">
                                            <span>{{ $c['label'] }}</span>
                                            <span style="color:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="sa-progress">
                                            <div class="sa-progress-fill" style="width:{{ $pct }}%;background:{{ $pct>=80?'#28c76f':($pct>=50?'#ff9f43':'#ea5455') }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div style="display:flex;align-items:center;justify-content:center">
                                <div style="text-align:center">
                                    <div style="font-size:2.5rem;font-weight:900;color:var(--sa-primary)">{{ $cs['total'] }}</div>
                                    <div style="font-size:.75rem;color:var(--sa-muted);font-weight:700">دسته‌بندی کل</div>
                                    <div style="margin-top:.8rem">
                                        <span class="sa-badge err" style="font-size:.8rem;padding:4px 12px">{{ $cs['no_meta_title'] }} بدون Meta</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($cs['items_no_meta']->count())
                            <div class="sa-card">
                                <div class="sa-card-header" style="color:var(--sa-danger)">🔴 دسته‌بندی‌های بدون Meta Title</div>
                                <div class="sa-card-body" style="padding:0">
                                    <table class="sa-table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>نام دسته</th>
                                            <th>Slug</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($cs['items_no_meta'] as $category)
                                            {{-- فقط دسته‌های اصلی که ریشه هستند --}}
                                            @if(empty($category->meta_title) and empty($category->category_id))
                                                <tr>
                                                    <td style="color:var(--sa-muted);width:40px">#{{ $category->id }}</td>
                                                    <td>
                                                        <span style="color:var(--sa-danger);font-weight:700">🗁</span>
                                                        {{ $category->title }} - {{$category->category_id}}
                                                        @if($category->children->isNotEmpty())
                                                            <span style="color:var(--sa-muted);font-size:.65rem">({{ $category->children->count() }} زیردسته)</span>
                                                        @endif
                                                    </td>
                                                    <td><code style="font-size:.72rem">{{ $category->slug ?: '—' }}</code></td>
                                                    <td>
                                                        <a class="edit-category" data-category="{{$category->slug}}" target="_blank" style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a>
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- زیردسته‌ها --}}
                                            @foreach($category->children as $child)
                                                @if(empty($child->meta_title))
                                                    <tr>
                                                        <td style="color:var(--sa-muted);width:40px">#{{ $child->id }}</td>
                                                        <td style="padding-left:30px">
                                                            <span style="color:var(--sa-muted);margin-right:5px">┘─</span>
                                                            <span style="color:var(--sa-danger);font-weight:700">🗀</span>
                                                            {{ $child->title }}
                                                            <span style="color:var(--sa-muted);font-size:.65rem">(زیردسته {{ $category->title }})</span>
                                                        </td>
                                                        <td><code style="font-size:.72rem">{{ $child->slug ?: '—' }}</code></td>
                                                        <td>
                                                            <a class="edit-category" data-category="{{$child->slug}}"  style="font-size:.72rem;color:var(--sa-primary);font-weight:700">✏️ ویرایش</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>


                    {{-- ══════════════ TAB: کراول زنده ══════════════ --}}
                    <div class="sa-tab-panel" id="panel-crawler">

                        <div class="sa-crawler-form">
                            <input type="url" id="crawl-url" placeholder="https://example.com/products/my-product"
                                   value="{{ config('app.url') }}">
                            <button class="sa-crawl-btn" onclick="startCrawl()">🔍 تحلیل URL</button>
                        </div>

                        <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:1rem;flex-wrap:wrap">
                            <button onclick="crawlQuick('/')" class="sa-crawl-btn" style="background:#f0f0f8;color:var(--sa-primary);border:1.5px solid var(--sa-primary)">🏠 صفحه اصلی</button>
                            <button onclick="checkRobots()" class="sa-crawl-btn" style="background:#f0f0f8;color:#28c76f;border:1.5px solid #28c76f">🤖 robots.txt</button>
                            <button onclick="checkSitemap()" class="sa-crawl-btn" style="background:#f0f0f8;color:#ff9f43;border:1.5px solid #ff9f43">🗺 sitemap.xml</button>
                            <div class="sa-spinner" id="crawl-spinner"></div>
                        </div>

                        <div id="crawl-result"></div>
                        <div id="robots-result"></div>
                        <div id="sitemap-result"></div>

                    </div>


                    {{-- ══════════════ TAB: فنی ══════════════ --}}
                    <div class="sa-tab-panel" id="panel-technical">

                        <div class="sa-two-col">

                            <div class="sa-card">
                                <div class="sa-card-header">📐 چک‌لیست Schema</div>
                                <div class="sa-card-body">
                                    <p style="font-size:.8rem;color:var(--sa-muted);margin-bottom:.8rem">Schema‌های توصیه‌شده برای فروشگاه اینترنتی:</p>
                                    @php
                                        $schemas = [
                                          ['type'=>'Product','desc'=>'برای هر محصول — قیمت، موجودی، برند', 'priority'=>'ضروری'],
                                          ['type'=>'BreadcrumbList','desc'=>'مسیر پیمایش برای Rich Result', 'priority'=>'ضروری'],
                                          ['type'=>'Organization','desc'=>'اطلاعات کسب‌وکار در صفحه اصلی', 'priority'=>'ضروری'],
                                          ['type'=>'WebSite + SearchAction','desc'=>'سرچ‌باکس در نتایج گوگل', 'priority'=>'مهم'],
                                          ['type'=>'FAQPage','desc'=>'سوالات متداول — Featured Snippet', 'priority'=>'مهم'],
                                          ['type'=>'Article','desc'=>'برای هر مقاله بلاگ', 'priority'=>'مهم'],
                                          ['type'=>'Review / AggregateRating','desc'=>'ستاره‌ها در نتایج گوگل', 'priority'=>'مفید'],
                                          ['type'=>'LocalBusiness','desc'=>'اگر فروشگاه فیزیکی دارید', 'priority'=>'مفید'],
                                        ];
                                    @endphp
                                    @foreach($schemas as $s)
                                        <div style="display:flex;align-items:baseline;gap:.6rem;padding:.4rem 0;border-bottom:1px solid var(--sa-border);font-size:.78rem">
                                            <code style="font-weight:800;color:var(--sa-primary);flex-shrink:0">{{ $s['type'] }}</code>
                                            <span style="color:#555;flex:1">{{ $s['desc'] }}</span>
                                            <span class="sa-badge {{ $s['priority']==='ضروری'?'err':($s['priority']==='مهم'?'warn':'ok') }}">{{ $s['priority'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="sa-card">
                                <div class="sa-card-header">⚡ Core Web Vitals راهنما</div>
                                <div class="sa-card-body">
                                    @php
                                        $cwv = [
                                          ['metric'=>'LCP','name'=>'Largest Contentful Paint','good'=>'< 2.5s','bad'=>'> 4s','tip'=>'بهینه‌سازی تصویر Hero، پیش‌بارگذاری فونت'],
                                          ['metric'=>'FID','name'=>'First Input Delay','good'=>'< 100ms','bad'=>'> 300ms','tip'=>'کاهش JavaScript مسدودکننده'],
                                          ['metric'=>'CLS','name'=>'Cumulative Layout Shift','good'=>'< 0.1','bad'=>'> 0.25','tip'=>'width/height روی تصاویر، پرهیز از inject محتوا'],
                                          ['metric'=>'FCP','name'=>'First Contentful Paint','good'=>'< 1.8s','bad'=>'> 3s','tip'=>'Critical CSS inline، حذف render-blocking'],
                                          ['metric'=>'TTFB','name'=>'Time to First Byte','good'=>'< 200ms','bad'=>'> 600ms','tip'=>'کشینگ سرور، CDN، بهینه دیتابیس'],
                                        ];
                                    @endphp
                                    @foreach($cwv as $c)
                                        <div style="padding:.5rem 0;border-bottom:1px solid var(--sa-border);font-size:.77rem">
                                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:2px">
                                                <strong style="color:var(--sa-primary);width:35px">{{ $c['metric'] }}</strong>
                                                <span style="color:var(--sa-text);font-weight:600">{{ $c['name'] }}</span>
                                            </div>
                                            <div style="display:flex;gap:.5rem;margin-bottom:2px">
                                                <span class="sa-badge ok">✅ {{ $c['good'] }}</span>
                                                <span class="sa-badge err">❌ {{ $c['bad'] }}</span>
                                            </div>
                                            <div style="color:#888;font-size:.72rem">💡 {{ $c['tip'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <div class="sa-card">
                            <div class="sa-card-header">🔒 چک‌لیست فنی SEO</div>
                            <div class="sa-card-body">
                                @php
                                    $techChecks = [
                                      ['label'=>'HTTPS فعال است', 'status'=>str_starts_with(config('app.url'), 'https') ? 'ok' : 'err', 'tip'=>'HTTPS یک فاکتور رتبه‌بندی گوگل است'],
                                      ['label'=>'www vs non-www یکسان و ریدایرکت دارد', 'status'=>'warn', 'tip'=>'هر دو باید به یکی ریدایرکت ۳۰۱ داشته باشند'],
                                      ['label'=>'صفحه ۴۰۴ سفارشی دارد', 'status'=>'warn', 'tip'=>'صفحه ۴۰۴ زیبا UX را بهبود می‌دهد'],
                                      ['label'=>'فشرده‌سازی Gzip/Brotli', 'status'=>'warn', 'tip'=>'از طریق .htaccess یا nginx کنترل کنید'],
                                      ['label'=>'Cache-Control headers', 'status'=>'warn', 'tip'=>'فایل‌های استاتیک باید cache طولانی داشته باشند'],
                                      ['label'=>'تصاویر WebP/AVIF', 'status'=>'warn', 'tip'=>'فرمت‌های مدرن حجم را تا ۸۰٪ کاهش می‌دهند'],
                                      ['label'=>'Lazy Loading تصاویر', 'status'=>'warn', 'tip'=>'loading="lazy" روی تمام تصاویر زیر fold'],
                                      ['label'=>'Sitemap.xml ارسال به Google Search Console', 'status'=>'warn', 'tip'=>'در GSC → Sitemaps ثبت کنید'],
                                      ['label'=>'robots.txt درست پیکربندی شده', 'status'=>'warn', 'tip'=>'Sitemap را در robots.txt معرفی کنید'],
                                      ['label'=>'Canonical URLs در تمام صفحات', 'status'=>'warn', 'tip'=>'از محتوای تکراری جلوگیری می‌کند'],
                                    ];
                                @endphp
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
                                    @foreach($techChecks as $t)
                                        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.45rem .6rem;border-radius:7px;font-size:.76rem;font-weight:600;background:{{ $t['status']==='ok'?'#28c76f12':($t['status']==='err'?'#ea545512':'#ff9f4312') }};color:{{ $t['status']==='ok'?'#1a7a44':($t['status']==='err'?'#a33':'#774400') }}">
                                            <span>{{ $t['status']==='ok'?'✅':($t['status']==='err'?'❌':'⚠️') }}</span>
                                            <div>
                                                <div>{{ $t['label'] }}</div>
                                                <div style="font-weight:400;font-size:.68rem;opacity:.8;margin-top:1px">{{ $t['tip'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>


                    {{-- ══════════════ TAB: محتوای تکراری ══════════════ --}}
                    <div class="sa-tab-panel" id="panel-duplicates">

                        <div class="sa-two-col">
                            <div class="sa-card">
                                <div class="sa-card-header" style="color:var(--sa-danger)">♻️ Meta Title تکراری — محصولات</div>
                                <div class="sa-card-body" style="padding:0">
                                    @if($dbStats['duplicateMetaTitles']->count())
                                        <table class="sa-table">
                                            <thead><tr><th>Meta Title</th><th>تعداد تکرار</th></tr></thead>
                                            <tbody>
                                            @foreach($dbStats['duplicateMetaTitles'] as $d)
                                                <tr>
                                                    <td>{{ Str::limit($d->meta_title, 55) }}</td>
                                                    <td><span class="sa-badge err">{{ $d->cnt }}x</span></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="sa-card-body"><p class="sa-empty">✅ Meta Title تکراری یافت نشد</p></div>
                                    @endif
                                </div>
                            </div>

                            <div class="sa-card">
                                <div class="sa-card-header" style="color:var(--sa-warning)">♻️ Meta Title تکراری — مقالات</div>
                                <div class="sa-card-body" style="padding:0">
                                    @if($dbStats['duplicatePostMetaTitles']->count())
                                        <table class="sa-table">
                                            <thead><tr><th>Meta Title</th><th>تعداد تکرار</th></tr></thead>
                                            <tbody>
                                            @foreach($dbStats['duplicatePostMetaTitles'] as $d)
                                                <tr>
                                                    <td>{{ Str::limit($d->meta_title, 55) }}</td>
                                                    <td><span class="sa-badge warn">{{ $d->cnt }}x</span></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="sa-card-body"><p class="sa-empty">✅ Meta Title تکراری یافت نشد</p></div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="sa-card">
                            <div class="sa-card-header">💡 راهنمای جلوگیری از محتوای تکراری</div>
                            <div class="sa-card-body">
                                @php
                                    $dupTips = [
                                      ['label'=>'Canonical Tag', 'desc'=>'در صفحات با پارامتر (فیلتر، صفحه‌بندی) canonical به URL اصلی اضافه کنید.'],
                                      ['label'=>'Meta Title یکتا', 'desc'=>'هر صفحه باید Meta Title کاملاً متفاوت داشته باشد — از الگوی «خرید X | سایت Y» پرهیز کنید اگر X تکراری است.'],
                                      ['label'=>'Pagination', 'desc'=>'برای صفحات pagination از rel="next/prev" یا canonical به صفحه اول استفاده کنید.'],
                                      ['label'=>'URL Parameters', 'desc'=>'پارامترهای tracking را در Google Search Console معرفی کنید.'],
                                      ['label'=>'WWW vs non-WWW', 'desc'=>'Redirect 301 از یکی به دیگری تنظیم کنید.'],
                                      ['label'=>'HTTP vs HTTPS', 'desc'=>'همه ترافیک HTTP را به HTTPS ریدایرکت کنید.'],
                                    ];
                                @endphp
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                                    @foreach($dupTips as $t)
                                        <div style="background:#f8f8fb;border-radius:8px;padding:.6rem .8rem;font-size:.78rem">
                                            <div style="font-weight:800;color:var(--sa-primary);margin-bottom:2px">{{ $t['label'] }}</div>
                                            <div style="color:#555">{{ $t['desc'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>

                </div><!-- /tab container -->

            </div><!-- /content-body -->
        </div>
    </div>


    <!-- Edit Modal -->
    <div class="modal fade text-left" id="modal-edit" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ویرایش دسته بندی </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="edit-form" action="#">
                    @method('put')
                    <div class="modal-body">

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn personal-danger-btn waves-effect waves-light" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn personal-success-btn waves-effect waves-light">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery-ui','jquery.validate','ckeditor','jquery-tagsinput']])
@push('scripts')
    <script>
        // ── Tab Switcher ──────────────────────────────────────────────
        function saTab(el, panel) {
            document.querySelectorAll('.sa-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.sa-tab-panel').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('panel-' + panel).classList.add('active');
        }

        // ── Crawl URL ─────────────────────────────────────────────────
        function startCrawl() {
            const url = document.getElementById('crawl-url').value.trim();
            if (!url) return alert('آدرس URL را وارد کنید');
            crawlUrl(url);
        }

        function crawlQuick(path) {
            const base = '{{ rtrim(config("app.url"), "/") }}';
            document.getElementById('crawl-url').value = base + path;
            crawlUrl(base + path);
        }

        function crawlUrl(url) {
            const spinner = document.getElementById('crawl-spinner');
            const result  = document.getElementById('crawl-result');
            result.style.display = 'none';
            spinner.style.display = 'block';
            document.querySelector('.sa-crawl-btn').disabled = true;

            fetch('{{ route("admin.seo.crawl") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ url }),
            })
                .then(r => r.json())
                .then(res => {
                    spinner.style.display = 'none';
                    document.querySelector('.sa-crawl-btn').disabled = false;
                    result.style.display = 'block';

                    if (!res.success) {
                        result.innerHTML = `<div class="sa-issue-row err"><div class="sa-issue-msg">❌ ${res.error}</div></div>`;
                        return;
                    }

                    const d = res.data;
                    const scoreClass = d.score >= 75 ? 'good' : (d.score >= 45 ? 'warn' : 'bad');

                    let issuesHtml = d.issues.map(i => `
      <div class="sa-issue-row err">
        <div class="sa-issue-field">${i.field}</div>
        <div class="sa-issue-msg">${i.msg}</div>
        <div class="sa-issue-fix">💡 ${i.fix}</div>
      </div>`).join('');

                    let warnsHtml = d.warnings.map(w => `
      <div class="sa-issue-row warn">
        <div class="sa-issue-field">${w.field}</div>
        <div class="sa-issue-msg">${w.msg}</div>
        <div class="sa-issue-fix">💡 ${w.fix}</div>
      </div>`).join('');

                    let goodHtml = d.good.map(g => `
      <div class="sa-issue-row ok">✔ ${g}</div>`).join('');

                    // OG Tags
                    let ogHtml = Object.entries(d.og_tags || {}).map(([k,v]) =>
                        `<div style="padding:.3rem .5rem;font-size:.75rem;border-bottom:1px solid #eee"><strong style="color:#7367f0">og:${k}</strong> — ${v}</div>`
                    ).join('') || '<p style="color:#888;font-size:.78rem;padding:.5rem">OG تگ‌ها یافت نشد</p>';

                    result.innerHTML = `
      <div class="sa-card">
        <div class="crawl-result-header">
          <div class="sa-score-sm ${scoreClass}">${d.score}</div>
          <div class="crawl-meta">
            <div class="crawl-meta-url">🌐 ${d.url}</div>
            <div class="crawl-meta-info">HTTP ${d.status} &nbsp;·&nbsp; ${d.load_time}ms &nbsp;·&nbsp; ${d.html_size_kb}KB</div>
          </div>
          <div class="crawl-pills">
            <span class="crawl-pill">H1: ${d.h1_count}</span>
            <span class="crawl-pill">🖼 ${d.total_images} تصویر</span>
            <span class="crawl-pill">🔗 ${d.internal_links} داخلی</span>
            <span class="crawl-pill">🌍 ${d.external_links} خارجی</span>
            <span class="crawl-pill">📐 ${d.schema_count} Schema</span>
          </div>
        </div>
        <div class="sa-card-body">

          ${d.issues.length ? `<div class="sa-section-title err">🔴 مشکلات بحرانی (${d.issues.length})</div>${issuesHtml}` : ''}
          ${d.warnings.length ? `<div class="sa-section-title warn" style="margin-top:.8rem">🟡 هشدارها (${d.warnings.length})</div>${warnsHtml}` : ''}
          ${d.good.length ? `<div class="sa-section-title ok" style="margin-top:.8rem">🟢 موارد تأیید شده (${d.good.length})</div>${goodHtml}` : ''}

          <div class="sa-section-title" style="margin-top:1rem">📋 اطلاعات متا</div>
          <table class="sa-table" style="margin-bottom:.8rem">
            <tr><td style="color:var(--sa-muted);font-weight:600;width:35%">Title</td><td>${d.title || '—'} <span style="font-size:.7rem;color:#888">(${d.title_len} کاراکتر)</span></td></tr>
            <tr><td style="color:var(--sa-muted);font-weight:600">Meta Description</td><td>${d.meta_desc ? (d.meta_desc.substring(0,100)+'...') : '—'} <span style="font-size:.7rem;color:#888">(${d.meta_desc_len} کاراکتر)</span></td></tr>
            <tr><td style="color:var(--sa-muted);font-weight:600">Canonical</td><td><code style="font-size:.72rem">${d.canonical || '—'}</code></td></tr>
            <tr><td style="color:var(--sa-muted);font-weight:600">H1</td><td>${d.h1_text || '—'}</td></tr>
          </table>

          <div class="sa-section-title">📣 Open Graph Tags</div>
          ${ogHtml}

          <div style="margin-top:.8rem">
            <a href="https://search.google.com/test/rich-results?url=${encodeURIComponent(d.url)}" target="_blank"
               style="font-size:.78rem;color:var(--sa-primary);font-weight:700;margin-left:1rem">
              🧪 Google Rich Results Test ↗
            </a>
            <a href="https://pagespeed.web.dev/report?url=${encodeURIComponent(d.url)}" target="_blank"
               style="font-size:.78rem;color:var(--sa-success);font-weight:700;margin-left:1rem">
              ⚡ PageSpeed Insights ↗
            </a>
            <a href="https://www.google.com/webmasters/tools/mobile-friendly?url=${encodeURIComponent(d.url)}" target="_blank"
               style="font-size:.78rem;color:var(--sa-warning);font-weight:700">
              📱 Mobile Friendly Test ↗
            </a>
          </div>

        </div>
      </div>`;
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    document.querySelector('.sa-crawl-btn').disabled = false;
                    document.getElementById('crawl-result').style.display = 'block';
                    document.getElementById('crawl-result').innerHTML =
                        `<div class="sa-issue-row err"><div class="sa-issue-msg">❌ خطا: ${err.message}</div></div>`;
                });
        }

        // ── Robots.txt ────────────────────────────────────────────────
        function checkRobots() {
            const spinner = document.getElementById('crawl-spinner');
            spinner.style.display = 'block';
            fetch('{{ route("admin.seo.robots") }}')
                .then(r => r.json())
                .then(res => {
                    spinner.style.display = 'none';
                    const el = document.getElementById('robots-result');
                    if (!res.success) {
                        el.innerHTML = `<div class="sa-issue-row err"><div class="sa-issue-msg">❌ ${res.error}</div></div>`;
                        return;
                    }
                    const issHtml = res.issues.map(i => `<div class="sa-issue-row err">🔴 ${i}</div>`).join('');
                    const warnHtml = res.warnings.map(w => `<div class="sa-issue-row warn">🟡 ${w}</div>`).join('');
                    const goodHtml = res.good.map(g => `<div class="sa-issue-row ok">✅ ${g}</div>`).join('');
                    el.innerHTML = `
        <div class="sa-card" style="margin-top:1rem">
          <div class="sa-card-header">🤖 robots.txt — HTTP ${res.status}</div>
          <div class="sa-card-body">
            ${issHtml}${warnHtml}${goodHtml}
            <div class="sa-section-title" style="margin-top:.8rem">محتوای فایل</div>
            <div class="sa-mono">${res.content.replace(/</g,'&lt;')}</div>
          </div>
        </div>`;
                });
        }

        // ── Sitemap ───────────────────────────────────────────────────
        function checkSitemap() {
            const spinner = document.getElementById('crawl-spinner');
            spinner.style.display = 'block';
            fetch('{{ route("admin.seo.sitemap") }}')
                .then(r => r.json())
                .then(res => {
                    spinner.style.display = 'none';
                    const el = document.getElementById('sitemap-result');
                    if (!res.success) {
                        el.innerHTML = `<div class="sa-issue-row err"><div class="sa-issue-msg">❌ ${res.error}</div></div>`;
                        return;
                    }
                    const issHtml  = res.issues.map(i => `<div class="sa-issue-row err">🔴 ${i}</div>`).join('');
                    const warnHtml = res.warnings.map(w => `<div class="sa-issue-row warn">🟡 ${w}</div>`).join('');
                    const goodHtml = res.good.map(g => `<div class="sa-issue-row ok">✅ ${g}</div>`).join('');
                    el.innerHTML = `
        <div class="sa-card" style="margin-top:1rem">
          <div class="sa-card-header">🗺 sitemap.xml — ${res.url_count} URL</div>
          <div class="sa-card-body">
            ${issHtml}${warnHtml}${goodHtml}
            <div style="margin-top:.8rem">
              <a href="{{ config('app.url') }}/sitemap.xml" target="_blank"
                 style="font-size:.78rem;color:var(--sa-primary);font-weight:700">🔗 مشاهده sitemap.xml ↗</a>
            </div>
          </div>
        </div>`;
                });
        }





        $(document).on('click', '.edit-category', function() {
            var category = $(this).data('category');

            $.ajax({
                url: BASE_URL + '/categories/' + category + '/edit',
                type: 'get',
                data: {},
                success: function(data) {
                    $('#edit-form').attr('action', BASE_URL + '/categories/' + category);
                    $('#edit-form').data('category', category);

                    $('#modal-edit .modal-body').html(data);

                    jQuery('#modal-edit').modal('show');

                    $('.tags').tagsInput({
                        'defaultText': 'افزودن',
                        'width': '100%',
                        'autocomplete_url': BASE_URL + '/get-tags',
                    });

                    if (typeof CKEDITOR !== 'undefined') {
                        CKEDITOR.replace('category-description');
                    }

                    $('#filter_type').trigger('change');
                },
                beforeSend: function(xhr) {
                    block('.sa-card-body')
                },
                complete: function() {
                    unblock('.sa-card-body');
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });


        $('#modal-edit').on('shown.bs.modal', function() {
            $('#edit-title').focus();
            unblock('.sa-card-body');
        });


        $('#edit-form').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            var form = $(this);
            var category = form.data('category');

            if (typeof CKEDITOR !== 'undefined') {
                formData.append('description', CKEDITOR.instances['category-description'].getData())
            }

            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: formData,
                success: function(data) {
                    $('a[data-category=' + category + ']').closest('.dd-handle').find('.category-title').text(data.title);
                    $('[data-category=' + category + ']').data('category', data.slug);
                    $('[data-category=' + category + ']').attr('data-category', data.slug);
                    jQuery('#modal-edit').modal('hide');

                },
                beforeSend: function(xhr) {
                    block('#modal-edit .modal-content');
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                complete: function() {
                    unblock('#modal-edit .modal-content');
                },
                cache: false,
                contentType: false,
                processData: false
            });

        });

    </script>
@endpush
