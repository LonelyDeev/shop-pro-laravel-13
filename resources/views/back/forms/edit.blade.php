@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/form-builder.css') }}">
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
                <form id="main-form" action="{{ route('admin.forms.update',$form) }}" method="post">
                    @method('PUT')
                    <!-- Description -->
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">ویرایش فرم </h4>
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
                                                        <input type="checkbox" name="published" value="1"  {{$form->published ? 'checked' : ''}}>
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
                                                                                       value="{{$form->title}}"
                                                                                       placeholder="مثال: فرم تماس با ما">
                                                                                <div
                                                                                    class="error-title error-fields"></div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>اسلاگ (آدرس) <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="text"
                                                                                       name="slug"
                                                                                       id="slug"
                                                                                       class="form-control"
                                                                                       value="{{$form->title}}"
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
                                                                                          placeholder="توضیحات کوتاه درباره فرم...">{!! $form->description !!}</textarea>
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
                                                                                       value="{{$form->success_message}}">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>متن دکمه ارسال</label>
                                                                                <input type="text"
                                                                                       name="button_text"
                                                                                       id="button_text"
                                                                                       class="form-control"
                                                                                       value="{{$form->button_text}}">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
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
                                                                        </div>
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
                                                                       name="meta_title" value="{{$form->meta_title}}">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>توضیحات سئو</label>
                                                                <textarea class="form-control" name="meta_description"
                                                                          rows="3">{{$form->meta_description}}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <fieldset class="form-group">
                                                                <label>کلمات کلیدی</label>
                                                                <input id="tags" type="text" name="tags" value="{{$form->getTags}}"
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
                                                    <option value="number">🔢 شماره (Number)</option>
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
                                                <label>متن راهنما</label>
                                                <input type="text" id="field-help" class="form-control"
                                                       placeholder="متن راهنمای فیلد">
                                            </div>

                                            <div class="form-group">
                                                <label> متن نمایشی</label>
                                                <input type="text" id="field-placeholder" class="form-control"
                                                       placeholder="مثال: نام را وارد کنید">
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

                                            <div class="dd nestable" data-action="{{route('admin.forms.reorder-fields',$form)}}">

                                                <ol id="exist-fields" class="sortable-container dd-list">
                                                        @foreach($form->fields as $index => $field)
                                                        <li class="preview-field dd-item" id="preview-field-{{ $field['id'] }}" data-id="{{ $field['id'] }}" data-order="{{ $index }}">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fa fa-arrows-alt  dd-handle" style="cursor: move; margin-right: 10px; color: #6c757d;"></i>
                                                                        <strong class="mr-2 ml-2" data-title="title">{{ $field['label'] }}</strong>

                                                                        <span class="badge badge-secondary" data-title="{{$field['type']}}">{{ $typeNames[$field['type']] ?? $field['type'] }}</span>
                                                                        @if($field['required'] ?? false)
                                                                            <span class="badge badge-danger mr-1 ml-2" data-title="required">ضروری</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-2">
                                                                        <small class="text-muted" data-title="name">نام: <code>{{ $field['name'] }}</code></small>
                                                                        @if(!empty($field['placeholder']))
                                                                            <small class="text-muted mr-2" data-title="help_text">| متن نمایشی: {{ $field['placeholder'] }}</small>
                                                                        @endif
                                                                    @if(!empty($field['help_text']))
                                                                            <small class="text-muted mr-2" data-title="help_text">| راهنما: {{ $field['help_text'] }}</small>
                                                                        @endif
                                                                        @if(!empty($field['validation']) || !empty($field['rules_validation']))
                                                                            <small class="text-muted mr-2" data-title="rules_validation">| اعتبارسنجی: {{ $field['validation'] ?? $field['rules_validation'] }}</small>
                                                                        @endif
                                                                        @if(!empty($field['default_value']))
                                                                            <small class="text-muted mr-2" data-title="default_value">| مقدار پیش‌فرض: {{ $field['default_value'] }}</small>
                                                                        @endif
                                                                        @if(!empty($field['class']))
                                                                            <small class="text-muted mr-2" data-title="class">| کلاس: {{ $field['class'] }}</small>
                                                                        @endif
                                                                    </div>
                                                                    @if(!empty($field['options']) && is_array($field['options']))
                                                                        <div class="mt-1">
                                                                            <small>گزینه‌ها: </small>
                                                                            @foreach($field['options'] as $option)
                                                                                <span class="badge badge-light">{{ $option }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @can('forms.delete')
                                                                    <button type="button" class="btn btn-sm btn-danger remove-exist-field" data-toggle="modal" data-id="{{$field['id']}}" data-target="#delete-modal" data-action="{{route('admin.forms.delete-field',['form'=>$form,'field'=>$field['id']])}}">
                                                                        <i class="fa-solid fa-trash-can" style="font-size: 10px"></i></button>
                                                                @endcan

                                                            </div>
                                                        </li>
                                                        @endforeach
                                                </ol>
                                                 <ol id="fields-preview" class="sortable-container dd-list">

                                                </ol>
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
    @can('forms.delete')
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
                        با حذف فیلد دیگر قادر به بازیابی آن نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete-form">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">
                                خیر
                            </button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
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
<script src="{{ asset('back/assets/js/pages/forms/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/forms/edit.js') }}"></script>



@endpush


