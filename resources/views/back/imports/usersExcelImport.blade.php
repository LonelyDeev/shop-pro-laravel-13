@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/pages/imports/usersExcelImport.css') }}">
@endpush
@php
    $fields = [
        'first_name'     => ['label' => 'نام',                 'required' => true,  'sample' => 'علی'],
        'last_name'      => ['label' => 'نام خانوادگی',        'required' => false, 'sample' => 'محمدی'],
        'email'          => ['label' => 'ایمیل',               'required' => true,  'sample' => 'ali@example.com'],
        'mobile'         => ['label' => 'موبایل',              'required' => true,  'sample' => '09123456789'],
        'password'       => ['label' => 'رمز ورود',            'required' => true,  'sample' => '123456789'],
        'image'          => ['label' => 'تصویر پروفایل',       'required' => false, 'sample' => 'https://site.com/avatar.jpg'],
        'national_code'  => ['label' => 'کد ملی',              'required' => false, 'sample' => '1234567890'],
        'birth_date'     => ['label' => 'تاریخ تولد',          'required' => false, 'sample' => '1370/01/01'],
        'card_number'    => ['label' => 'شماره کارت',          'required' => false, 'sample' => '5022291012345678'],
    ];
@endphp

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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">مدیریت کاربران</li>
                                    <li class="breadcrumb-item active">ایجاد کاربران از فایل Excel</li>
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
                            <i class="feather icon-users mr-1"></i>
                            افزودن کاربران از فایل Excel
                        </h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form class="form" id="excel-create-form" action="{{ route('admin.import.users.store') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    {{-- LEFT: Field selector --}}
                                    <div class="col-lg-5 col-md-12 mb-3">
                                        <div class="import-panel">
                                            <div class="import-panel-header d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="feather icon-check-square"></i>
                                                    فیلدهای مورد نظر را انتخاب کنید
                                                </h5>
                                                <button type="button" id="toggle-all-fields" class="btn btn-sm btn-outline-primary">
                                                    <i class="feather icon-repeat"></i>
                                                    انتخاب/لغو همه
                                                </button>
                                            </div>
                                            <p class="text-muted mb-1">
                                                ستون‌هایی که می‌خواهید در فایل اکسل وارد کنید را تیک بزنید. ترتیب ستون‌ها در پیش‌نمایش روبه‌رو نمایش داده می‌شود.
                                            </p>
                                            <ul class="row field-list mt-2">
                                                @foreach($fields as $key => $field)
                                                    <li class="col-md-6 col-12 mb-1">
                                                        <div class="custom-control custom-checkbox custom-checkbox-success field-item">
                                                            <fieldset class="checkbox">
                                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                                    <input
                                                                        id="export-checkbox-{{ $key }}"
                                                                        type="checkbox"
                                                                        class="custom-control-input field-checkbox"
                                                                        name="filters[{{ $key }}]"
                                                                        value="1"
                                                                        data-key="{{ $key }}"
                                                                        data-label="{{ $field['label'] }}"
                                                                        data-sample="{{ $field['sample'] }}"
                                                                        checked
                                                                        @if($field['required']) data-required="1" @endif
                                                                    >
                                                                    <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                                    <span>
                                                                      {{ $field['label'] }}
                                                                        @if($field['required'])
                                                                            <span class="badge badge-danger ml-1">الزامی</span>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </fieldset>

                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <div class="alert alert-info mt-2 alert-validation-msg" role="alert">
                                                <i class="feather icon-info ml-1 align-middle"></i>
                                                <span>
                                                    بسیار مهم است که ترتیب ستون‌های فایل اکسل شما دقیقا با پیش‌نمایش روبرو یکسان باشد.
                                                </span>
                                            </div>

                                            <div class="alert alert-warning alert-validation-msg" role="alert">
                                                <i class="feather icon-alert-triangle ml-1 align-middle"></i>
                                                <span>
                                                    فیلدهای
                                                    <span class="badge badge-danger">نام</span>
                                                    <span class="badge badge-danger">ایمیل</span>
                                                    <span class="badge badge-danger">موبایل</span>
                                                    <span class="badge badge-danger">رمز ورود</span>
                                                    الزامی هستند و امکان حذف آن‌ها وجود ندارد.
                                                </span>
                                            </div>

                                            <div class="alert alert-secondary alert-validation-msg" role="alert">
                                                <i class="feather icon-check-circle ml-1 align-middle"></i>
                                                <span>
                                                    پس از آپلود، در صورت عدم دریافت پیام
                                                    <span class="badge badge-success">موفقیت</span>
                                                    کاربران ایجاد نخواهند شد.
                                                </span>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- RIGHT: Preview + upload --}}
                                    <div class="col-lg-7 col-md-12">
                                        <div class="import-panel">
                                            <div class="import-panel-header d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="feather icon-grid mr-1"></i>
                                                    پیش‌نمایش زنده فایل اکسل
                                                </h5>
                                                <span class="badge badge-light-primary" id="column-count-badge">0 ستون</span>
                                            </div>
                                            <p class="text-muted mb-1 mt-1">
                                                ترتیب ستون‌های زیر را دقیقاً در فایل اکسل خود رعایت کنید:
                                            </p>
                                            <div class="excel-preview-wrapper mt-2">
                                                <table class="excel-preview-table" id="excel-preview-table">
                                                    <thead>
                                                    <tr id="excel-col-letters"></tr>
                                                    <tr id="excel-col-headers"></tr>
                                                    </thead>
                                                    <tbody id="excel-sample-rows"></tbody>
                                                </table>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="feather icon-info"></i>
                                                ردیف اول فایل شما باید عناوین فیلدها باشد و از ردیف دوم به بعد داده‌ها قرار گیرد.
                                            </small>

                                            {{-- چک‌باکس بروزرسانی کاربران تکراری --}}
                                        <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" class="custom-control-input" id="update_duplicate" name="update_duplicate" value="1" checked>
                                                    <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                    <span>
                                                      بروزرسانی کاربران تکراری (در صورت وجود موبایل یا ایمیل تکراری، اطلاعات کاربر به‌روزرسانی شود)

                                                    </span>
                                                </div>
                                            </fieldset>

                                            <div class="mt-2">
                                                <h5 class="mb-2">
                                                    <i class="feather icon-upload-cloud mr-1"></i>
                                                    انتخاب فایل Excel
                                                </h5>

                                                <div class="dropzone-area" id="dropzone-area">
                                                    <input id="file" type="file" accept=".xlsx,.xls" name="file" class="dropzone-input" required>
                                                    <div class="dropzone-content">
                                                        <i class="feather icon-upload dropzone-icon"></i>
                                                        <p class="dropzone-text mb-0">فایل اکسل خود را اینجا بکشید و رها کنید</p>
                                                        <small class="text-muted">یا کلیک کنید و فایل را انتخاب نمایید</small>
                                                        <p class="dropzone-filename mt-1 mb-0" id="dropzone-filename"></p>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-block mt-2 waves-effect waves-light">
                                                    <i class="feather icon-check mr-1"></i>
                                                    افزودن فایل و ایجاد کاربران
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                {{-- نمایش خطاهای واردات کاربران --}}
                @php
                    $errorFiles = glob(storage_path('logs/users_import_errors_*.json'));
                    $lastErrorFile = !empty($errorFiles) ? end($errorFiles) : null;
                    $errorData = null;
                    $fileDate = null;
                    if ($lastErrorFile && file_exists($lastErrorFile)) {
                        $errorData = json_decode(file_get_contents($lastErrorFile), true);
                        $fileDate = jdate(filemtime($lastErrorFile),'Y-m-d H:i:s');
                    }

                    $totalCount   = $errorData['total_count']   ?? 0;
                    $successCount = $errorData['success_count'] ?? 0;
                    $failCount    = $errorData['fail_count']    ?? 0;
                    $errorCount   = count($errorData['errors']  ?? []);
                    $successRate  = $totalCount > 0 ? round(($successCount / $totalCount) * 100, 1) : 0;
                @endphp

                @if($errorData && !empty($errorData['failed_rows']))

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card import-error-card">
                                <div class="gradient-header d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="header-title">
                        <span class="icon-wrap">
                            <i class="feather icon-alert-triangle"></i>
                        </span>
                                        <span>گزارش خطاهای واردات کاربران</span>
                                    </div>
                                    <div class="header-actions">
                        <span class="date-pill">
                            <i class="feather icon-calendar"></i>
                            {{ $fileDate ?? 'تاریخ نامشخص' }}
                        </span>
                                        <button type="button" class="btn-delete" id="delete-error-file" data-file="{{ basename($lastErrorFile) }}">
                                            <i class="feather icon-trash-2"></i>
                                            حذف گزارش
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <div class="stat-grid">
                                        <div class="stat-card total">
                                            <i class="feather icon-users stat-icon"></i>
                                            <div class="stat-value">{{ number_format($totalCount) }}</div>
                                            <div class="stat-label">کل کاربران</div>
                                        </div>
                                        <div class="stat-card success">
                                            <i class="feather icon-check-circle stat-icon"></i>
                                            <div class="stat-value">{{ number_format($successCount) }}</div>
                                            <div class="stat-label">واردات موفق</div>
                                        </div>
                                        <div class="stat-card fail">
                                            <i class="feather icon-x-circle stat-icon"></i>
                                            <div class="stat-value">{{ number_format($failCount) }}</div>
                                            <div class="stat-label">واردات ناموفق</div>
                                        </div>
                                        <div class="stat-card error">
                                            <i class="feather icon-alert-octagon stat-icon"></i>
                                            <div class="stat-value">{{ number_format($errorCount) }}</div>
                                            <div class="stat-label">تعداد خطاها</div>
                                        </div>
                                    </div>

                                    <div class="progress-wrap">
                                        <div class="progress-head">
                                            <span class="text-muted">نرخ موفقیت واردات</span>
                                            <strong>{{ $successRate }}%</strong>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $successRate }}%"
                                                 aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="error-table-wrap table-responsive">
                                        <table class="table table-hover error-table">
                                            <thead>
                                            <tr>
                                                <th style="width: 80px;" class="text-center">سطر</th>
                                                <th style="width: 180px;">نام کاربر</th>
                                                <th>دلیل خطا</th>
                                                <th style="width: 130px;" class="text-center">جزئیات داده</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($errorData['failed_rows'] as $row)
                                                <tr>
                                                    <td class="text-center">
                                                        <span class="row-num">{{ $row['row'] ?? '-' }}</span>
                                                    </td>
                                                    <td class="name-cell">
                                                        <i class="feather icon-user text-muted mr-1"></i>
                                                        {{ $row['name'] ?? 'نامشخص' }}
                                                    </td>
                                                    <td>
                                            <span class="error-pill">
                                                <i class="feather icon-info mr-1"></i>
                                                {{ $row['error'] ?? 'نامشخص' }}
                                            </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn-view-data" data-toggle="collapse" data-target="#data-{{ $loop->index }}">
                                                            <i class="feather icon-eye"></i>
                                                            نمایش
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="p-0" style="border: none;">
                                                        <div id="data-{{ $loop->index }}" class="collapse">
                                                            <pre class="data-preview">{{ json_encode($row['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if(!empty($errorData['duplicates']))
                                        <div class="duplicate-alert">
                            <span class="alert-icon">
                                <i class="feather icon-copy"></i>
                            </span>
                                            <div>
                                                <strong>هشدار رکوردهای تکراری:</strong>
                                                تعداد <strong>{{ count($errorData['duplicates']) }}</strong> مورد تکراری در فایل ورودی شناسایی شد.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="form-progress" class="progress progress-bar-success progress-xl" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script>
        var DELETE_ERROR_API="{{ route('admin.import.users.delete-error') }}";
        var X_CSRF_TOKEN="{{ csrf_token() }}";
    </script>
    <script src="{{ asset('back/assets/js/pages/users/import.js') }}"></script>

@endpush

@php
    session()->forget('ImportError');
    session()->forget('ImportSuccess');
@endphp
