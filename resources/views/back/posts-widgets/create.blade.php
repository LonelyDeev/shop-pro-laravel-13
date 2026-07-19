@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/widgets/all.css')}}">
@endpush
@section('content')


    <div class="app-content content widgets-page">
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
                                    <li class="breadcrumb-item">قالب ها</li>
                                    <li class="breadcrumb-item active">ایجاد ابزارک</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="page-heading">
                    <h2>ایجاد ابزارک جدید</h2>
                    <p>یک بخش از گالری زیر انتخاب کنید، عنوان و وضعیت را مشخص کنید و فیلدهای مربوطه را تکمیل نمایید.</p>
                </div>

                <section class="widgets-card">
                    <div class="widgets-card-head">
                        <h4>مشخصات ابزارک</h4>
                    </div>

                    <div id="main-card" class="widgets-card-body">
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <form class="form" id="widget-create-form" action="{{ route('admin.posts-widgets.store') }}" data-redirect="{{ route('admin.posts-widgets.index') }}" method="post" enctype="multipart/form-data">
                                    @csrf

                                    <div class="section-block">
                                        <label class="field-label">نوع ابزارک <small>با کلیک روی دکمه زیر، از بین الگوها انتخاب کنید</small></label>

                                        {{-- kept intact (id/name/data-*) so the existing create.js keeps working --}}
                                        @include('back.posts-widgets.partials.widget-select', ['page' => 'posts'])

                                        <button type="button" class="type-picker-trigger" id="type-picker-trigger" data-toggle="modal" data-target="#widget-type-modal">
                                            <span class="thumb" id="type-picker-thumb"><i class="feather icon-layout"></i></span>
                                            <span class="info">
                                                <span class="title" id="type-picker-title">انتخاب نوع ابزارک</span>
                                                <span class="subtitle" id="type-picker-subtitle">هنوز چیزی انتخاب نشده</span>
                                            </span>
                                            <span class="chevron"><i class="feather icon-chevron-left"></i></span>
                                        </button>
                                    </div>

                                    <div class="section-block row">
                                        <div class="col-md-6">
                                            <label class="field-label">عنوان ابزارک</label>
                                            <input type="text" class="styled-input" name="title" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="field-label">وضعیت</label>
                                            <select name="is_active" class="styled-input">
                                                <option value="1">فعال</option>
                                                <option value="0">غیر فعال</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div id="template" class="section-block">
                                        <div class="row"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn-submit">
                                                <i class="feather icon-check"></i> ایجاد ابزارک
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-4">
                                <div class="preview-panel box-fullScreen">
                                    <div class="preview-title">پیش‌نمایش</div>
                                    <a class="btn btn-sm btn-default btn-round btn-fullscreen" rel="tooltip" title="تمام صفحه" href="#">
                                        <i class=" fas fa-maximize"></i>
                                    </a>
                                    <div class="preview-frame">
                                        <img id="widget-image" src="" alt="widget" style="display:none;">
                                        <div class="preview-placeholder" id="preview-placeholder">
                                            <i class="feather icon-image"></i>
                                            پس از انتخاب نوع ابزارک، پیش‌نمایش آن اینجا نمایش داده می‌شود
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- widget type picker modal --}}
                @include('back.posts-widgets.partials.widget-select-modal', ['page' => 'posts'])
            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/widgets/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/posts-widgets/create.js') }}"></script>

@endpush
