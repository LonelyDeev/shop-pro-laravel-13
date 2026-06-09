@extends('back.layouts.master')

@push('styles')
    <style>
        .form-preview-container {
            background: #f8f9fa;
            min-height: calc(100vh - 200px);
            border-radius: 15px;
            padding: 20px;
        }

        .form-description-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .form-box.full-width {
            width: 100%;
        }

        .form-box.half-width {
            width: 50%;
        }

        .form-box.third-width {
            width: 33.33%;
        }

        .form-preview-field {
            padding: 10px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: move;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-preview-field:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .form-preview-field.dragging {
            opacity: 0.5;
        }

        .field-actions {
            position: absolute;
            top: -10px;
            right: -10px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 10;
        }

        .form-preview-field:hover .field-actions {
            display: flex;
        }

        .drag-handle {
            cursor: grab;
            color: #6c757d;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .settings-sidebar {
            position: sticky;
            top: 20px;
        }

        .field-setting-item {
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-8 col-12 mb-2">
                    <h3 class="content-header-title">پیش‌نمایش فرم: {{ $form->title }}</h3>
                </div>
                <div class="content-header-right col-md-4 col-12 mb-2">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.forms.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> بازگشت
                        </a>
                        <button id="save-settings" class="btn btn-primary" data-action="{{ route("admin.forms.save-settings", $form->id) }}">
                            <i class="fa fa-save"></i> ذخیره تنظیمات
                        </button>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="row">
                    <!-- پنل تنظیمات سمت راست -->
                    <div class="col-md-3">
                        <div class="settings-sidebar">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">تنظیمات فرم</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>موقعیت فرم</label>
                                        <select id="form-position" class="form-control">
                                            <option value="top" {{ $setting->form_position == 'top' ? 'selected' : '' }}>بالای توضیحات</option>
                                            <option value="bottom" {{ $setting->form_position == 'bottom' ? 'selected' : '' }}>پایین توضیحات</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>عرض فرم</label>
                                        <select id="form-width" class="form-control">
                                            <option value="full" {{ $setting->form_width == 'full' ? 'selected' : '' }}>تمام صفحه (100%)</option>
                                            <option value="half" {{ $setting->form_width == 'half' ? 'selected' : '' }}>نصف صفحه (50%)</option>
                                            <option value="third" {{ $setting->form_width == 'third' ? 'selected' : '' }}>یک سوم صفحه (33%)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>تراز فرم</label>
                                        <select id="form-alignment" class="form-control">
                                            <option value="center" {{ $setting->form_alignment == 'center' ? 'selected' : '' }}>وسط</option>
                                            <option value="right" {{ $setting->form_alignment == 'right' ? 'selected' : '' }}>راست</option>
                                            <option value="left" {{ $setting->form_alignment == 'left' ? 'selected' : '' }}>چپ</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>کلاس پیش‌فرض فیلدها</label>
                                        <input type="text" id="default-column-class" class="form-control" value="{{ $setting->default_column_class ?? 'col-md-6' }}">
                                        <small class="text-muted">مثال: col-md-6, col-12, col-md-4</small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>کلاس سفارشی فرم</label>
                                        <input type="text" id="form-class" class="form-control" value="{{ $setting->form_class }}">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>CSS سفارشی</label>
                                        <textarea id="custom-css" rows="4" class="form-control">{{ $setting->custom_css }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">کد فرم</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>کد شورتکد</label>
                                        <input type="text" readonly class="form-control" style="direction: ltr" value="[form-{{ $form->id }}]">
                                        <small>برای استفاده در محتوا</small>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label>لینک مستقیم</label>
                                        <input type="text" readonly class="form-control" style="direction: ltr" value="{{ url('/form/' . $form->slug) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- پیش‌نمایش فرم سمت چپ -->
                    <div class="col-md-9">
                        <div class="form-preview-container">

                            <div class="form-description-box">
                                <h3>{{ $form->title }}</h3>
                                <div class="description-content">
                                    {!! $form->description !!}
                                </div>
                            </div>

                            <div id="form-container"
                                 class="form-box "
                                 style="display: none;">
                                <div class="alert alert-warning">
                                    <i class=" fas fa-circle-exclamation"></i>
                                   با دابل کلیک روی هر فیلد،میتوانید اندازه فیلد را تعیین کنید
                                </div>
                                <form id="preview-form">
                                    @csrf
                                    <div class="row" id="fields-container" data-action="{{ route("admin.forms.update-fields-display", $form->id) }}">
                                        @foreach($fields as $field)
                                            <div class="field-item {{ $field->column_class ?? 'col-md-6' }}" data-id="{{ $field->id }}">
                                                <div class="form-group">
                                                    @if($field->show_label)
                                                        <label for="field_{{ $field->id }}">
                                                            {{ $field->label }}
                                                            @if($field->required)<span class="text-danger">*</span>@endif
                                                        </label>
                                                    @endif

                                                    <input type="{{ $field->type }}"
                                                           name="{{ $field->name }}"
                                                           class="form-control"
                                                           placeholder="{{ $field->placeholder }}"
                                                        {{ $field->required ? 'required' : '' }}>

                                                    @if($field->help_text)
                                                        <small class="text-muted">{{ $field->help_text }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-primary">{{ $form->button_text }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تنظیمات فیلد -->
    <div class="modal fade" id="fieldSettingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تنظیمات فیلد</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="current-field-id">
                    <div class="form-group">
                        <label>کلاس ستون</label>
                        <select id="field-column-class" class="form-control">
                            <option value="col-12">تمام عرض (col-12)</option>
                            <option value="col-md-6 col-12">نصف عرض (col-md-6)</option>
                            <option value="col-md-4 col-12">یک سوم عرض (col-md-4)</option>
                            <option value="col-md-3 col-12">یک چهارم عرض (col-md-3)</option>
                            <option value="col-md-8 col-12">دو سوم عرض (col-md-8)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="field-show-label"> نمایش لیبل
                        </label>
                    </div>
                    <div class="form-group">
                        <label>کلاس والد</label>
                        <input type="text" id="field-wrapper-class" class="form-control" placeholder="مثال: mb-3">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    <button type="button" id="save-field-settings" class="btn btn-primary">ذخیره</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{asset('back/app-assets/plugins/sortable/sortable.min.js')}}"></script>
    <script src="{{ asset('back/assets/js/pages/forms/preview.js') }}"></script>

@endpush
