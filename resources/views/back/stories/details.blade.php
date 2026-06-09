@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item"><i class="feather icon-home"></i> مدیریت</li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.stories.index') }}">مدیریت استوری‌ها</a>
                                    </li>
                                    <li class="breadcrumb-item active">جزئیات استوری</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ===== Hero Section: Story Info + Stats ===== --}}
                <div class="card story-hero-card mb-2" style="
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 16px;
                border: none;
                overflow: hidden;
                position: relative;
            ">
                    <div style="
                    position: absolute; inset: 0;
                    background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2280%22 cy=%2220%22 r=%2240%22 fill=%22rgba(255,255,255,0.05)%22/><circle cx=%2220%22 cy=%2280%22 r=%2230%22 fill=%22rgba(255,255,255,0.04)%22/></svg>');
                    pointer-events: none;
                "></div>
                    <div class="card-body p-2" style="position: relative; z-index: 1;">
                        <div class="row align-items-center">
                            {{-- Cover Image + Title --}}
                            <div class="col-md-3 text-center mb-2 mb-md-0">
                                <div style="
                                width: 110px; height: 110px;
                                border-radius: 20px;
                                border: 3px solid rgba(255,255,255,0.4);
                                overflow: hidden;
                                margin: 0 auto 12px;
                                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                            ">
                                    <img
                                        src="{{ $story->cover_image ? asset($story->cover_image) : asset('/empty.svg') }}"
                                        alt="{{ $story->title }}"
                                        style="width:100%; height:100%; object-fit:cover;"
                                    >
                                </div>
                                <h4 class="text-white font-weight-bold mb-1">{{ $story->title }}</h4>
                                <p class="mb-1">
                                    @if($story->expiry_date < now())
                                        <span class="badge" style="background:rgba(255,77,77,0.85); color:#fff; padding:5px 12px; border-radius:20px; font-size:12px;">
                                        <i class="fa fa-times-circle mr-1"></i> منقضی شده
                                    </span>
                                    @else
                                        <span class="badge" style="background:rgba(40,199,111,0.85); color:#fff; padding:5px 12px; border-radius:20px; font-size:12px;">
                                        <i class="fa fa-check-circle mr-1"></i> فعال تا {{ jdate($story->expiry_date)->format('d F Y') }}
                                    </span>
                                    @endif
                                </p>
                                <small style="color:rgba(255,255,255,0.7); font-size:11px; display:block;">
                                    <i class="fa fa-clock mr-1"></i> ایجاد: {{ $story->created_at->diffForHumans() }}
                                </small>
                                <small style="color:rgba(255,255,255,0.7); font-size:11px; display:block;">
                                    <i class="fa fa-sync mr-1"></i> بروزرسانی: {{ $story->published_at ? $story->published_at->diffForHumans() : $story->created_at->diffForHumans() }}
                                </small>
                            </div>

                            {{-- Stat Counters --}}
                            <div class="col-md-9">
                                <div class="row">
                                    @php
                                        $heroStats = [
                                            ['icon'=>'fa-eye',        'color'=>'#4fc3f7', 'value'=> number_format($viewsCount),              'label'=>'بازدید کل',        'sub'=>'مجموع تمام بازدیدها'],
                                            ['icon'=>'fa-user-check', 'color'=>'#81c784', 'value'=> number_format($realViewsCount),          'label'=>'بازدید واقعی',     'sub'=>'تعداد کاربران یکتا'],
                                            ['icon'=>'fa-heart',      'color'=>'#e57373', 'value'=> number_format($story->likes_count),      'label'=>'تعداد لایک‌ها',   'sub'=>'مجموع لایک‌های دریافتی'],
                                            ['icon'=>'fa-comment',    'color'=>'#ffb74d', 'value'=> number_format($story->comments()->count()),'label'=>'تعداد دیدگاه‌ها','sub'=>'مجموع نظرات ثبت‌شده'],
                                        ];
                                    @endphp
                                    @foreach($heroStats as $stat)
                                        <div class="col-6 col-md-3 mb-2">
                                            <div style="
                                        background: rgba(255,255,255,0.12);
                                        backdrop-filter: blur(10px);
                                        border-radius: 14px;
                                        border: 1px solid rgba(255,255,255,0.2);
                                        padding: 18px 10px;
                                        text-align: center;
                                        transition: transform .2s;
                                    " onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                                                <div style="
                                            width:44px; height:44px; border-radius:50%;
                                            background:rgba(255,255,255,0.15);
                                            display:flex; align-items:center; justify-content:center;
                                            margin: 0 auto 10px;
                                        ">
                                                    <i class="fa {{ $stat['icon'] }}" style="font-size:18px; color:{{ $stat['color'] }};"></i>
                                                </div>
                                                <h3 style="color:#fff; font-weight:700; margin-bottom:2px; font-size:22px;">{{ $stat['value'] }}</h3>
                                                <p style="color:rgba(255,255,255,0.9); margin-bottom:2px; font-size:13px; font-weight:600;">{{ $stat['label'] }}</p>
                                                <small style="color:rgba(255,255,255,0.6); font-size:11px;">{{ $stat['sub'] }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Interaction Stats Row ===== --}}
                <div class="row">

                    {{-- Product Clicks --}}
                    <div class="col-md-4 mb-2">
                        <div class="card h-100" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                            <div class="card-body text-center p-2">
                                <div style="
                                width:56px; height:56px; border-radius:50%;
                                background:linear-gradient(135deg,#f6d365,#fda085);
                                display:flex; align-items:center; justify-content:center;
                                margin:0 auto 12px;
                                box-shadow: 0 4px 15px rgba(253,160,133,0.4);
                            ">
                                    <i class="fa fa-shopping-cart" style="font-size:22px; color:#fff;"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-0" style="font-size:15px;">کلیک روی محصول</h5>
                                <h2 style="font-size:36px; font-weight:800; color:#fda085; margin:6px 0;">{{ number_format($productClicks) }}</h2>
                                <p class="text-muted mb-2" style="font-size:13px;">تعداد کلیک روی محصول</p>
                                <button type="button"
                                        class="btn btn-sm"
                                        data-toggle="modal" data-target="#productClicksModal"
                                        style="border-radius:20px; background:linear-gradient(135deg,#f6d365,#fda085); color:#fff; border:none; padding:6px 18px; font-size:13px;">
                                    <i class="fa fa-info-circle mr-1"></i> جزئیات بیشتر
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Widget Clicks --}}
                    <div class="col-md-4 mb-2">
                        <div class="card h-100" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                            <div class="card-body text-center p-2">
                                <div style="
                                width:56px; height:56px; border-radius:50%;
                                background:linear-gradient(135deg,#a18cd1,#fbc2eb);
                                display:flex; align-items:center; justify-content:center;
                                margin:0 auto 12px;
                                box-shadow: 0 4px 15px rgba(161,140,209,0.4);
                            ">
                                    <i class="fa fa-link" style="font-size:22px; color:#fff;"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-0" style="font-size:15px;">کلیک روی ویجت</h5>
                                <h2 style="font-size:36px; font-weight:800; color:#a18cd1; margin:6px 0;">{{ number_format($widgetClicks) }}</h2>
                                <p class="text-muted mb-2" style="font-size:13px;">تعداد کلیک روی ویجت</p>
                                <button type="button"
                                        class="btn btn-sm"
                                        data-toggle="modal" data-target="#widgetClicksModal"
                                        style="border-radius:20px; background:linear-gradient(135deg,#a18cd1,#fbc2eb); color:#fff; border:none; padding:6px 18px; font-size:13px;">
                                    <i class="fa fa-info-circle mr-1"></i> جزئیات بیشتر
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Stats --}}
                    <div class="col-md-4 mb-2">
                        <div class="card h-100" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="
                                    width:40px; height:40px; border-radius:50%;
                                    background:linear-gradient(135deg,#43e97b,#38f9d7);
                                    display:flex; align-items:center; justify-content:center;
                                    margin-left:10px;
                                    box-shadow: 0 4px 15px rgba(67,233,123,0.35);
                                ">
                                        <i class="fa fa-chart-line" style="font-size:16px; color:#fff;"></i>
                                    </div>
                                    <h6 class="font-weight-bold mb-0">پیشرفت مشاهده</h6>
                                </div>

                                @php
                                    $progressItems = [
                                        ['pct'=>25,  'count'=>$progressStats['progress_25'],  'color'=>'#4fc3f7', 'bg'=>'rgba(79,195,247,0.12)'],
                                        ['pct'=>50,  'count'=>$progressStats['progress_50'],  'color'=>'#667eea', 'bg'=>'rgba(102,126,234,0.12)'],
                                        ['pct'=>75,  'count'=>$progressStats['progress_75'],  'color'=>'#fda085', 'bg'=>'rgba(253,160,133,0.12)'],
                                        ['pct'=>100, 'count'=>$progressStats['progress_100'], 'color'=>'#43e97b', 'bg'=>'rgba(67,233,123,0.12)'],
                                    ];
                                    $maxProg = max(array_column($progressItems,'count')) ?: 1;
                                @endphp

                                @foreach($progressItems as $p)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size:12px; font-weight:600; color:#555;">{{ $p['pct'] }}% مشاهده</span>
                                            <span style="
                                        font-size:12px; font-weight:700;
                                        background:{{ $p['bg'] }}; color:{{ $p['color'] }};
                                        padding:2px 10px; border-radius:10px;
                                    ">{{ number_format($p['count']) }}</span>
                                        </div>
                                        <div style="background:#f0f0f0; border-radius:8px; height:8px; overflow:hidden;">
                                            <div style="
                                        height:100%;
                                        width:{{ $maxProg > 0 ? round(($p['count']/$maxProg)*100) : 0 }}%;
                                        background:{{ $p['color'] }};
                                        border-radius:8px;
                                        transition: width .6s ease;
                                    "></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Likes Table ===== --}}
                <div class="card mb-2" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                    <div class="card-header d-flex align-items-center" style="
                    background:linear-gradient(135deg,#ff6b6b,#ee0979);
                    border-radius:14px 14px 0 0;
                    border:none; padding:16px 20px;
                ">
                        <div style="
                        width:36px; height:36px; border-radius:50%;
                        background:rgba(255,255,255,0.2);
                        display:flex; align-items:center; justify-content:center;
                        margin-left:10px;
                    ">
                            <i class="fa fa-heart" style="color:#fff; font-size:16px;"></i>
                        </div>
                        <h5 class="mb-0 text-white font-weight-bold">لیست افرادی که لایک کرده‌اند</h5>
                        <span class="badge ml-auto" style="background:rgba(255,255,255,0.25); color:#fff; padding:6px 14px; border-radius:20px; font-size:13px;">
                        {{ number_format($story->likes_count) }} لایک
                    </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size:14px;">
                                <thead>
                                <tr style="background:#f8f9fc;">
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">کاربر</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">تاریخ</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">نوع</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">دستگاه</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آی پی</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($likes as $like)
                                    <tr style="transition:background .15s;" onmouseover="this.style.background='#f8f9fc'" onmouseout="this.style.background=''">
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <div class="d-flex align-items-center">
                                                <div style="
                                                width:34px; height:34px; border-radius:50%;
                                                background:linear-gradient(135deg,#667eea,#764ba2);
                                                display:flex; align-items:center; justify-content:center;
                                                margin-left:10px; flex-shrink:0;
                                            ">
                                                    <i class="fa fa-user" style="color:#fff; font-size:13px;"></i>
                                                </div>
                                                <span class="font-weight-600">
                                                @if($like->user)
                                                        {{ $like->user->fullname ?? $like->user->name }}
                                                    @elseif($like->admin)
                                                        {{ $like->admin->fullname }}
                                                        <span class="badge badge-primary" style="font-size:10px;">ادمین</span>
                                                    @else
                                                        کاربر مهمان
                                                    @endif
                                            </span>
                                            </div>
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666;">
                                            <i class="fa fa-calendar-alt mr-1 text-muted"></i>
                                            {{ jdate($like->created_at)->format('d F Y H:i') }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            @if($like->is_guest)
                                                <span style="background:rgba(255,193,7,0.15); color:#e6a817; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                                                <i class="fa fa-user-secret mr-1"></i> مهمان
                                            </span>
                                            @else
                                                <span style="background:rgba(40,199,111,0.15); color:#28c76f; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                                                <i class="fa fa-user-check mr-1"></i> عضو
                                            </span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666;">
                                            <i class="fa fa-{{ $like->device_type === 'mobile' ? 'mobile-alt' : ($like->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }} mr-1 text-primary"></i>
                                            {{ $like->device_type }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <code style="background:#f0f0f0; padding:3px 8px; border-radius:6px; font-size:12px;">{{ $like->ip_address }}</code>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4" style="color:#aaa;">
                                            <i class="fa fa-heart-broken fa-2x d-block mb-2"></i>
                                            هیچ لایکی ثبت نشده است
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($likes->hasPages())
                            <div class="p-2 border-top">{{ $likes->links() }}</div>
                        @endif
                    </div>
                </div>

                {{-- ===== Comments Management ===== --}}
                <div id="comments" class="card mb-2" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                    <div class="card-header d-flex align-items-center" style="
                    background:linear-gradient(135deg,#667eea,#764ba2);
                    border-radius:14px 14px 0 0;
                    border:none; padding:16px 20px;
                ">
                        <div style="
                        width:36px; height:36px; border-radius:50%;
                        background:rgba(255,255,255,0.2);
                        display:flex; align-items:center; justify-content:center;
                        margin-left:10px;
                    ">
                            <i class="fa fa-comments" style="color:#fff; font-size:16px;"></i>
                        </div>
                        <h5 class="mb-0 text-white font-weight-bold">مدیریت دیدگاه‌ها</h5>
                        <span class="badge ml-auto" style="background:rgba(255,255,255,0.25); color:#fff; padding:6px 14px; border-radius:20px; font-size:13px;">
                        {{ number_format($story->comments()->count()) }} دیدگاه
                    </span>
                    </div>

                    {{-- Bulk Actions Bar --}}
                    <div class="collapse datatable-actions" style="background:#fff7e6; border-bottom:1px solid #ffe0a0;">
                        <div class="d-flex align-items-center flex-wrap p-2 gap-2">
                        <span class="font-weight-bold text-danger ml-2">
                            <i class="fa fa-check-square mr-1"></i>
                            <span id="datatable-selected-rows">0</span> مورد انتخاب شده
                        </span>
                            <div class="form-group mb-0 mr-2" style="min-width:180px;">
                                <select name="comment_status" id="comment_status" class="form-control form-control-sm" style="border-radius:8px;">
                                    <option value="approved">قبول همه</option>
                                    <option value="rejected">رد همه</option>
                                    <option value="deleted">حذف همه</option>
                                </select>
                            </div>
                            <button class="btn btn-sm personal-danger-btn" type="button"
                                    data-toggle="modal" data-target="#multiple-operation-modal"
                                    style="border-radius:8px; padding:7px 18px;">
                                <i class="fa fa-bolt mr-1"></i> انجام عملیات
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size:14px;">
                                <thead>
                                <tr style="background:#f8f9fc;">
                                    <th style="padding:14px 20px; border-bottom:2px solid #eee; width:40px;">
                                        <fieldset class="checkbox mb-0">
                                            <div class="vs-checkbox-con vs-checkbox-primary checkbox-all">
                                                <input type="checkbox">
                                                <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                            </div>
                                        </fieldset>
                                    </th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">کاربر</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">دیدگاه</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">تاریخ</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">وضعیت</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($comments as $comment)
                                    <tr id="comment-{{ $comment->id }}" data-comment-id="{{ $comment->id }}"
                                        style="transition:background .15s;"
                                        onmouseover="this.style.background='#f8f9fc'" onmouseout="this.style.background=''">
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <fieldset class="checkbox mb-0">
                                                <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                    <input type="checkbox" value="{{ $comment->id }}">
                                                    <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                                </div>
                                            </fieldset>
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <div class="d-flex align-items-center">
                                                <div style="
                                                width:34px; height:34px; border-radius:50%;
                                                background:linear-gradient(135deg,#667eea,#764ba2);
                                                display:flex; align-items:center; justify-content:center;
                                                margin-left:10px; flex-shrink:0;
                                            ">
                                                    <i class="fa fa-user" style="color:#fff; font-size:13px;"></i>
                                                </div>
                                                <div>
                                                <span class="font-weight-600">
                                                    @if($comment->user)
                                                        {{ $comment->user->fullname ?? $comment->user->name }}
                                                        @if($comment->user->is_admin)
                                                            <span class="badge badge-primary" style="font-size:10px;">ادمین</span>
                                                        @endif
                                                    @else
                                                        کاربر مهمان
                                                    @endif
                                                </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; max-width:280px;">
                                            <p class="mb-0 text-truncate" style="color:#555; font-size:13px;" title="{{ $comment->comment }}">
                                                {{ $comment->comment }}
                                            </p>
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666; white-space:nowrap;">
                                            <i class="fa fa-calendar-alt mr-1 text-muted"></i>
                                            {{ jdate($comment->created_at)->format('d F Y H:i') }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;" class="comment-status">
                                            @if($comment->status == 'approved')
                                                <span style="background:rgba(40,199,111,0.15); color:#28c76f; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                                                <i class="fa fa-check-circle mr-1"></i> تایید شده
                                            </span>
                                            @elseif($comment->status == 'rejected')
                                                <span style="background:rgba(234,84,85,0.15); color:#ea5455; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                                                <i class="fa fa-times-circle mr-1"></i> رد شده
                                            </span>
                                            @else
                                                <span style="background:rgba(255,159,67,0.15); color:#ff9f43; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                                                <i class="fa fa-clock mr-1"></i> در انتظار تایید
                                            </span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <div class="d-flex" style="gap:6px;">
                                                <button class="btn btn-sm approve-comment"
                                                        data-action="{{ route('admin.stories.comments.status', $comment) }}"
                                                        data-id="{{ $comment->id }}"
                                                        style="{{ $comment->status == 'approved' ? 'display:none;' : '' }}
                                                       background:rgba(40,199,111,0.15); color:#28c76f; border:1px solid rgba(40,199,111,0.3);
                                                       border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; white-space:nowrap;">
                                                    <i class="fa fa-check mr-1"></i> تایید
                                                </button>
                                                <button class="btn btn-sm reject-comment"
                                                        data-action="{{ route('admin.stories.comments.status', $comment) }}"
                                                        data-id="{{ $comment->id }}"
                                                        style="{{ $comment->status == 'rejected' ? 'display:none;' : '' }}
                                                       background:rgba(255,159,67,0.15); color:#ff9f43; border:1px solid rgba(255,159,67,0.3);
                                                       border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; white-space:nowrap;">
                                                    <i class="fa fa-times mr-1"></i> رد
                                                </button>
                                                <button class="btn btn-sm delete-comment"
                                                        data-toggle="modal" data-target="#delete-modal"
                                                        data-id="{{ $comment->id }}"
                                                        data-action="{{ route('admin.stories.comments.destroy', $comment) }}"
                                                        style="background:rgba(234,84,85,0.15); color:#ea5455; border:1px solid rgba(234,84,85,0.3);
                                                       border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; white-space:nowrap;">
                                                    <i class="fa fa-trash mr-1"></i> حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4" style="color:#aaa;">
                                            <i class="fa fa-comment-slash fa-2x d-block mb-2"></i>
                                            هیچ دیدگاهی ثبت نشده است
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($comments->hasPages())
                            <div class="p-2 border-top">{{ $comments->links() }}</div>
                        @endif
                    </div>
                </div>

                {{-- ===== Viewers Table ===== --}}
                <div class="card mb-2" style="border-radius:14px; border:none; box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                    <div class="card-header d-flex align-items-center flex-wrap" style="
                    background:linear-gradient(135deg,#11998e,#38ef7d);
                    border-radius:14px 14px 0 0;
                    border:none; padding:16px 20px;
                ">
                        <div style="
                        width:36px; height:36px; border-radius:50%;
                        background:rgba(255,255,255,0.2);
                        display:flex; align-items:center; justify-content:center;
                        margin-left:10px;
                    ">
                            <i class="fa fa-users" style="color:#fff; font-size:16px;"></i>
                        </div>
                        <h5 class="mb-0 text-white font-weight-bold">لیست بازدیدکنندگان</h5>
                        <div class="ml-auto d-flex" style="gap:8px; flex-wrap:wrap;">
                        <span style="background:rgba(255,255,255,0.25); color:#fff; padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600;">
                            <i class="fa fa-eye mr-1"></i> کل: {{ number_format($story->views_count) }}
                        </span>
                            <span style="background:rgba(255,255,255,0.25); color:#fff; padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600;">
                            <i class="fa fa-user-check mr-1"></i> یکتا: {{ number_format($realViewsCount) }}
                        </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size:14px;">
                                <thead>
                                <tr style="background:#f8f9fc;">
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">کاربر</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">تعداد بازدید</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آخرین بازدید</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">اولین بازدید</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">دستگاه</th>
                                    <th style="padding:14px 20px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آی پی</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($viewers as $view)
                                    <tr style="transition:background .15s;"
                                        onmouseover="this.style.background='#f8f9fc'" onmouseout="this.style.background=''">
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            @if($view->user)
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $view->user->image_url }}"
                                                         class="rounded-circle" width="36" height="36"
                                                         style="object-fit:cover; border:2px solid #667eea; margin-left:10px; flex-shrink:0;">
                                                    <div>
                                                        <strong>{{ $view->user->fullname ?? $view->user->name }}</strong>
                                                        @if($view->user->is_admin)
                                                            <span class="badge badge-primary" style="font-size:10px;">ادمین</span>
                                                        @endif
                                                        <small class="text-muted d-block">{{ $view->user->email }}</small>
                                                    </div>
                                                </div>
                                            @elseif($view->admin)
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset($view->admin->avatar ?? 'default-avatar.jpg') }}"
                                                         class="rounded-circle" width="36" height="36"
                                                         style="object-fit:cover; border:2px solid #ea5455; margin-left:10px; flex-shrink:0;">
                                                    <div>
                                                        <strong>{{ $view->admin->fullname }}</strong>
                                                        <span class="badge badge-danger" style="font-size:10px;">ادمین</span>
                                                        <small class="text-muted d-block">{{ $view->admin->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <div style="
                                                    width:36px; height:36px; border-radius:50%;
                                                    background:linear-gradient(135deg,#bdc3c7,#95a5a6);
                                                    display:flex; align-items:center; justify-content:center;
                                                    margin-left:10px; flex-shrink:0;
                                                ">
                                                        <i class="fa fa-user" style="color:#fff; font-size:14px;"></i>
                                                    </div>
                                                    <div>
                                                        <strong>کاربر مهمان</strong>
                                                        <small class="text-muted d-block">نشست: {{ substr($view->session_id, 0, 10) }}...</small>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                        <span style="
                                            background:linear-gradient(135deg,#667eea,#764ba2);
                                            color:#fff; padding:4px 14px; border-radius:20px;
                                            font-size:13px; font-weight:700;
                                        ">{{ number_format($view->count) }}</span>
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666; white-space:nowrap;">
                                            <i class="fa fa-clock mr-1 text-muted"></i>
                                            {{ jdate($view->last_interacted_at ?? $view->created_at)->format('d F Y H:i') }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666; white-space:nowrap;">
                                            <i class="fa fa-calendar-alt mr-1 text-muted"></i>
                                            {{ jdate($view->created_at)->format('d F Y H:i') }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle; color:#666;">
                                            @php
                                                $deviceIcon = $view->device_type === 'mobile' ? 'mobile-alt' : ($view->device_type === 'tablet' ? 'tablet-alt' : 'desktop');
                                                $deviceLabel = $view->device_type === 'mobile' ? 'موبایل' : ($view->device_type === 'tablet' ? 'تبلت' : 'کامپیوتر');
                                                $deviceColor = $view->device_type === 'mobile' ? '#667eea' : ($view->device_type === 'tablet' ? '#fda085' : '#43e97b');
                                            @endphp
                                            <i class="fa fa-{{ $deviceIcon }}" style="color:{{ $deviceColor }}; margin-left:5px;"></i>
                                            {{ $deviceLabel }}
                                        </td>
                                        <td style="padding:12px 20px; vertical-align:middle;">
                                            <code style="background:#f0f0f0; padding:3px 8px; border-radius:6px; font-size:12px;">{{ $view->ip_address ?? '-' }}</code>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4" style="color:#aaa;">
                                            <i class="fa fa-eye-slash fa-2x d-block mb-2"></i>
                                            هیچ بازدیدی ثبت نشده است
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($viewers->hasPages())
                            <div class="p-2 border-top">{{ $viewers->links() }}</div>
                        @endif
                    </div>
                </div>

            </div>{{-- /content-body --}}
        </div>{{-- /content-wrapper --}}
    </div>{{-- /app-content --}}


    {{-- ===== Modal: Product Clicks ===== --}}
    <div class="modal fade" id="productClicksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:linear-gradient(135deg,#f6d365,#fda085); border:none; padding:18px 24px;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="fa fa-shopping-cart mr-2"></i> جزئیات کلیک روی محصول
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8; font-size:22px;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size:14px;">
                            <thead>
                            <tr style="background:#f8f9fc;">
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">کاربر</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">تعداد کلیک</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آخرین کلیک</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آدرس</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">دستگاه</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آی پی</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $productClickDetails = \App\Models\StoryInteraction::where('story_id', $story->id)
                                    ->where('type', 'product_click')
                                    ->orderBy('count', 'desc')
                                    ->get();
                            @endphp
                            @forelse($productClickDetails as $click)
                                <tr>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        @if($click->user)
                                            {{ $click->user->fullname ?? $click->user->name }}
                                        @elseif($click->admin)
                                            {{ $click->admin->fullname }}
                                            <span class="badge badge-danger" style="font-size:10px;">ادمین</span>
                                        @else
                                            <span class="text-muted">کاربر مهمان</span>
                                            @if($click->session_id)
                                                <small class="text-muted d-block">نشست: {{ substr($click->session_id, 0, 10) }}...</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                    <span style="background:rgba(253,160,133,0.2); color:#fda085; padding:3px 12px; border-radius:20px; font-size:13px; font-weight:700;">
                                        {{ number_format($click->count) }}
                                    </span>
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle; color:#666; white-space:nowrap;">
                                        {{ jdate($click->last_interacted_at ?? $click->created_at)->format('d F Y H:i') }}
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        <a href="{{ $click->target_url }}" target="_blank"
                                           style="color:#667eea; font-weight:600; text-decoration:none;">
                                            <i class="fa fa-external-link-alt mr-1"></i> نمایش
                                        </a>
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle; color:#666;">
                                        <i class="fa fa-{{ $click->device_type === 'mobile' ? 'mobile-alt' : ($click->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }} mr-1 text-primary"></i>
                                        {{ $click->device_type }}
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        <code style="background:#f0f0f0; padding:3px 8px; border-radius:6px; font-size:12px;">{{ $click->ip_address ?? '-' }}</code>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color:#aaa;">
                                        <i class="fa fa-mouse-pointer fa-2x d-block mb-2"></i>
                                        هیچ کلیکی ثبت نشده است
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #eee; padding:12px 20px;">
                    <button type="button" class="btn btn-sm" data-dismiss="modal"
                            style="background:#f0f0f0; color:#555; border:none; border-radius:8px; padding:8px 20px; font-weight:600;">
                        <i class="fa fa-times mr-1"></i> بستن
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Widget Clicks ===== --}}
    <div class="modal fade" id="widgetClicksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:linear-gradient(135deg,#a18cd1,#fbc2eb); border:none; padding:18px 24px;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="fa fa-link mr-2"></i> جزئیات کلیک روی ویجت
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8; font-size:22px;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size:14px;">
                            <thead>
                            <tr style="background:#f8f9fc;">
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">کاربر</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">تعداد کلیک</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آخرین کلیک</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آدرس</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">دستگاه</th>
                                <th style="padding:12px 18px; font-weight:700; color:#555; border-bottom:2px solid #eee;">آی پی</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $widgetClickDetails = \App\Models\StoryInteraction::where('story_id', $story->id)
                                    ->where('type', 'widget_click')
                                    ->orderBy('count', 'desc')
                                    ->get();
                            @endphp
                            @forelse($widgetClickDetails as $click)
                                <tr>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        @if($click->user)
                                            {{ $click->user->fullname ?? $click->user->name }}
                                        @elseif($click->admin)
                                            {{ $click->admin->fullname }}
                                            <span class="badge badge-danger" style="font-size:10px;">ادمین</span>
                                        @else
                                            <span class="text-muted">کاربر مهمان</span>
                                        @endif
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                    <span style="background:rgba(161,140,209,0.2); color:#a18cd1; padding:3px 12px; border-radius:20px; font-size:13px; font-weight:700;">
                                        {{ number_format($click->count) }}
                                    </span>
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle; color:#666; white-space:nowrap;">
                                        {{ jdate($click->last_interacted_at ?? $click->created_at)->format('d F Y H:i') }}
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        <a href="{{ $click->target_url }}" target="_blank"
                                           style="color:#a18cd1; font-weight:600; text-decoration:none;">
                                            <i class="fa fa-external-link-alt mr-1"></i> نمایش
                                        </a>
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle; color:#666;">
                                        <i class="fa fa-{{ $click->device_type === 'mobile' ? 'mobile-alt' : ($click->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }} mr-1 text-primary"></i>
                                        {{ $click->device_type }}
                                    </td>
                                    <td style="padding:11px 18px; vertical-align:middle;">
                                        <code style="background:#f0f0f0; padding:3px 8px; border-radius:6px; font-size:12px;">{{ $click->ip_address ?? '-' }}</code>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color:#aaa;">
                                        <i class="fa fa-mouse-pointer fa-2x d-block mb-2"></i>
                                        هیچ کلیکی ثبت نشده است
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #eee; padding:12px 20px;">
                    <button type="button" class="btn btn-sm" data-dismiss="modal"
                            style="background:#f0f0f0; color:#555; border:none; border-radius:8px; padding:8px 20px; font-weight:600;">
                        <i class="fa fa-times mr-1"></i> بستن
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Multiple Operation ===== --}}
    <div class="modal fade text-left" id="multiple-operation-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:linear-gradient(135deg,#ff6b6b,#ee0979); border:none; padding:16px 20px;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="fa fa-exclamation-triangle mr-2"></i> آیا مطمئن هستید؟
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:20px; color:#555; text-align:center;">
                    <i class="fa fa-bolt fa-3x mb-3" style="color:#ff9f43; display:block;"></i>
                    عملیات گروهی روی موارد انتخاب‌شده اعمال خواهد شد.
                </div>
                <div class="modal-footer" style="border-top:1px solid #eee; padding:12px 20px;">
                    <form action="{{ route('admin.stories.comments.multipleOperation') }}" method="post"
                          id="story-multiple-operation-form" class="w-100 d-flex justify-content-between">
                        @csrf
                        <button type="button" class="btn btn-sm personal-success-btn" data-dismiss="modal"
                                style="border-radius:8px; padding:8px 20px;">
                            <i class="fa fa-times mr-1"></i> خیر
                        </button>
                        <button type="submit" class="btn btn-sm personal-danger-btn"
                                style="border-radius:8px; padding:8px 20px; background:linear-gradient(135deg,#ff6b6b,#ee0979); border:none; color:#fff; font-weight:600;">
                            <i class="fa fa-check mr-1"></i> بله، انجام شود
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Delete Comment ===== --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:linear-gradient(135deg,#ea5455,#c0392b); border:none; padding:16px 20px;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="fa fa-trash mr-2"></i> حذف دیدگاه
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:20px; text-align:center;">
                    <i class="fa fa-exclamation-circle fa-3x mb-3 d-block" style="color:#ea5455;"></i>
                    <p style="color:#555; margin:0;">با حذف دیدگاه دیگر قادر به بازیابی آن نخواهید بود.</p>
                </div>
                <div class="modal-footer" style="border-top:1px solid #eee; padding:12px 20px;">
                    <form action="#" id="comment-delete-form" class="w-100 d-flex justify-content-between">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn btn-sm personal-success-btn" data-dismiss="modal"
                                style="border-radius:8px; padding:8px 20px;">
                            <i class="fa fa-times mr-1"></i> خیر
                        </button>
                        <button type="submit" class="btn btn-sm"
                                style="border-radius:8px; padding:8px 20px; background:linear-gradient(135deg,#ea5455,#c0392b); border:none; color:#fff; font-weight:600;">
                            <i class="fa fa-trash mr-1"></i> بله، حذف شود
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/stories/details.js') }}"></script>
@endpush
