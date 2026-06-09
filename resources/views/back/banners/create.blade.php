@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.css') }}">
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
                                    <li class="breadcrumb-item active">ایجاد بنر
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
                        <h4 class="card-title">ایجاد بنر جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form class="form" id="banner-create-form" action="{{ route('admin.banners.store') }}"
                                      method="banner">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-12 col-12">
                                                <fieldset class="form-group">
                                                    <label>تصویر شاخص</label>

                                                    <input type="text" id="image_label"
                                                           class="form-control display-hidden" name="image"
                                                           aria-label="Image" aria-describedby="button-image">

                                                    <span class="remove-img-uploader display-hidden">
                                                            <i class="fa fa-trash text-danger px-1"></i>
                                                    </span>

                                                    <div class="file-uploader dropzone dropzone-area mb-2 ui-sortable dz-clickable"
                                                         id="button-image">
                                                        <div class="img-uploader display-hidden">
                                                            <img src="">
                                                        </div>
                                                        <div class="dz-message">برای آپلود کلیک کنید</div>
                                                    </div>
                                                    {{--<p><small>بهترین اندازه <span class="text-danger">{{ config('front.imageSizes.postImage') }}</span> پیکسل میباشد.</small></p>--}}

                                                </fieldset>
                                            </div>

                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>لینک <small>(اختیاری)</small></label>
                                                    <input type="text" class="form-control banner-link ltr" name="link">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>عنوان <small>(اختیاری)</small></label>
                                                    <input type="text" class="form-control "
                                                           name="title">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12">
                                                <div class="form-group">
                                                    <label>نمایش در صفحه</label>
                                                    <select class="form-control" name="page" id="page_select">
                                                        @if(config('front.banner_sections'))
                                                            @foreach(config('front.banner_sections') as $bannerSections)
                                                                <option value="{{ $bannerSections['key'] }}" {{ old('page', 'home') == $bannerSections['key'] ? 'selected' : '' }}>
                                                                    {{ $bannerSections['name'] }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>گروه بنر</label>
                                                    <select class="form-control" name="group" id="group_select">
                                                        @if(config('front.bannerGroups') && isset(config('front.bannerGroups')['home']))
                                                            @foreach(config('front.bannerGroups')['home'] as $bannerGroup)
                                                                <option value="{{ $bannerGroup['group'] }}"
                                                                        data-width="{{ $bannerGroup['width'] }}"
                                                                        data-height="{{ $bannerGroup['height'] }}">
                                                                    {{ $bannerGroup['name'] }} ({{ $bannerGroup['size'] }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>موقعیت</label>
                                                    <select class="form-control" name="place">
                                                        @if (config('front.bannerGroupsPlace'))

                                                            @foreach (config('front.bannerGroupsPlace') as $bannerGroup)
                                                                <option
                                                                    value="{{ $bannerGroup['group'] }}">{{ $bannerGroup['name'] }}</option>
                                                            @endforeach

                                                        @endif
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="col-md-12 col-12">
                                                <div class="form-group">
                                                    <label>توضیحات <small>(اختیاری)</small></label>
                                                    <textarea type="text" class="form-control "
                                                              name="description"></textarea>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="row">

                                            <div class="col-12 col-md-6">

                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="published" checked>
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        <span>انتشار بنر؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button type="submit"
                                                        class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد
                                                    بنر
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.js') }}"></script>

    <script>
        var pages = [
            @foreach($pages as $page)
                "/pages/{{ $page }}",
            @endforeach
        ];
        var bannerGroups = @json(config('front.bannerGroups'));
    </script>
    <script src="{{ asset('back/assets/js/pages/banners/create.js') }}"></script>
    <script>

    </script>
@endpush
