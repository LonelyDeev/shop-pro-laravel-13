@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/pages.css')}}">
@endpush
@section('content')

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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">مدیریت صفحات</li>
                                    <li class="breadcrumb-item active">ایجاد صفحه</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد صفحه جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form class="form" id="page-create-form" action="{{ route('admin.pages.store') }}" method="post">
                                @csrf
                                <div class="nav-vertical">
                                    <div class=" nav nav-tabs flex-column nav-left ">
                                        <ul class="nav nav-tabs flex-column nav-vertical-right" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="baseVerticalLeft-tab1" data-toggle="tab" aria-controls="tabVerticalLeft1" href="#tabVerticalLeft1" role="tab" aria-selected="false"><i class=" fas fa-clipboard-list"></i> اطلاعات کلی</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="productMetaTab" data-toggle="tab" aria-controls="tabProductMeta" href="#tabProductMeta" role="tab" aria-selected="true"><i class=" fab fa-squarespace"></i> تنظیمات سئو</a>
                                            </li>
                                        </ul>

                                        <div class="nav-vertical-right mt-2">
                                            <div class="col-12 ">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary ">
                                                        <input type="checkbox" name="published" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>انتشار صفحه؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد صفحه</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tabVerticalLeft1" role="tabpanel" aria-labelledby="baseVerticalLeft-tab1">
                                            <div class="col-12">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="col-md-6 col-12">
                                                            <div class="form-group">
                                                                <label>عنوان</label>
                                                                <input type="text" class="form-control" name="title">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <div class="form-group">
                                                                <label>آدرس</label>
                                                                <input type="text" class="form-control" name="slug">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label for="first-name-vertical">محتوا</label>
                                                                <textarea id="content" class="form-control" rows="3" name="content"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- بخش جدید راهنمای شورتکدها -->
                                                    <div class="alert alert-primary d-flex justify-content-between align-items-center mt-2 p-1" style="border-radius: 12px; background: linear-gradient(45deg, #4f46e5, #7c3aed); color: #fff; border: none;">
                                                        <div class="d-flex align-items-center p-1">
                                                            <i class="fas fa-puzzle-piece fa-lg ml-1" style="color: rgba(255,255,255,0.8);"></i>
                                                            <div>
                                                                <strong style="font-size: 15px;">افزودن ویجت‌ها و فرم‌ها</strong>
                                                                <p class="mb-0 font-small-2" style="opacity: 0.9;">برای درج عناصر پویا در صفحه، روی دکمه روبرو کلیک کنید.</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-light btn-sm waves-effect waves-light" data-toggle="modal" data-target="#widgetsGuideModal" style="border-radius: 8px; color: #4f46e5; font-weight: bold;">
                                                            <i class="fas fa-th-large"></i> گالری ویجت‌ها
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane " id="tabProductMeta" role="tabpanel" aria-labelledby="productMetaTab">
                                            <div class="col-12">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>عنوان سئو</label>
                                                                <input type="text" class="form-control" name="meta_title" value="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>توضیحات سئو</label>
                                                                <textarea class="form-control" name="meta_description" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <fieldset class="form-group">
                                                                <label>کلمات کلیدی</label>
                                                                <input id="tags" type="text" name="tags" class="form-control">
                                                            </fieldset>
                                                        </div>

                                                        <div class="row seo-help-info">
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i class=" fas fa-check"></i><span class="title">ایجاد Google Snippet برای موتور جستجو</span><span class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i class=" fas fa-check"></i><span class="title">ایجاد پیشنمایش برای شبکه های اجتماعی</span><span class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i class=" fas fa-check"></i><span class="title">افزودن به sitemap.xml سایت</span><span class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i class=" fas fa-check"></i><span class="title">ایجاد تمامی Head TAG های ضروری سئو </span><span class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @php
        // دریافت خودکار لیست ویجت‌ها از کانفیگ‌ها
        $homeWidgets = config('front.home-widgets', []);
        $postsWidgets = config('front.posts-widgets', []);
        $allWidgets = array_merge($homeWidgets, $postsWidgets);
    @endphp

        <!-- مودال زیبای راهنمای شورتکدها -->
    <div class="modal fade text-left wg-modal" id="widgetsGuideModal" tabindex="-1" role="dialog" aria-labelledby="widgetsGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header wg-header">
                    <div class="d-flex align-items-center" style="gap: 10px; flex-wrap: wrap;">
                        <span class="wg-header-icon"><i class="fas fa-cubes"></i></span>
                        <h5 class="modal-title" id="widgetsGuideModalLabel">گالیــری ویجت‌ها و فرم‌ها</h5>
                        <span class="wg-count-pill">
                        <i class="fas fa-layer-group"></i>
                        <span>{{ count($allWidgets) + 1 }}</span> مورد
                    </span>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span>
                    </button>
                </div>

                {{-- Body (scrolls internally) --}}
                <div class="modal-body wg-body">

                    <p class="wg-help">
                        <i class="fas fa-info-circle"></i>
                        برای نمایش ویجت در محتوای صفحه، روی دکمه <strong>«کپی شورتکد»</strong> کلیک کنید و سپس در ویرایشگر متن، آن را <strong>Paste</strong> کنید.
                    </p>

                    {{-- Toolbar: search + filter chips --}}
                    <div class="wg-toolbar">
                        <div class="wg-search-wrap">
                            <i class="fas fa-search wg-search-icon"></i>
                            <input type="text" class="form-control wg-search-input" placeholder="جستجو در ویجت‌ها و فرم‌ها…" aria-label="جستجو">
                        </div>
                        <div class="wg-chips" role="tablist">
                            <button type="button" class="wg-chip active" data-filter="all">
                                <i class="fas fa-border-all"></i> همه
                            </button>
                            <button type="button" class="wg-chip" data-filter="widget">
                                <i class="fas fa-puzzle-piece"></i> ویجت‌ها
                            </button>
                            <button type="button" class="wg-chip" data-filter="form">
                                <i class="fas fa-file-alt"></i> فرم‌ها
                            </button>
                        </div>
                    </div>

                    {{-- Grid --}}
                    <div class="row wg-grid">
                        @php
                            $wgGradients = [
                                'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                                'linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%)',
                                'linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)',
                                'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)',
                                'linear-gradient(135deg, #10b981 0%, #14b8a6 100%)',
                                'linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%)',
                            ];
                            $wgIcons = ['fa-puzzle-piece', 'fa-cube', 'fa-layer-group', 'fa-shapes', 'fa-th-large', 'fa-cubes'];
                        @endphp

                        @foreach($allWidgets as $key => $widget)
                            @php
                                $gi     = $loop->index % 6;
                                $title  = $widget['title'] ?? $key;
                                $sc     = '[widget-' . $key . ']';
                                $search = mb_strtolower($title . ' ' . $key . ' ' . $sc . ' widget ویجت', 'UTF-8');
                            @endphp
                            <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3 wg-card-col"
                                 data-category="widget"
                                 data-search="{{ $search }}">
                                <div class="wg-card">
                                    <div class="wg-card-head"
                                         @if(isset($widget['image']))
                                             style="background-image: linear-gradient(135deg, rgba(99,102,241,.55), rgba(139,92,246,.45)), url('{{ theme_asset($widget['image']) }}'); background-size: cover; background-position: center;"
                                         @else
                                             style="background: {{ $wgGradients[$gi] }};"
                                        @endif>
                                        @if(!isset($widget['image']))
                                            <i class="fas {{ $wgIcons[$gi] }} wg-card-icon"></i>
                                        @endif
                                        <span class="wg-badge">ویجت</span>
                                    </div>
                                    <div class="wg-card-body">
                                        <h6 class="wg-card-title">{{ $title }}</h6>
                                        <code class="wg-shortcode"><i class="fas fa-terminal"></i> {{ $sc }}</code>
                                        <button type="button" class="btn wg-copy-btn" data-shortcode="{{ $sc }}">
                                            <i class="fas fa-copy"></i> <span>کپی شورتکد</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Distinct "forms" card --}}
                        <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3 wg-card-col"
                             data-category="form"
                             data-search="فرم ورودی اطلاعاتی form [form-1] فرم‌ها">
                            <div class="wg-card wg-card-form">
                                <div class="wg-card-head wg-form-head">
                                    <i class="fas fa-file-alt wg-card-icon"></i>
                                    <span class="wg-badge wg-badge-form">فرم</span>
                                </div>
                                <div class="wg-card-body">
                                    <h6 class="wg-card-title">فرم‌های ورودی اطلاعاتی</h6>
                                    <small class="wg-hint"><i class="fas fa-lightbulb"></i> برای فرم‌ها عدد را تغییر دهید</small>
                                    <code class="wg-shortcode wg-shortcode-form"><i class="fas fa-terminal"></i> [form-1]</code>
                                    <button type="button" class="btn wg-copy-btn wg-copy-btn-form" data-shortcode="[form-1]">
                                        <i class="fas fa-copy"></i> <span>کپی شورتکد</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Empty state --}}
                    <div class="wg-empty-state d-none">
                        <i class="fas fa-search-minus"></i>
                        <p>موردی مطابق با جستجوی شما یافت نشد.</p>
                        <span>لطفاً عبارت دیگری را امتحان کنید یا فیلتر را تغییر دهید.</span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer wg-footer">
                <span class="wg-footer-count">
                    <i class="fas fa-check-circle"></i>
                    <span class="wg-footer-count-num">{{ count($allWidgets) + 1 }}</span> مورد یافت شد
                </span>
                    <button type="button" class="btn wg-close-btn" data-dismiss="modal">
                        <i class="fas fa-times"></i> بستن پنجره
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/pages/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/pages/create.js') }}"></script>

    <script>

    </script>
@endpush
