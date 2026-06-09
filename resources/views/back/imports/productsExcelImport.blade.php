@extends('back.layouts.master')

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
                                <li class="breadcrumb-item">مدیریت محصولات
                                </li>
                                <li class="breadcrumb-item active">ایجاد محصولات از فایل Excel
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
                    <h4 class="card-title">افزودن محصولات از فایل Excel</h4>
                </div>

                <div id="main-card" class="card-content">
                    <div class="card-body">
                        <form class="form" id="excel-create-form" action="{{ route('admin.import.products.store') }}" method="post">
                            @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h5>

                            </div>
                            <div class="modal-body">
                                    <ul class="row" style='list-style: persian;'>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-title" type="checkbox" class="custom-control-input" name="filters[title]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-title">عنوان</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-title_en" type="checkbox" class="custom-control-input" name="filters[title_en]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-title_en">عنوان انگلیسی</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-slug" type="checkbox" class="custom-control-input" name="filters[slug]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-slug">slug</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-brand" type="checkbox" class="custom-control-input" name="filters[brand]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-brand">برند</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-weight" type="checkbox" class="custom-control-input" name="filters[weight]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-weight">وزن</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-unit" type="checkbox" class="custom-control-input" name="filters[unit]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-unit">واحد</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[price]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-price">قیمت </label>
                                            </div>
                                        </li>
                                       {{-- <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[discount]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-price">تخفیف (درصد) </label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[cart_max]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-price">بیشترین تعداد مجاز در هر سفارش</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[cart_min]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-price">کمترین تعداد مجاز در هر سفارش</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[color]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-price">رنگ</label>
                                            </div>
                                        </li>--}}
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-stock" type="checkbox" class="custom-control-input" name="filters[stock]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-stock">موجودی انبار</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-short_description" type="checkbox" class="custom-control-input" name="filters[short_description]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-short_description">توضیحات کوتاه</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-description" type="checkbox" class="custom-control-input" name="filters[description]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-description">توضیحات</label>
                                            </div>
                                        </li>

                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-special" type="checkbox" class="custom-control-input" name="filters[special]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-special">محصول ویژه</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-published" type="checkbox" class="custom-control-input" name="filters[published]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-published">پیش نویس</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-image" type="checkbox" class="custom-control-input" name="filters[image]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-image">تصویر شاخص</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-meta_title" type="checkbox" class="custom-control-input" name="filters[meta_title]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-meta_title">عنوان سئو</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-meta_description" type="checkbox" class="custom-control-input" name="filters[meta_description]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-meta_description">توضیحات سئو</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-tags" type="checkbox" class="custom-control-input" name="filters[tags]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-tags">کلمات کلیدی (موبایل,...)</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-publish_date" type="checkbox" class="custom-control-input" name="filters[publish_date]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-publish_date">تاریخ انتشار</label>
                                            </div>
                                        </li>
                                    </ul>
                                {{--    <div class="row">

                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-specifications" type="checkbox" class="custom-control-input" name="filters[specifications]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-specifications">لیست مشخصات</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-images" type="checkbox" class="custom-control-input" name="filters[images]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-images">لیست تصاویر</label>
                                            </div>
                                        </div>
                                    </div>--}}

                                </div>

                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <span class='d-block'>
                                      <i class="feather icon-info ml-1 align-middle"></i>
                                   بسیار مهم می باشد که جایگاه فیلد ها به همین ترتیب باشد.
                                    اگر فایل شما به عنوان مثال تصویر شاخص ویا برند و... را دارا نمی باشد، این فیلد هارا خالی بگذارید و فیلدها را با همین عناوین ایجاد کنید.
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <i class="feather icon-info ml-1 align-middle"></i>
                                <span>
                                    در فایل اکسل فیلد های
                                    <span class="badge badge-danger">عنوان</span>
                                    <span class="badge badge-danger">قیمت</span>
                                    <span class="badge badge-danger">موجودی انبار</span>
                                    الزامی است.
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <i class="feather icon-info ml-1 align-middle"></i>
                                <span>
                                    بعد از آپلود فایل به منظور آپلود، اگر پیغام
                                    <span class="badge badge-success">موفقیت</span>
                                    را دریافت نکردید، به منظور خطا و ایجاد نشدن محصولات می باشد.

                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 offset-md-1">

                                <div class="form-body">
                                    <div class="row">
                                        <fieldset class="form-group">
                                            <label>انتخاب فایل Excel</label>
                                            <div class="custom-file">
                                                <input id="file" type="file"  accept=".xlsx" name="file" class="custom-file-input" required>
                                                <label class="custom-file-label" for="file"></label>
                                            </div>
                                        </fieldset>
                                    </div>



                                    <div class="row">
                                        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">افزودن فایل</button>
                                    </div>
                                </div>



                        </div>

                        </form>
                    </div>
                </div>
            </section>
            <div id="form-progress" class="progress progress-bar-success progress-xl" style="display: none;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
            </div>
            <!--/ Description -->

        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/products/import.js') }}"></script>
@endpush

@php
session()->forget('ImportError');
session()->forget('ImportSuccess');
 @endphp
