@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/datatable/datatable.css') }}">
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
                                    <li class="breadcrumb-item">مدیریت فروشگاها
                                    </li>
                                    <li class="breadcrumb-item active">لیست محصولات
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <!-- filter start -->
                @include('back.products.partials.index-filters', ['filter_action' => route('admin.products.index')])
                <!-- filter end -->

                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست محصولات</h4>
                        @can('products.export')
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><button type="button" data-toggle="modal" data-target="#products-export-modal" class="btn btn-outline-primary waves-effect waves-light"><i class="fa fa-file-excel-o"></i> خروجی گرفتن از لیست</button></li>
                                </ul>
                            </div>
                        @endcan
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="mb-2 collapse datatable-actions">
                                <div class="d-flex align-items-center">
                                    <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>

                                    <button class="btn personal-danger-btn mr-2 waves-effect waves-light" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                </div>
                            </div>
                            <div class="datatable datatable-bordered datatable-head-custom" id="products_datatable" data-action="{{ route('admin.sellers.products.apiIndex') }}"></div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- multiple delete modal --}}
    <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف محصولات دیگر قادر به بازیابی آنها نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.products.multipleDestroy') }}" id="product-multiple-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- delete product modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف محصول دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="product-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn btn-success waves-effect waves-light" data-dismiss="modal">
                            خیر
                        </button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- export modal -->
    <div class="modal fade text-left" id="products-export-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="products-export-form" action="{{ route('admin.products.export') }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-row" type="checkbox" class="custom-control-input" name="filters[row]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-row">ردیف</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-id" type="checkbox" class="custom-control-input" name="filters[id]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-id">آیدی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-title" type="checkbox" class="custom-control-input" name="filters[title]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-title">عنوان</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-title_en" type="checkbox" class="custom-control-input" name="filters[title_en]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-title_en">عنوان انگلیسی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-category" type="checkbox" class="custom-control-input" name="filters[category]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-category">نام دسته بندی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-type" type="checkbox" class="custom-control-input" name="filters[type]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-type">نوع محصول</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-brand" type="checkbox" class="custom-control-input" name="filters[brand]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-brand">برند</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-weight" type="checkbox" class="custom-control-input" name="filters[weight]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-weight">وزن</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-unit" type="checkbox" class="custom-control-input" name="filters[unit]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-unit">واحد</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-labels" type="checkbox" class="custom-control-input" name="filters[labels]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-labels">برچسب</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-short_description" type="checkbox" class="custom-control-input" name="filters[short_description]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-short_description">توضیحات کوتاه</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-description" type="checkbox" class="custom-control-input" name="filters[description]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-description">توضیحات</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-publish_date" type="checkbox" class="custom-control-input" name="filters[publish_date]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-publish_date">تاریخ انتشار</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-special" type="checkbox" class="custom-control-input" name="filters[special]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-special">محصول ویژه</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-published" type="checkbox" class="custom-control-input" name="filters[published]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-published">پیش نویس</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-image" type="checkbox" class="custom-control-input" name="filters[image]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-image">تصویر شاخص</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-meta_title" type="checkbox" class="custom-control-input" name="filters[meta_title]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-meta_title">عنوان سئو</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-slug" type="checkbox" class="custom-control-input" name="filters[slug]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-slug">slug</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-tags" type="checkbox" class="custom-control-input" name="filters[tags]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-tags">کلمات کلیدی</label>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-prices" type="checkbox" class="custom-control-input" name="filters[prices]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-prices">قیمت ها</label>
                                </div>
                            </div>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">نوع خروجی</label>
                                    <select name="export_type" class="form-control">
                                        <option value="excel">اکسل</option>
                                        {{-- <option value="print">چاپ</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn personal-success-btn waves-effect waves-light">خروجی گرفتن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/datatable/scripts.bundle.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/datatable/core.datatable.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/datatable/datatable.checkbox.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/sellers/products/index.js') }}?v=4"></script>
    <script src="{{ asset('back/assets/js/pages/products/filters.js') }}?v=2"></script>
@endpush
