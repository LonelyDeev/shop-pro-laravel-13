@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/form-builder.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/form-builder-modal.css') }}">
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
                            <!-- بخش فیلدهای فرم (تمام عرض) -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="fb-list-header">
                                            <div class="fb-list-info">
                                                <h4><i class="fa fa-list-check"></i> فیلدهای فرم</h4>
                                                <small>فیلدها را با کشیدن می‌توانید مرتب کنید</small>
                                            </div>
                                            <button type="button" class="fb-trigger-btn" data-toggle="modal" data-target="#fieldTypeModal">
                                                <i class="fa fa-plus"></i> افزودن فیلد جدید
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div id="fields-preview" class="sortable-container">
                                                <div class="fb-empty-state" id="empty-fields-msg">
                                                    <div class="fb-empty-icon"><i class="fa fa-inbox"></i></div>
                                                    <h5>هنوز فیلدی اضافه نشده است</h5>
                                                    <p>برای شروع، روی «افزودن فیلد جدید» بزنید</p>
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

    <!-- ====================== مودال ۱: انتخاب نوع فیلد ====================== -->
    <div class="modal fade fb-modal" id="fieldTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <span class="fb-title-icon"><i class="fa fa-shapes"></i></span>
                        <span class="fb-title-text">
                            <span>انتخاب نوع فیلد</span>
                            <span class="fb-title-sub">نوع فیلد مورد نظر را انتخاب کنید</span>
                        </span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="fb-step-indicator">
                    <div class="fb-step active">
                        <span class="fb-step-num">1</span>
                        <span>انتخاب نوع</span>
                    </div>
                    <div class="fb-step-divider"></div>
                    <div class="fb-step">
                        <span class="fb-step-num">2</span>
                        <span>پیکربندی</span>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="fb-type-grid">
                        <div class="fb-type-card" data-type="text" data-icon="fa-font" data-name="متن ساده">
                            <div class="fb-type-icon-wrap"><i class="fa fa-font"></i></div>
                            <div class="fb-type-name">متن ساده</div>
                            <div class="fb-type-desc">یک خط متن کوتاه</div>
                        </div>
                        <div class="fb-type-card" data-type="email" data-icon="fa-envelope" data-name="ایمیل">
                            <div class="fb-type-icon-wrap"><i class="fa fa-envelope"></i></div>
                            <div class="fb-type-name">ایمیل</div>
                            <div class="fb-type-desc">آدرس ایمیل معتبر</div>
                        </div>
                        <div class="fb-type-card" data-type="number" data-icon="fa-hashtag" data-name="شماره">
                            <div class="fb-type-icon-wrap"><i class="fa fa-hashtag"></i></div>
                            <div class="fb-type-name">شماره</div>
                            <div class="fb-type-desc">فقط عدد وارد شود</div>
                        </div>
                        <div class="fb-type-card" data-type="textarea" data-icon="fa-align-right" data-name="متن چند خطی">
                            <div class="fb-type-icon-wrap"><i class="fa fa-align-right"></i></div>
                            <div class="fb-type-name">متن چند خطی</div>
                            <div class="fb-type-desc">متن طولانی و پاراگراف</div>
                        </div>
                        <div class="fb-type-card" data-type="select" data-icon="fa-list-ul" data-name="انتخابگر">
                            <div class="fb-type-icon-wrap"><i class="fa fa-list-ul"></i></div>
                            <div class="fb-type-name">انتخابگر</div>
                            <div class="fb-type-desc">انتخاب از لیست کشویی</div>
                        </div>
                        <div class="fb-type-card" data-type="checkbox" data-icon="fa-check-square" data-name="چک‌باکس">
                            <div class="fb-type-icon-wrap"><i class="fa fa-check-square"></i></div>
                            <div class="fb-type-name">چک‌باکس</div>
                            <div class="fb-type-desc">چند انتخاب از گزینه‌ها</div>
                        </div>
                        <div class="fb-type-card" data-type="radio" data-icon="fa-dot-circle" data-name="رادیویی">
                            <div class="fb-type-icon-wrap"><i class="fa fa-dot-circle"></i></div>
                            <div class="fb-type-name">رادیویی</div>
                            <div class="fb-type-desc">یک انتخاب از گزینه‌ها</div>
                        </div>
                        <div class="fb-type-card" data-type="date" data-icon="fa-calendar" data-name="تاریخ">
                            <div class="fb-type-icon-wrap"><i class="fa fa-calendar"></i></div>
                            <div class="fb-type-name">تاریخ</div>
                            <div class="fb-type-desc">انتخاب تاریخ شمسی</div>
                        </div>
                        <div class="fb-type-card" data-type="file" data-icon="fa-paperclip" data-name="فایل">
                            <div class="fb-type-icon-wrap"><i class="fa fa-paperclip"></i></div>
                            <div class="fb-type-name">فایل</div>
                            <div class="fb-type-desc">آپلود فایل و تصویر</div>
                        </div>
                        <div class="fb-type-card" data-type="password" data-icon="fa-lock" data-name="رمز عبور">
                            <div class="fb-type-icon-wrap"><i class="fa fa-lock"></i></div>
                            <div class="fb-type-name">رمز عبور</div>
                            <div class="fb-type-desc">ورود پسورد مخفی</div>
                        </div>
                        <div class="fb-type-card" data-type="url" data-icon="fa-link" data-name="لینک">
                            <div class="fb-type-icon-wrap"><i class="fa fa-link"></i></div>
                            <div class="fb-type-name">لینک</div>
                            <div class="fb-type-desc">آدرس وب‌سایت معتبر</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn fb-btn-ghost" data-dismiss="modal">
                        <i class="fa fa-times"></i> انصراف
                    </button>
                    <button type="button" id="fb-continue-btn" class="btn fb-btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;">
                        ادامه <i class="fa fa-arrow-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================== مودال ۲: پیکربندی فیلد ====================== -->
    <div class="modal fade fb-modal" id="fieldConfigModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <span class="fb-title-icon"><i class="fa fa-sliders"></i></span>
                        <span class="fb-title-text">
                            <span>پیکربندی فیلد</span>
                            <span class="fb-title-sub">اطلاعات فیلد را تکمیل کنید</span>
                        </span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="fb-step-indicator">
                    <div class="fb-step completed">
                        <span class="fb-step-num"><i class="fa fa-check" style="font-size: 10px;"></i></span>
                        <span>انتخاب نوع</span>
                    </div>
                    <div class="fb-step-divider"></div>
                    <div class="fb-step active">
                        <span class="fb-step-num">2</span>
                        <span>پیکربندی</span>
                    </div>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="field-type-select" value="text">

                    <!-- بنر نوع فیلد انتخاب‌شده -->
                    <div class="fb-selected-banner">
                        <div class="fb-banner-icon" id="fb-banner-icon">
                            <i class="fa fa-font"></i>
                        </div>
                        <div class="fb-banner-text">
                            <div class="fb-banner-label">نوع فیلد انتخاب شده</div>
                            <div class="fb-banner-title" id="fb-banner-title">متن ساده</div>
                        </div>
                        <button type="button" class="fb-banner-change" id="fb-change-type">
                            <i class="fa fa-exchange-alt"></i> تغییر
                        </button>
                    </div>

                    <!-- اطلاعات پایه -->
                    <div class="fb-form-section">
                        <div class="fb-form-section-title">
                            <i class="fa fa-info-circle"></i>
                            اطلاعات پایه
                        </div>
                        <div class="fb-row-2">
                            <div class="form-group">
                                <label>عنوان فیلد <span class="text-danger">*</span></label>
                                <input type="text" id="field-label" class="form-control" placeholder="مثال: نام و نام خانوادگی">
                                <div class="error-field-label error"></div>
                            </div>
                            <div class="form-group">
                                <label>نام فیلد (name) <span class="text-danger">*</span></label>
                                <input type="text" id="field-name" class="form-control" placeholder="مثال: full_name">
                                <div class="error-field-name error"></div>
                            </div>
                        </div>
                        <small class="text-muted" style="margin-top: -4px;">نام فیلد فقط شامل حروف انگلیسی، اعداد و زیرخط باشد</small>

                        <div class="form-group" id="options-container" style="display: none; margin-top: 12px;">
                            <label>گزینه‌ها <span class="text-danger">*</span></label>
                            <div id="options-list">
                                <div class="input-group mb-2 option-item">
                                    <input type="text" class="form-control" placeholder="گزینه 1">
                                    <div class="input-group-append">
                                        <button class="btn btn-danger remove-option" type="button">×</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-option" class="btn btn-sm">
                                <i class="fa fa-plus"></i> افزودن گزینه
                            </button>
                        </div>
                    </div>

                    <!-- تنظیمات نمایش -->
                    <div class="fb-form-section">
                        <div class="fb-form-section-title">
                            <i class="fa fa-palette"></i>
                            تنظیمات نمایش
                        </div>
                        <div class="fb-row-2">
                            <div class="form-group">
                                <label>متن نمایشی (Placeholder)</label>
                                <input type="text" id="field-placeholder" class="form-control" placeholder="مثال: نام را وارد کنید">
                            </div>
                            <div class="form-group">
                                <label>متن راهنما</label>
                                <input type="text" id="field-help" class="form-control" placeholder="متن راهنمای فیلد">
                            </div>
                            <div class="form-group">
                                <label>مقدار پیش‌فرض</label>
                                <input type="text" id="field-default" class="form-control" placeholder="مقدار پیش‌فرض">
                            </div>
                            <div class="form-group">
                                <label>کلاس CSS</label>
                                <input type="text" id="field-class" class="form-control" placeholder="مثال: form-control-lg">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>قوانین اعتبارسنجی (اختیاری)</label>
                            <input type="text" id="field-validation" class="form-control" placeholder="مثال: min:3|max:255">
                            <small class="text-muted">مثال: min:3|max:255|regex:/[a-z]/</small>
                        </div>
                    </div>

                    <!-- تنظیمات اضافی -->
                    <div class="fb-form-section" style="margin-bottom: 0;">
                        <div class="fb-form-section-title">
                            <i class="fa fa-cog"></i>
                            تنظیمات اضافی
                        </div>
                        <label class="fb-required-toggle" for="field-required" style="margin-bottom: 0;">
                            <div class="fb-required-icon"><i class="fa fa-exclamation"></i></div>
                            <fieldset class="checkbox mb-0">
                                <div class="vs-checkbox-con vs-checkbox-primary">
                                    <input type="checkbox" id="field-required">
                                    <span class="vs-checkbox">
                                        <span class="vs-checkbox--check">
                                            <i class="vs-icon feather icon-check"></i>
                                        </span>
                                    </span>
                                </div>
                            </fieldset>
                            <div class="fb-required-text">
                                <span class="fb-required-label">فیلد اجباری</span>
                                <span class="fb-required-hint">کاربر باید این فیلد را پر کند</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn fb-btn-ghost" id="fb-back-btn">
                        <i class="fa fa-arrow-right"></i> بازگشت
                    </button>
                    <button type="button" id="add-field-btn"
                            data-action="{{ route("admin.forms.render-fields") }}"
                            class="btn fb-btn-success">
                        <i class="fa fa-check"></i> افزودن فیلد
                    </button>
                </div>
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


