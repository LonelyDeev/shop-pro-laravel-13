@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/sliders.css')}}">
@endpush
@section('content')

    @php
        // آمار کلی
        $total     = $sliders->count();
        $published = $sliders->where('published', true)->count();
        $drafts    = $sliders->where('published', false)->count();
        $pagesCovered = $sliders->flatMap(fn ($s) => $s->pages ?: [])->unique()->count();

        // کاتالوگ گروه‌ها
        $groupCatalog = \App\Models\Slider::availableGroups();

        // تعریف ۳ صفحه ثابت با اطلاعات نمایشی
        $pageSections = [
            'home' => [
                'label'    => 'صفحه اصلی',
                'icon'     => 'fa-house',
                'subtitle' => 'اسلایدرهای نمایش‌داده‌شده در صفحه اصلی سایت',
                'css'      => 'home',
            ],
            'posts' => [
                'label'    => 'صفحه اصلی مقالات',
                'icon'     => 'fa-newspaper',
                'subtitle' => 'اسلایدرهای نمایش‌داده‌شده در صفحه مقالات',
                'css'      => 'posts',
            ],
            'sellers' => [
                'label'    => 'صفحه اصلی فروشندگان',
                'icon'     => 'fa-store',
                'subtitle' => 'اسلایدرهای نمایش‌داده‌شده در صفحه فروشندگان',
                'css'      => 'sellers',
            ],
        ];
    @endphp
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-body" id="main-card">
                <div class="container-fluid py-4">

                    {{-- هدر --}}
                    <div class="sk-page-header d-flex align-items-center justify-content-between">
                        <div>
                            <h3>
                                <span class="icon-wrap"><i class="fa fa-images"></i></span>
                                مدیریت اسلایدرها
                            </h3>
                            <p>اسلایدرها بر اساس صفحه نمایش، به‌صورت کارت گروه‌بندی شده‌اند.</p>
                        </div>
                        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus ms-1"></i> اسلایدر جدید
                        </a>
                    </div>

                    {{-- آمار --}}
                    <div class="sk-stats">
                        <div class="sk-stat sk-stat--blue">
                            <div class="sk-stat-icon"><i class="fa fa-images"></i></div>
                            <div class="sk-stat-info">
                                <div class="sk-stat-value">{{ $total }}</div>
                                <div class="sk-stat-label">کل اسلایدرها</div>
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
                                $pageSliders = $sliders->filter(fn ($s) => in_array($pageKey, $s->pages ?: []));
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
                                    <span class="sk-page-section-count">{{ $pageSliders->count() }} اسلایدر</span>
                                </div>
                                <div class="sk-page-section-body">
                                    @if ($pageSliders->count() > 0)
                                        <div class="sk-slider-cards">
                                            @foreach ($pageSliders as $slider)
                                                @include('back.sliders._slider_card', [
                                                    'slider'       => $slider,
                                                    'groupCatalog' => $groupCatalog,
                                                ])
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="sk-page-empty">
                                            <div class="sk-page-empty-icon">
                                                <i class="fa {{ $pageInfo['icon'] }}"></i>
                                            </div>
                                            <p>هیچ اسلایدری برای این صفحه ثبت نشده است.</p>
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
                                    <h4>هنوز اسلایدری ثبت نشده است</h4>
                                    <p>اولین اسلایدر خود را ایجاد کنید تا در صفحات سایت نمایش داده شود.</p>
                                    <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary px-4">
                                        <i class="fa fa-plus ms-1"></i> ایجاد اسلایدر
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        @endsection
        @push('scripts')
            <script>
              window.pages = @json(array_keys(\App\Models\Slider::availablePages()));
            </script>
            <script src="{{ asset('back/assets/js/pages/sliders/all.js') }}?v=2"></script>
    @endpush
