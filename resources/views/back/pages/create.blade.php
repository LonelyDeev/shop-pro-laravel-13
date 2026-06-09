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
                                    <li class="breadcrumb-item">مدیریت صفحات
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد صفحه
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
                        <h4 class="card-title">ایجاد صفحه جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form class="form" id="page-create-form" action="{{ route('admin.pages.store') }}"
                                  method="post">
                                @csrf
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
                                                <button type="submit"
                                                        class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد
                                                    صفحه
                                                </button>
                                            </div>
                                        </div>


                                    </div>


                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tabVerticalLeft1" role="tabpanel"
                                             aria-labelledby="baseVerticalLeft-tab1">
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
                                                                <textarea id="content" class="form-control" rows="3"
                                                                          name="content"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="alert alert-warning">
                                                        <i class=" fas fa-circle-exclamation"></i>
                                                        شما میتوانید فرمی که طراحی کردید را داخل صفحه خود فراخوانی کنید.
                                                        <br>
                                                        به این صورت <code>[شناسه فرم-form]</code> فراخوانی کنید.<br>
                                                        مثال: <code>[form-1]</code>
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
                            </form>

                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')

    <script src="{{ asset('back/assets/js/pages/pages/create.js') }}"></script>
    <script>
        // در فایل CKEditor configuration
        CKEDITOR.on('instanceReady', function(ev) {
            ev.editor.dataProcessor.htmlFilter.addRules({
                elements: {
                    'div': function(element) {
                        if (element.attributes.class && element.attributes.class.includes('shortcode-form')) {
                            // نمایش شورتکد به جای HTML رندر شده
                            var formId = element.attributes.class.match(/shortcode-form-(\d+)/);
                            if (formId) {
                                element.children = [];
                                element.add({
                                    type: CKEDITOR.NODE_TEXT,
                                    value: '[form-' + formId[1] + ']'
                                });
                            }
                        }
                        return element;
                    }
                }
            });
        });
    </script>
@endpush
