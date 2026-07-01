@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/pages/imports/productsExcelImport.css') }}">
@endpush
@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">مدیریت</a></li>
                                    <li class="breadcrumb-item"><a href="#">مدیریت محصولات</a></li>
                                    <li class="breadcrumb-item active">ایجاد محصولات از فایل Excel</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section class="card import-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="feather icon-file-text mr-1"></i>
                            افزودن محصولات از فایل Excel
                        </h4>
                    </div>
                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form class="form" id="excel-create-form"
                                  action="{{ route('admin.import.products.store') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row">

                                    {{-- ============ LEFT: FIELD SELECTOR ============ --}}
                                    <div class="col-12 col-lg-5">
                                        <section class="import-panel">
                                            <div class="card-header border-bottom p-0 pb-1">
                                                <h6 class="card-title">
                                                    <i class="feather icon-check-square "></i>
                                                    فیلدهای مورد نظر را انتخاب کنید
                                                </h6>
                                                <button type="button" id="toggle-all-fields"
                                                        class="btn btn-sm btn-outline-primary p-0">
                                                    <i class="feather icon-repeat"></i>
                                                    انتخاب/لغو همه
                                                </button>
                                            </div>

                                            <div class="card-body p-0">
                                                <p class="text-muted mb-1">
                                                    ستون‌هایی که می‌خواهید در فایل اکسل وارد کنید را تیک بزنید. ترتیب
                                                    ستون‌ها در پیش‌نمایش روبه‌رو نمایش داده می‌شود.
                                                </p>

                                                <ul class="row fields-list pl-0" style="list-style: none;">
                                                    @php
                                                        $fields = [
                                                            'title'             => ['label' => 'عنوان',                      'required' => true],
                                                            'title_en'          => ['label' => 'عنوان انگلیسی',             'required' => false],
                                                            'slug'              => ['label' => 'Slug',                       'required' => false],
                                                            'brand'             => ['label' => 'برند',                       'required' => false],
                                                            'weight'            => ['label' => 'وزن',                        'required' => false],
                                                            'unit'              => ['label' => 'واحد',                       'required' => false],
                                                            'price'             => ['label' => 'قیمت',                       'required' => true],
                                                            'stock'             => ['label' => 'موجودی انبار',             'required' => true],
                                                            'short_description' => ['label' => 'توضیحات کوتاه',            'required' => false],
                                                            'description'       => ['label' => 'توضیحات',                   'required' => false],
                                                            'special'           => ['label' => 'محصول ویژه',               'required' => false],
                                                            'published'         => ['label' => 'پیش‌نویس',                  'required' => false],
                                                            'image'             => ['label' => 'تصویر شاخص',               'required' => false],
                                                            'meta_title'        => ['label' => 'عنوان سئو',                 'required' => false],
                                                            'meta_description'  => ['label' => 'توضیحات سئو',              'required' => false],
                                                            'tags'              => ['label' => 'کلمات کلیدی',              'required' => false],
                                                            'publish_date'      => ['label' => 'تاریخ انتشار',             'required' => false],
                                                            'category'      => ['label' => 'دسته بندی',             'required' => false],
                                                            'type'      => ['label' => 'نوع محصول (فیزیکی،دانلودی)',             'required' => false],
                                                        ];
                                                    @endphp

                                                    @foreach($fields as $key => $f)
                                                        <li class="col-md-6 mb-1">
                                                            <div
                                                                class="custom-control custom-checkbox custom-checkbox-success">
                                                                <fieldset class="checkbox">
                                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                                        <input    id="export-checkbox-{{ $key }}"
                                                                                  type="checkbox"
                                                                                  class="custom-control-input field-checkbox"
                                                                                  name="filters[{{ $key }}]"
                                                                                  value="1"
                                                                                  data-label="{{ $f['label'] }}"
                                                                                  data-key="{{ $key }}"
                                                                                  checked
                                                                                  @if($f['required']) data-required="1" @endif>
                                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                                        <span>   {{ $f['label'] }}
                                                                            @if($f['required'])
                                                                                <span
                                                                                    class="badge badge-danger badge-pill ml-50">الزامی</span>
                                                                            @endif</span>
                                                                    </div>
                                                                </fieldset>

                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </section>
                                    </div>

                                    {{-- ============ RIGHT: LIVE PREVIEW + UPLOAD ============ --}}
                                    <div class="col-12 col-lg-7">
                                        <section class="import-panel">

                                            <div
                                                class="import-panel-header d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="feather icon-grid mr-1"></i>
                                                    پیش‌نمایش زنده فایل اکسل
                                                </h5>
                                                <span class="badge badge-light-primary" id="column-count">0 ستون</span>
                                            </div>

                                            <div class="">
                                                <p class="text-muted mb-1">
                                                    ترتیب ستون‌های زیر را دقیقاً در فایل اکسل خود رعایت کنید:
                                                </p>

                                                <div class="table-responsive excel-preview-wrapper">
                                                    <table
                                                        class="table table-bordered table-striped mb-0 excel-preview-table">
                                                        <thead class="thead-dark">
                                                        <tr id="preview-letters"></tr>
                                                        <tr id="preview-headers"></tr>
                                                        </thead>
                                                        <tbody id="preview-body">
                                                        {{-- filled by JS --}}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="warehouse_id" class="font-weight-bold">
                                                            <i class="feather icon-home mr-50"></i>
                                                            انتخاب انبار مقصد
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="warehouse_id" id="warehouse_id"
                                                                class="form-control select2" required>
                                                            <option value="">انتخاب انبار...</option>
                                                            @foreach($warehouses ?? [] as $warehouse)
                                                                <option value="{{ $warehouse->id }}"
                                                                    {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                                    {{ $warehouse->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('warehouse_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>


                                            <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" class="custom-control-input" id="update_duplicate" name="update_duplicate" value="1" checked>
                                                    <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                    <span>
                                                    بروزرسانی محصولات تکراری (در صورت وجود slug تکراری، اطلاعات محصول به‌روزرسانی شود)

                                                    </span>
                                                </div>
                                            </fieldset>


                                            {{-- Alerts --}}
                                            <div class="alert alert-warning mt-1" role="alert">
                                                <i class="feather icon-alert-triangle ml-1 align-middle"></i>
                                                <span>
                                ترتیب ستون‌ها باید دقیقاً مطابق پیش‌نمایش بالا باشد. اگر ستونی را نمی‌خواهید پر کنید، آن ستون را خالی بگذارید ولی حذف نکنید.
                            </span>
                                            </div>

                                            <div class="alert alert-danger mt-1" role="alert">
                                                <i class="feather icon-alert-circle ml-1 align-middle"></i>
                                                <span>
                                فیلدهای <strong>عنوان</strong>، <strong>قیمت</strong> و <strong>موجودی انبار</strong> الزامی هستند.
                            </span>
                                            </div>

                                            {{-- Upload box --}}
                                            <section class="card shadow-sm">
                                                <div class="card-header border-bottom">
                                                    <h4 class="card-title">
                                                        <i class="feather icon-upload-cloud align-middle mr-50"></i>
                                                        آپلود فایل
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="upload-dropzone" id="upload-dropzone">
                                                        <i class="feather icon-file-text upload-icon"></i>
                                                        <p class="mb-50">فایل <strong>.xlsx</strong> خود را اینجا رها
                                                            کنید
                                                            یا کلیک کنید</p>
                                                        <small class="text-muted d-block mb-1" id="file-name-display">فایلی
                                                            انتخاب نشده است</small>
                                                        <input id="file" type="file" accept=".xlsx" name="file"
                                                               class="d-none" required>
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                                onclick="document.getElementById('file').click()">
                                                            <i class="feather icon-folder align-middle mr-50"></i>انتخاب
                                                            فایل
                                                        </button>
                                                    </div>

                                                    <div class="d-flex justify-content-end mt-2">
                                                        <button type="submit"
                                                                class="btn btn-primary waves-effect waves-light">
                                                            <i class="feather icon-check align-middle mr-50"></i>
                                                            افزودن محصولات
                                                        </button>
                                                    </div>
                                                </div>
                                            </section>

                                            <div id="form-progress" class="progress progress-bar-success progress-xl"
                                                 style="display: none;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                     role="progressbar" style="width:0%">0%
                                                </div>
                                            </div>
                                        </section>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
   <script>
       var DELETE_ERROR_API="{{ route('admin.import.products.delete-error') }}";
       var X_CSRF_TOKEN="{{ csrf_token() }}";
   </script>
    <script src="{{ asset('back/assets/js/pages/products/import.js') }}"></script>
@endpush

@php
    session()->forget('ImportError');
    session()->forget('ImportSuccess');
@endphp
