@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/form-builder.css') }}">
    <style>
        .sortable-container{
            list-style: none;
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
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">داشبورد</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.forms.index') }}">فرم‌ها</a>
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد فرم جدید</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <form id="main-form" action="{{ route('admin.forms.store') }}" method="post">
                    <!-- Description -->
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">ایجاد فرم جدید</h4>
                        </div>

                        <div id="main-card" class="card-content">
                            <div class="card-body">


                                <div class="nav-vertical">
                                    <div class=" nav nav-tabs flex-column nav-left ">
                                        <ul class="nav nav-tabs flex-column nav-vertical-right" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="baseVerticalLeft-tab1" data-toggle="tab"
                                                   aria-controls="tabVerticalLeft1" href="#tabVerticalLeft1" role="tab"
                                                   aria-selected="false"><i class=" fas fa-clipboard-list"></i> اطلاعات
                                                    کلی</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="productMetaTab" data-toggle="tab"
                                                   aria-controls="tabProductMeta" href="#tabProductMeta" role="tab"
                                                   aria-selected="true"><i class=" fab fa-squarespace"></i> تنظیمات سئو</a>
                                            </li>

                                        </ul>


                                        <div class="nav-vertical-right mt-2">
                                            <div class="col-12 ">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary ">
                                                        <input type="checkbox" name="published" value="1" checked>
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
                                                <button type="submit"
                                                        class="btn btn-primary mr-1 mb-1 waves-effect waves-light"><i
                                                        class="fa fa-save"></i>ذخیره فرم
                                                </button>
                                            </div>
                                        </div>


                                    </div>


                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tabVerticalLeft1" role="tabpanel"
                                             aria-labelledby="baseVerticalLeft-tab1">
                                            <div class="col-12">

                                                <div class="row">
                                                    <!-- اطلاعات فرم -->
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h4 class="card-title">اطلاعات فرم</h4>
                                                            </div>
                                                            <div class="card-content">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>عنوان فرم <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="text"
                                                                                       name="title"
                                                                                       id="title"
                                                                                       class="form-control"
                                                                                       value=""
                                                                                       placeholder="مثال: فرم تماس با ما">
                                                                                <div
                                                                                    class="error-title error-fields"></div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>نامک (آدرس) <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="text"
                                                                                       name="slug"
                                                                                       id="slug"
                                                                                       class="form-control"
                                                                                       value=""
                                                                                       placeholder="مثال: contact-us">
                                                                                <small class="text-muted">بهتر است خالی
                                                                                    بماند و خودکار ایجاد شود</small>
                                                                                <div
                                                                                    class="error-slug error-fields"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label>توضیحات فرم</label>
                                                                                <textarea name="description"
                                                                                          id="description"
                                                                                          class="form-control"
                                                                                          rows="3"
                                                                                          placeholder="توضیحات کوتاه درباره فرم..."></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>پیام موفقیت</label>
                                                                                <input type="text"
                                                                                       name="success_message"
                                                                                       id="success_message"
                                                                                       class="form-control"
                                                                                       value="فرم با موفقیت ارسال شد">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>متن دکمه ارسال</label>
                                                                                <input type="text"
                                                                                       name="button_text"
                                                                                       id="button_text"
                                                                                       class="form-control"
                                                                                       value="ارسال">
                                                                            </div>
                                                                        </div>

                                                                       {{-- <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>ایمیل اعلان</label>
                                                                                <input type="email"
                                                                                       name="email_notify"
                                                                                       id="email_notify"
                                                                                       class="form-control"
                                                                                       value="">
                                                                                <small class="text-muted">در صورت پر
                                                                                    شدن، ایمیل اعلان ارسال
                                                                                    می‌شود</small>
                                                                            </div>
                                                                        </div>--}}
                                                                    </div>


                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>


                                        <div class="tab-pane " id="tabProductMeta" role="tabpanel"
                                             aria-labelledby="productMetaTab">
                                            <div class="col-12">
                                                <div class="form-body">

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>عنوان سئو</label>
                                                                <input type="text" class="form-control"
                                                                       name="meta_title" value="">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>توضیحات سئو</label>
                                                                <textarea class="form-control" name="meta_description"
                                                                          rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <fieldset class="form-group">
                                                                <label>کلمات کلیدی</label>
                                                                <input id="tags" type="text" name="tags"
                                                                       class="form-control">
                                                            </fieldset>
                                                        </div>


                                                        <div class="row seo-help-info">
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i
                                                                        class=" fas fa-check"></i><span class="title">ایجاد Google Snippet برای موتور جستجو</span><span
                                                                        class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i
                                                                        class=" fas fa-check"></i><span class="title">ایجاد پیشنمایش برای شبکه های اجتماعی</span><span
                                                                        class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i
                                                                        class=" fas fa-check"></i><span class="title">افزودن به sitemap.xml سایت</span><span
                                                                        class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                            <div class="col-sm-6 col-12 mb-4">
                                                                <div class="checkbox-container"><i
                                                                        class=" fas fa-check"></i><span class="title">ایجاد تمامی Head TAG های ضروری سئو </span><span
                                                                        class="flag"> (ایجاد خودکار) </span></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>
                    </section>

                    <!-- Description -->
                    <section class="">
                        <div class="row">
                            <!-- بخش افزودن فیلدها -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">افزودن فیلد جدید</h4>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>نوع فیلد <span
                                                        class="text-danger">*</span></label>
                                                <select id="field-type-select" class="form-control">
                                                    <option value="text">✅ متن ساده (Text)</option>
                                                    <option value="email">📧 ایمیل (Email)</option>
=                                                    <option value="number">🔢 شماره (Number)</option>
                                                    <option value="textarea">📝 متن چند خطی (Textarea)
                                                    </option>
                                                    <option value="select">📋 انتخابگر (Select)</option>
                                                    <option value="checkbox">☑️ چک‌باکس (Checkbox)
                                                    </option>
                                                    <option value="radio">🔘 دکمه رادیویی (Radio)
                                                    </option>
                                                    <option value="date">📅 تاریخ (Date)</option>
                                                    <option value="file">📎 فایل (File)</option>
                                                    <option value="password">🔒 رمز عبور (Password)
                                                    </option>
                                                    <option value="url">🌐 لینک (URL)</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>عنوان فیلد <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" id="field-label" class="form-control"
                                                       placeholder="مثال: نام و نام خانوادگی">
                                                <div class="error-field-label error "></div>
                                            </div>

                                            <div class="form-group">
                                                <label>نام فیلد (name) <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" id="field-name" class="form-control"
                                                       placeholder="مثال: full_name">
                                                <small class="text-muted">فقط حروف انگلیسی، اعداد و
                                                    زیرخط</small>
                                                <div class="error-field-name error"></div>
                                            </div>

                                            <div class="form-group" id="options-container"
                                                 style="display: none;">
                                                <label>گزینه‌ها</label>
                                                <div id="options-list">
                                                    <div class="input-group mb-2 option-item">
                                                        <input type="text" class="form-control"
                                                               placeholder="گزینه 1">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-danger remove-option"
                                                                    type="button">×
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" id="add-option"
                                                        class="btn btn-sm btn-secondary">
                                                    <i class="fa fa-plus"></i> افزودن گزینه
                                                </button>
                                            </div>

                                            <div class="form-group">
                                                <label>متن نمایشی </label>
                                                <input type="text" id="field-placeholder" class="form-control"
                                                       placeholder="مثال: نام را وارد کنید">
                                            </div>
                                            <div class="form-group">
                                                <label>متن راهنما</label>
                                                <input type="text" id="field-help" class="form-control"
                                                       placeholder="متن راهنمای فیلد">
                                            </div>

                                            <div class="form-group">
                                                <label>کلاس CSS</label>
                                                <input type="text" id="field-class" class="form-control"
                                                       placeholder="مثال: form-control-lg">
                                            </div>

                                            <div class="form-group">
                                                <label>مقدار پیش‌فرض</label>
                                                <input type="text" id="field-default"
                                                       class="form-control" placeholder="مقدار پیش‌فرض">
                                            </div>

                                            <div class="form-group">
                                                <label>قوانین اعتبارسنجی (اختیاری)</label>
                                                <input type="text" id="field-validation"
                                                       class="form-control"
                                                       placeholder="مثال: min:3|max:255">
                                                <small class="text-muted">مثال:
                                                    min:3|max:255|regex:/[a-z]/</small>
                                            </div>

                                            <fieldset class="checkbox mb-2">
                                                <div class="vs-checkbox-con vs-checkbox-primary ">
                                                    <input type="checkbox" id="field-required" >
                                                    <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    <span>  فیلد اجباری</span>
                                                </div>
                                            </fieldset>

                                            <button type="button" id="add-field-btn"
                                                    data-action="{{ route("admin.forms.render-fields") }}"
                                                    class="btn btn-success btn-block">
                                                <i class="fa fa-plus"></i> افزودن فیلد به فرم
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">فیلدهای فرم</h4>
                                        <small class="text-muted">فیلدها را با کشیدن می‌توانید مرتب
                                            کنید</small>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div id="fields-preview" class="sortable-container">
                                                <div class="text-center text-muted p-5"
                                                     id="empty-fields-msg">
                                                    <i class="fa fa-arrow-right fa-2x"></i>
                                                    <p class="mt-2">از بخش سمت راست فیلدها را اضافه
                                                        کنید</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- دکمه‌های اقدام -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-3" style="margin: 0 auto">
                                                <button type="submit"
                                                        class="btn btn-primary mr-1 mb-1 waves-effect waves-light"><i
                                                        class="fa fa-save"></i>ذخیره فرم
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="fields_data" id="fields-data">
                    </section>

                </form>
            </div>
        </div>
    </div>
@endsection
@include('back.partials.plugins', [
    'plugins' => [
        'ckeditor',
        'jquery-tagsinput',
        'jquery.validate',
        'jquery-ui',
        'jquery-ui-sortable',
        'persian-datepicker',
    ],
])
@push('scripts')
    <script src="{{asset('back/app-assets/plugins/sortable/sortable.min.js')}}"></script>
    <script src="{{ asset('back/assets/js/pages/forms/create.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/forms/all.js') }}"></script>

@endpush


