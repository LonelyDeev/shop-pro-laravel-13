@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/packages/index.css') }}">
@endpush

@section('content')
    <div class="app-content content pkg-page">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            {{-- Breadcrumbs --}}
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item active">بازار پکیج‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- Hero header --}}
                <div class="pkg-hero mb-3">
                    <div class="pkg-hero-bg"></div>
                    <div class="pkg-hero-content d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <div class="pkg-hero-icon mr-1">
                                <i class="feather icon-package"></i>
                            </div>
                            <div>
                                <h3 class="mb-0">بازار پکیج‌ها</h3>
                                <small>پکیج‌های آماده را نصب و مدیریت کنید</small>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.packages.installed') }}" class="btn pkg-btn-soft">
                                <i class="feather icon-grid"></i> ماژول‌های نصب‌شده
                            </a>
                            <button type="button" id="btn-check-updates" class="btn pkg-btn-outline ml-1">
                                <i class="feather icon-refresh-cw"></i> بررسی آپدیت‌ها
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Stat cards --}}
                @php
                    $total       = count($packages);
                    $freeCount   = collect($packages)->where('is_free', true)->count();
                    $paidCount   = $total - $freeCount;
                    $installedCount = count($installedMap);
                @endphp

                <div class="row match-height mb-3">
                    <div class="col-6 col-xl-3">
                        <div class="pkg-stat" style="--accent:#6366f1; --accent-soft:rgba(99,102,241,0.1)">
                            <div class="pkg-stat-icon"><i class="feather icon-package"></i></div>
                            <div class="pkg-stat-meta">
                                <span class="pkg-stat-value">{{ $total }}</span>
                                <span class="pkg-stat-label">کل پکیج‌ها</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="pkg-stat" style="--accent:#0ea5e9; --accent-soft:rgba(14,165,233,0.1)">
                            <div class="pkg-stat-icon"><i class="feather icon-gift"></i></div>
                            <div class="pkg-stat-meta">
                                <span class="pkg-stat-value">{{ $freeCount }}</span>
                                <span class="pkg-stat-label">رایگان</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="pkg-stat" style="--accent:#10b981; --accent-soft:rgba(16,185,129,0.1)">
                            <div class="pkg-stat-icon"><i class="feather icon-check-circle"></i></div>
                            <div class="pkg-stat-meta">
                                <span class="pkg-stat-value">{{ $installedCount }}</span>
                                <span class="pkg-stat-label">نصب‌شده</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="pkg-stat" style="--accent:#f59e0b; --accent-soft:rgba(245,158,11,0.1)">
                            <div class="pkg-stat-icon"><i class="feather icon-dollar-sign"></i></div>
                            <div class="pkg-stat-meta">
                                <span class="pkg-stat-value">{{ $paidCount }}</span>
                                <span class="pkg-stat-label">پولی</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search & filter --}}
                <div class="pkg-filter mb-3">
                    <form id="pkg-filter-form" method="GET" class="d-flex align-items-center gap-1 flex-wrap">
                        <div class="flex-grow-1 mr-1" style="min-width: 240px;">
                            <div class="pkg-search-box">
                                <i class="feather icon-search"></i>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="جستجو در پکیج‌ها...">
                            </div>
                        </div>
                        <select name="category" class="pkg-select mr-1">
                            <option value="">همه دسته‌ها</option>
                            <option value="shop" @if(request('category')==='shop') selected @endif>فروشگاه</option>
                            <option value="payment" @if(request('category')==='payment') selected @endif>پرداخت</option>
                            <option value="notification" @if(request('category')==='notification') selected @endif>اعلان</option>
                            <option value="seo" @if(request('category')==='seo') selected @endif>سئو</option>
                        </select>
                        <button type="submit" class="btn pkg-btn-primary mr-1">
                            <i class="feather icon-filter"></i> اعمال
                        </button>
                        <a href="{{ route('admin.packages.index') }}" class="btn pkg-btn-ghost">
                            <i class="feather icon-x"></i>
                        </a>
                    </form>
                </div>

                {{-- Packages grid --}}
                @if ($packages)
                    <div class="pkg-grid" id="pkg-grid">
                        @foreach ($packages as $pkg)

                            @php
                                $slug = $pkg['slug'] ?? '';

                                $isInstalled = array_key_exists($slug, $installedMap);

                                $installedVersion = $installedMap[$slug] ?? null;

                                $latestVersion = $pkg['latest_version'] ? $pkg['latest_version']['version'] : ($pkg['version'] ?? '');
                                $hasUpdate = $isInstalled && version_compare($latestVersion, $installedVersion, '>');
                                $thumbnail = $pkg['thumbnail_url'] ?? ($pkg['thumbnail'] ?? null);

                            @endphp
                            <div class="pkg-card" data-slug="{{ $slug }}">
                                <div class="pkg-card-media">
                                    @if ($thumbnail)
                                        <img src="{{ $thumbnail }}" alt="{{ $pkg['name'] ?? $slug }}" loading="lazy">
                                    @else
                                        <div class="pkg-card-media-placeholder">
                                            <i class="feather icon-package"></i>
                                        </div>
                                    @endif
                                    <div class="pkg-card-badges">
                                        @if ($pkg['is_free'] ?? false)
                                            <span class="pkg-tag pkg-tag-free">رایگان</span>
                                        @else
                                            <span class="pkg-tag pkg-tag-paid">پولی</span>
                                        @endif
                                    </div>
                                    @if ($isInstalled)
                                        <div class="pkg-card-installed-stamp">
                                            <i class="feather icon-check-circle"></i> نصب‌شده
                                        </div>
                                    @endif
                                    <div class="pkg-card-overlay">
                                        <button type="button" class="pkg-card-view btn-show-modal" data-slug="{{ $slug }}">
                                            <i class="feather icon-eye"></i> مشاهده جزئیات
                                        </button>
                                    </div>
                                </div>
                                <div class="pkg-card-body">
                                    @if($latestVersion)
                                        <div class="d-flex justify-content-between align-items-start mb-1 gap-1">
                                            <h5 class="pkg-card-title">{{ $pkg['name'] ?? $slug }}</h5>
                                            <span class="pkg-card-version">v{{ $latestVersion }}</span>
                                        </div>
                                        <p class="pkg-card-desc">
                                            {{ \Illuminate\Support\Str::limit($pkg['description'] ?? 'بدون توضیحات', 90) }}
                                        </p>
                                    @endif


                                    <div class="pkg-card-meta">
                                        @if (!empty($pkg['author']))
                                            <span class="pkg-chip"><i class="feather icon-user"></i> {{ $pkg['author'] }}</span>
                                        @endif
                                        @if (!empty($pkg['category']))
                                            <span class="pkg-chip"><i class="feather icon-tag"></i> {{ $pkg['category'] }}</span>
                                        @endif
                                    </div>

                                    <div class="pkg-card-footer">
                                        <div class="pkg-card-price">
                                            @php
                                                $minPrice = $pkg['min_price'] ?? ($pkg['price'] ?? 0);
                                                $isFree = $pkg['is_free'] ?? false;
                                                $hasFreePlan = $pkg['has_free_plan'] ?? false;
                                            @endphp
                                            @if ($isFree)
                                                <span class="text-success">رایگان</span>
                                            @elseif ($hasFreePlan)
                                                <span class="text-success">رایگان</span>
                                            @else
                                                <small class="text-muted d-block">از</small>
                                                <span class="pkg-price-value">{{ number_format($minPrice) }}</span>
                                                <span class="pkg-price-unit">تومان</span>
                                            @endif
                                        </div>
                                        @if (!$isInstalled)
                                            <button type="button"
                                                    class="btn pkg-btn-install btn-install"
                                                    data-slug="{{ $slug }}"
                                                    data-name="{{ $pkg['name'] ?? $slug }}"
                                                    data-free="{{ $isFree ? '1' : '0' }}"
                                                    data-price="{{ $minPrice }}"
                                                    data-plans='@json($pkg['plans'] ?? [])'>
                                                <i class="feather icon-download-cloud"></i> نصب
                                            </button>
                                        @elseif ($hasUpdate)
                                            <button type="button"
                                                    class="btn pkg-btn-update btn-show-modal"
                                                    data-slug="{{ $slug }}">
                                                <i class="feather icon-arrow-up"></i> آپدیت
                                            </button>
                                        @else
                                            <span class="pkg-up-to-date">
                                                <i class="feather icon-check"></i> به‌روز
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if (!empty($pagination['links']))
                        <div class="d-flex justify-content-center mt-3">
                            {!! $pagination['links'] !!}
                        </div>
                    @endif
                @else
                    <div class="pkg-empty-state">
                        <div class="pkg-empty-icon"><i class="feather icon-alert-octagon"></i></div>
                        <h5>ارتباط با سرور پکیج‌ها برقرار نشد!</h5>
                        <p>لطفاً چند لحظه دیگر مجدد تلاش کنید یا با پشتیبانی تماس بگیرید.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection

{{-- ====================== --}}
{{-- مدال‌ها (بیرون از content wrapper برای جلوگیری از تداخل CSS) --}}
{{-- ====================== --}}

{{-- مدال جزئیات پکیج --}}
<div class="modal fade pkg-modal" id="package-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content pkg-modal-content">
            <div class="modal-header pkg-modal-header p-1">
                <h5 class="modal-title pkg-modal-title" id="modal-pkg-title"></h5>
                <button type="button" class="close pkg-modal-close-btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pkg-modal-body" id="modal-pkg-body">
                {{-- محتوا توسط JS پر می‌شود --}}
            </div>
            <div class="modal-footer pkg-modal-footer p-1" id="modal-pkg-footer"></div>
        </div>
    </div>
</div>

{{-- Confirm install modal --}}
<div class="modal fade" id="install-confirm-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content pkg-confirm-modal">
            <div class="modal-header pkg-confirm-header">
                <h5 class="modal-title">
                    <i class="feather icon-download-cloud"></i> نصب پکیج
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pkg-confirm-body">
                <p>آیا از نصب پکیج <strong id="confirm-pkg-name"></strong> مطمئن هستید؟</p>

                {{-- بخش انتخاب پلن (با JS پر می‌شه) --}}
                <div id="confirm-plans-section" class="confirm-plans-section d-none mt-3">
                    <h6 class="mb-2"><i class="feather icon-tag"></i> انتخاب طرح قیمت‌گذاری</h6>
                    <div id="confirm-plans-list" class="pkg-plans-list row"></div>
                </div>

                {{-- اطلاعات پرداخت --}}
                <div id="confirm-payment-info" class="pkg-confirm-alert d-none mt-3">
                    <i class="feather icon-info"></i>
                    <span>مبلغ قابل پرداخت: <strong id="confirm-pkg-price"></strong></span>
                </div>

                <input type="hidden" id="confirm-selected-plan" value="">
            </div>
            <div class="modal-footer pkg-confirm-footer">
                <button type="button" class="btn pkg-btn-ghost" data-dismiss="modal">انصراف</button>
                <button type="button" id="confirm-install-btn" class="btn pkg-btn-primary">
                    <i class="feather icon-download-cloud"></i> <span id="confirm-btn-text">شروع نصب</span>
                </button>
            </div>
        </div>
    </div>
</div>

@include('back.partials.plugins', ['plugins' => ['sweetalert2']])

@push('scripts')
    <script>
        window.csrfToken = '{{ csrf_token() }}';
        window.routes = {
            show:    '{{ route("admin.packages.show", ":slug") }}',
            install: '{{ route("admin.packages.install", ":slug") }}',
            update:  '{{ route("admin.packages.update", ":slug") }}',
            uninstall: '{{ route("admin.packages.uninstall", ":slug") }}',
            toggle:  '{{ route("admin.packages.toggle", ":slug") }}',
            status:  '{{ route("admin.packages.status", ":slug") }}',
            checkUpdates: '{{ route("admin.packages.check-updates") }}',
        };
    </script>
    <script src="{{ asset('back/assets/js/pages/packages/index.js') }}?v=4"></script>
    <script src="{{ asset('back/assets/js/pages/packages/modal.js') }}?v=4"></script>
    <script src="{{ asset('back/assets/js/pages/packages/progress.js') }}?v=1"></script>
@endpush
