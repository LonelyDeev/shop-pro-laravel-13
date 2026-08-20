@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/banners.css')}}">
@endpush
@section('content')


    @php
        // آمار کلی
        $total     = $banners->count();
        $published = $banners->where('published', true)->count();
        $drafts    = $banners->where('published', false)->count();
        $pagesCovered = $banners->flatMap(fn ($b) => $b->pages ?: [])->unique()->count();

        // کاتالوگ‌ها
        $groupCatalog = \App\Models\Banner::availableGroups();
        $placeCatalog = \App\Models\Banner::availablePlaces();

        // تعریف ۲ صفحه ثابت با اطلاعات نمایشی
        $pageSections = [
            'home' => [
                'label'    => 'صفحه اصلی',
                'icon'     => 'fa-house',
                'subtitle' => 'بنرهای نمایش‌داده‌شده در صفحه اصلی سایت',
                'css'      => 'home',
            ],
            'posts' => [
                'label'    => 'صفحه اصلی مقالات',
                'icon'     => 'fa-newspaper',
                'subtitle' => 'بنرهای نمایش‌داده‌شده در صفحه مقالات',
                'css'      => 'posts',
            ],
        ];
    @endphp
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت بنرها
                                    </li>
                                    <li class="breadcrumb-item active">لیست بنرها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body" id="main-card">
    <div class="container-fluid py-4">

        {{-- آمار --}}
        <div class="sk-stats">
            <div class="sk-stat sk-stat--blue">
                <div class="sk-stat-icon"><i class="fa fa-images"></i></div>
                <div class="sk-stat-info">
                    <div class="sk-stat-value">{{ $total }}</div>
                    <div class="sk-stat-label">کل بنرها</div>
                </div>
            </div>
            <div class="sk-stat sk-stat--green">
                <div class="sk-stat-icon"><i class="fa fa-circle-check"></i></div>
                <div class="sk-stat-info">
                    <div class="sk-stat-value">{{ $published }}</div>
                    <div class="sk-stat-label">منتشر شده</div>
                </div>
            </div>
            <div class="sk-stat sk-stat--amber">
                <div class="sk-stat-icon"><i class="fa fa-pen-ruler"></i></div>
                <div class="sk-stat-info">
                    <div class="sk-stat-value">{{ $drafts }}</div>
                    <div class="sk-stat-label">پیش‌نویس</div>
                </div>
            </div>
            <div class="sk-stat sk-stat--purple">
                <div class="sk-stat-icon"><i class="fa fa-file-lines"></i></div>
                <div class="sk-stat-info">
                    <div class="sk-stat-value">{{ $pagesCovered }}</div>
                    <div class="sk-stat-label">صفحات پوشش‌داده‌شده</div>
                </div>
            </div>
        </div>

        @if ($total > 0)
            {{-- بخش‌های گروه‌بندی‌شده بر اساس صفحه --}}
            @foreach ($pageSections as $pageKey => $pageInfo)
                @php
                    $pageBanners = $banners->filter(fn ($b) => in_array($pageKey, $b->pages ?: []));
                @endphp
                <div class="sk-page-section sk-page-section--{{ $pageInfo['css'] }}">
                    <div class="sk-page-section-header">
                        <span class="sk-page-section-icon">
                            <i class="fa {{ $pageInfo['icon'] }}"></i>
                        </span>
                        <div class="sk-page-section-info">
                            <h4 class="sk-page-section-title">{{ $pageInfo['label'] }}</h4>
                            <small class="sk-page-section-subtitle">{{ $pageInfo['subtitle'] }}</small>
                        </div>
                        <span class="sk-page-section-count">{{ $pageBanners->count() }} بنر</span>
                    </div>

                    <div class="sk-page-section-body">
                        @if ($pageBanners->count() > 0)
                            <div class="sk-slider-cards">
                                @foreach ($pageBanners as $banner)
                                    @include('back.banners._banner_card', [
                                        'banner'       => $banner,
                                        'groupCatalog' => $groupCatalog,
                                        'placeCatalog' => $placeCatalog,
                                    ])
                                @endforeach
                            </div>
                        @else
                            <div class="sk-page-empty">
                                <div class="sk-page-empty-icon">
                                    <i class="fa {{ $pageInfo['icon'] }}"></i>
                                </div>
                                <p>هیچ بنری برای این صفحه ثبت نشده است.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            {{-- حالت خالی کلی --}}
            <div class="sk-page-section sk-page-section--home">
                <div class="sk-page-section-body">
                    <div class="sk-empty">
                        <div class="sk-empty-icon">
                            <i class="fa fa-image"></i>
                        </div>
                        <h4>هنوز بنری ثبت نشده است</h4>
                        <p>اولین بنر خود را ایجاد کنید تا در صفحات سایت نمایش داده شود.</p>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary px-4">
                            <i class="fa fa-plus ms-1"></i> ایجاد بنر
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => ['jquery-ui', 'jquery.validate']])

@push('scripts')
    <script>
        window.BASE_URL = "{{ url('/') }}";
        window.pages    = @json(array_keys(\App\Models\Banner::availablePages()));
    </script>
    <script src="{{ asset('back/assets/js/pages/banners/all.js') }}?v=2"></script>
@endpush
