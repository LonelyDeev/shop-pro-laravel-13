@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.css') }}">
    <style>
        .file-uploader.dropzone .dz-message{
            top: 14%;
        }
        .file-uploader.dropzone .dz-message:before{
            top: 100px;
        }
    </style>
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
                                <li class="breadcrumb-item">مدیریت اسلایدرها
                                </li>
                                <li class="breadcrumb-item active">ایجاد اسلایدر
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
                    <h4 class="card-title">ایجاد اسلایدر جدید</h4>
                </div>

                <div id="main-card" class="card-content">
                    <div class="card-body">
                        <div class="col-12 col-md-10 offset-md-1">
                            <form class="form" id="slider-create-form" action="{{ route('admin.sliders.store') }}" method="slider">
                                @csrf
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12">
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
                                        <div class="story-hide col-md-6 col-12">
                                            <div class="form-group">
                                                <label>عنوان <small>(اختیاری)</small></label>
                                                <input type="text" class="form-control" name="title">
                                            </div>
                                        </div>

                                        <div class="story-hide col-md-6 col-12">
                                            <div class="form-group">
                                                <label>لینک <small>(اختیاری)</small></label>
                                                <input type="text" class="form-control slider-link ltr" name="link">
                                            </div>
                                        </div>
                                        <div id='story-only' class="col-md-12 col-12 d-none">
                                            <div class="form-group">
                                                <label>کاور استوری </label>
                                                <input type="text" class="form-control"  name="title" placeholder='آدرس عکس کاور استوری را وارد کنید'>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>نمایش در صفحه</label>
                                                <select class="form-control" name="page" id="page_select">
                                                    @if(config('front.slider_sections'))
                                                        @foreach(config('front.slider_sections') as $sliderSections)
                                                            <option value="{{ $sliderSections['key'] }}" {{ old('page', $slider->page ?? 'home') == $sliderSections['key'] ? 'selected' : '' }}>
                                                                {{ $sliderSections['name'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label>گروه اسلایدر</label>
                                                <select class="form-control" name="group" id="group_select">
                                                    @if(config('front.sliderGroups') && isset(config('front.sliderGroups')['home']))
                                                        @foreach(config('front.sliderGroups')['home'] as $sliderGroup)
                                                            <option value="{{ $sliderGroup['group'] }}" data-width="{{ $sliderGroup['width'] }}" data-height="{{ $sliderGroup['height'] }}">
                                                                {{ $sliderGroup['name'] }} ({{ $sliderGroup['size'] }})
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-12">
                                            <div class="form-group">
                                                <label for="first-name-vertical">توضیحات <small>(اختیاری)</small></label>
                                                <textarea id="description" class="form-control" rows="4" name="description"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" name="published" checked>
                                                    <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    <span>انتشار اسلایدر؟</span>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد اسلایدر</button>
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
        var pages =  [
            @foreach($pages as $page)
                "/pages/{{ $page }}",
            @endforeach
        ];

        var sliderGroups = @json(config('front.sliderGroups'));
        var currentGroup = "{{ old('group', $slider->group ?? '') }}";
    </script>
    <script src="{{ asset('back/assets/js/pages/sliders/create.js') }}"></script>


@endpush
