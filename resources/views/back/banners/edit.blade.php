@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/banners.css')}}">
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
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت بنرها
                                    </li>
                                    <li class="breadcrumb-item active">ویرایش بنر
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">ویرایش بنر </h4>
                    </div>

                    <div class="container-fluid py-4">

                        <form id="banner-edit-form"
                              method="POST"
                              action="{{ route('admin.banners.update', $banner) }}"
                              enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div id="main-card">

                                {{-- دراپ‌زون --}}
                                @include('back.banners._image_uploader', [
                                    'currentImage' => old('image', $banner->image),
                                    'required'     => true,
                                ])

                                <div class="row g-4">
                                    {{-- چک‌باکس‌ها --}}
                                    <div class="col-lg-7">

                                        {{-- بخش صفحات --}}
                                        @include('back.banners._pages_section', [
                                            'selected' => old('pages', $banner->pages ?: []),
                                        ])

                                        {{-- بخش گروه‌ها --}}
                                        @include('back.banners._checkbox_grid', [
                                            'name'    => 'groups',
                                            'options' => $groups,
                                            'selected'=> old('groups', $banner->groups ?: []),
                                            'title'   => 'گروه‌های بنر',
                                            'icon'    => 'fa-layer-group',
                                            'variant' => 'groups',
                                        ])

                                        {{-- بخش موقعیت‌ها --}}
                                        @include('back.banners._checkbox_grid', [
                                            'name'    => 'places',
                                            'options' => $places,
                                            'selected'=> old('places', $banner->places ?: []),
                                            'title'   => 'موقعیت نمایش',
                                            'icon'    => 'fa-location-dot',
                                            'variant' => 'places',
                                        ])

                                    </div>

                                    {{-- اطلاعات --}}
                                    <div class="col-lg-5">
                                        <div class="sk-info-card">
                                            <div class="sk-info-card-header">
                                                <i class="fa fa-circle-info"></i>
                                                <h5>اطلاعات بنر</h5>
                                            </div>
                                            <div class="sk-info-card-body">

                                                <div class="mb-3">
                                                    <label class="form-label">عنوان <small
                                                            class="text-muted">(اختیاری)</small></label>
                                                    <input type="text" name="title"
                                                           value="{{ old('title', $banner->title) }}"
                                                           class="form-control">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">لینک <small
                                                            class="text-muted">(اختیاری)</small></label>
                                                    <input type="text" name="link"
                                                           value="{{ old('link', $banner->link) }}"
                                                           class="form-control banner-link ltr">
                                                    <small class="text-muted">با تایپ کردن، صفحات داخلی پیشنهاد داده
                                                        می‌شوند.</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">توضیحات <small class="text-muted">(اختیاری)</small></label>
                                                    <textarea name="description" rows="4"
                                                              class="form-control">{{ old('description', $banner->description) }}</textarea>
                                                </div>

                                                <div class="publish-box">
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               name="published" value="1" id="published"
                                                            {{ old('published', $banner->published) ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="publish-info">
                                                        <strong>انتشار بنر</strong>
                                                        <small>اگر فعال باشد، بنر در سایت نمایش داده می‌شود.</small>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="sk-info-card-footer">
                                                <a href="{{ route('admin.banners.index') }}" class="btn btn-light">
                                                    انصراف
                                                </a>
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="fa fa-save ms-1"></i> به‌روزرسانی
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </section>
            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => ['jquery-ui', 'jquery.validate']])

@push('scripts')
    <script>
        window.pages = @json(array_keys(\App\Models\Banner::availablePages()));
    </script>
    <script src="{{ asset('back/assets/js/pages/banners/all.js') }}?v=2"></script>
@endpush
