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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">توسعه دهنده</li>
                                    <li class="breadcrumb-item active">بروزرسانی</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="alert alert-warning py-2" role="alert">
                    <i class="feather icon-alert-triangle mr-1 align-middle"></i>
                    <span>لطفا توجه داشته باشید در صورتی که نرم افزار را شخصی سازی کرده اید و در کدها تغییراتی ایجاد کرده اید، با بروزرسانی نرم افزار تغییرات از بین می روند.</span>
                </div>

                <section class="card">
                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-md-12">
                                <h3>بروزرسانی نرم افزار</h3>
                                <hr>

                                <div class="alert alert-info" role="alert">
                                    <span>نسخه فعلی نرم افزار شما {{ $versionInstalled }}</span>
                                </div>

                                @if ($isNewVersionAvailable)
                                    <div class="alert alert-primary" role="alert">
                                        <i class="feather icon-check mr-1 align-middle"></i>
                                        <span>نسخه {{ $versionAvailable }} موجود است و هم اکنون میتوانید نرم افزار را بروزرسانی کنید.</span>
                                    </div>

                                    <button id="update-application" type="button" class="btn btn-lg btn-success mb-1 waves-effect waves-light">
                                        <i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی
                                    </button>
                                @else
                                    <div class="alert alert-success" role="alert">
                                        <span>شما از آخرین نسخه ی موجود نرم افزار استفاده میکنید.</span>
                                    </div>
                                @endif

                                <button id="updater-after" type="button" class="btn btn-lg btn-primary mb-1 waves-effect waves-light">
                                    <i class="feather icon-settings mr-1"></i> اجرای دستورات بعد از بروزرسانی
                                </button>

                                <!-- دکمه بررسی مجدد -->
                                <button id="check-update" type="button" class="btn btn-lg btn-info mb-1 waves-effect waves-light">
                                    <i class="feather icon-refresh-cw mr-1"></i> بررسی مجدد
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Progress Bar Section -->
                <div class="card" id="progress-card" style="display: none;">
                    <div class="card-header">
                        <h4 class="card-title">پیشرفت بروزرسانی</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="form-group">
                                <div class="progress progress-bar-success progress-xl" style="height: 30px;">
                                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width: 0%; height: 30px; line-height: 30px; font-size: 14px;"
                                         aria-valuenow="0"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        0%
                                    </div>
                                </div>
                                <p id="progress-text" class="text-muted mt-2">
                                    <i class="feather icon-loader mr-1"></i>
                                    در حال آماده‌سازی...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <div id="status-messages"></div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تغییرات بروزرسانی ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <ul class="activity-timeline timeline-left list-unstyled">
                                <li>
                                    <div class="timeline-icon bg-success">
                                        <i class="feather icon-chevron-left font-medium-2 align-middle"></i>
                                    </div>
                                    <div class="timeline-info">
                                        <p class="font-weight-bold mb-0">نسخه 1.0.0</p>
                                        <p class="font-small-3">- انتشار اولین نسخه</p>
                                    </div>
                                    <small class="text-primary">01 آبان 1402</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let progressInterval = null;
            let statusCheckInterval = null;
            let isProcessing = false;

            // تابع نمایش پیام
            function showMessage(type, message, dismissible = true) {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                        type === 'warning' ? 'alert-warning' : 'alert-info';

                const dismissBtn = dismissible ? '<button type="button" class="close" data-dismiss="alert">&times;</button>' : '';
                const html = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${dismissBtn}
                        ${message}
                    </div>
                `;
                $('#status-messages').append(html);
            }

            // تابع نمایش/مخفی کردن پیشرفت
            function toggleProgress(show) {
                if (show) {
                    $('#progress-card').slideDown(300);
                    $('#progress-bar').css('width', '0%').text('0%');
                    $('#progress-text').html('<i class="feather icon-loader mr-1"></i> در حال آماده‌سازی...');
                } else {
                    $('#progress-card').slideUp(300);
                }
            }

            // تابع آپدیت پیشرفت
            function updateProgress(percent, text = null) {
                $('#progress-bar').css('width', percent + '%').text(percent + '%');
                if (text) {
                    $('#progress-text').html(text);
                }

                // تغییر رنگ بر اساس درصد
                const bar = $('#progress-bar');
                if (percent < 30) {
                    bar.removeClass('bg-success bg-info bg-warning').addClass('bg-primary');
                } else if (percent < 60) {
                    bar.removeClass('bg-primary bg-success bg-warning').addClass('bg-info');
                } else if (percent < 90) {
                    bar.removeClass('bg-primary bg-info bg-warning').addClass('bg-success');
                } else if (percent < 100) {
                    bar.removeClass('bg-primary bg-info bg-success').addClass('bg-warning');
                } else {
                    bar.removeClass('bg-primary bg-info bg-warning').addClass('bg-success');
                }
            }

            // تابع دریافت وضعیت آپدیت
            function checkUpdateStatus() {
                $.ajax({
                    url: '{{ route("admin.developer.updateStatus") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.status === 'processing') {
                            isProcessing = true;
                            toggleProgress(true);
                            updateProgress(response.progress || 0,
                                `<i class="feather icon-loader mr-1"></i> در حال بروزرسانی... (${response.progress || 0}%)`);

                            // غیرفعال کردن دکمه‌ها
                            $('#update-application').prop('disabled', true);
                            $('#updater-after').prop('disabled', true);

                            // تغییر متن دکمه
                            $('#update-application').html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');
                        } else if (response.status === 'success') {
                            isProcessing = false;
                            toggleProgress(false);
                            showMessage('success', `✅ بروزرسانی با موفقیت انجام شد! نسخه جدید: ${response.version || 'نامشخص'}`);

                            // فعال کردن دکمه‌ها
                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');

                            // به‌روزرسانی نسخه نمایش داده شده
                            setTimeout(() => location.reload(), 3000);

                            if (progressInterval) {
                                clearInterval(progressInterval);
                                progressInterval = null;
                            }
                        } else if (response.status === 'error') {
                            isProcessing = false;
                            toggleProgress(false);
                            showMessage('error', `❌ خطا در بروزرسانی: ${response.message || 'خطای ناشناخته'}`);

                            // فعال کردن دکمه‌ها
                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');

                            if (progressInterval) {
                                clearInterval(progressInterval);
                                progressInterval = null;
                            }
                        } else {
                            // اگر آپدیت در حال انجام نیست
                            if (isProcessing) {
                                isProcessing = false;
                                toggleProgress(false);
                                $('#update-application').prop('disabled', false);
                                $('#updater-after').prop('disabled', false);
                                $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                            }
                        }
                    },
                    error: function() {
                        // اگر خطا رخ داد، سعی نکنید دوباره
                        if (isProcessing) {
                            isProcessing = false;
                            toggleProgress(false);
                            showMessage('error', '❌ خطا در ارتباط با سرور برای بررسی وضعیت');
                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                        }
                    }
                });
            }

            // تابع شروع بروزرسانی
            $('#update-application').on('click', function() {
                const $btn = $(this);

                // جلوگیری از کلیک مجدد
                if ($btn.prop('disabled') || isProcessing) {
                    return;
                }

                // غیرفعال کردن دکمه
                $btn.prop('disabled', true);
                $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال شروع...');

                // نمایش پیشرفت
                toggleProgress(true);
                updateProgress(0, '<i class="feather icon-loader mr-1"></i> در حال شروع بروزرسانی...');

                // ارسال درخواست بروزرسانی
                $.ajax({
                    url: '{{ route("admin.developer.updateApplication") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'started') {
                            showMessage('success', `✅ بروزرسانی شروع شد! نسخه ${response.version || 'جدید'} در حال دانلود...`);
                            isProcessing = true;

                            // شروع چک کردن وضعیت
                            if (progressInterval) {
                                clearInterval(progressInterval);
                            }

                            // هر 5 ثانیه وضعیت را بررسی کن
                            progressInterval = setInterval(checkUpdateStatus, 5000);

                            // بلافاصله یک بار چک کن
                            setTimeout(checkUpdateStatus, 1000);

                            // تغییر متن دکمه
                            $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');
                            $('#updater-after').prop('disabled', true);
                        } else {
                            showMessage('error', `❌ ${response.message || 'خطا در شروع بروزرسانی'}`);
                            $btn.prop('disabled', false);
                            $btn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                            toggleProgress(false);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        showMessage('error', `❌ ${response?.message || 'خطا در ارتباط با سرور'}`);
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                        toggleProgress(false);
                        isProcessing = false;
                    }
                });
            });

            // دکمه اجرای دستورات بعد از بروزرسانی
            $('#updater-after').on('click', function() {
                const $btn = $(this);

                if ($btn.prop('disabled')) {
                    return;
                }

                $btn.prop('disabled', true);
                $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال اجرا...');

                $.ajax({
                    url: '{{ route("admin.developer.updaterAfter") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            showMessage('success', `✅ ${response.message || 'دستورات با موفقیت اجرا شدند'}`);
                        } else {
                            showMessage('error', `❌ ${response.message || 'خطا در اجرای دستورات'}`);
                        }
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-settings mr-1"></i> اجرای دستورات بعد از بروزرسانی');
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        showMessage('error', `❌ ${response?.message || 'خطا در ارتباط با سرور'}`);
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-settings mr-1"></i> اجرای دستورات بعد از بروزرسانی');
                    }
                });
            });

            // دکمه بررسی مجدد
            $('#check-update').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بررسی...');

                // پاک کردن پیام‌های قبلی
                $('#status-messages').empty();

                // اگر در حال بروزرسانی هستیم، وضعیت را چک کن
                if (isProcessing) {
                    checkUpdateStatus();
                    $btn.prop('disabled', false);
                    $btn.html('<i class="feather icon-refresh-cw mr-1"></i> بررسی مجدد');
                    return;
                }

                // بررسی وجود آپدیت جدید
                $.ajax({
                    url: '{{ route("admin.developer.checkUpdate") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.update_available) {
                            showMessage('info', `📦 نسخه جدید ${response.version} موجود است!`);
                            // تغییر وضعیت دکمه بروزرسانی
                            const updateBtn = $('#update-application');
                            updateBtn.prop('disabled', false);
                            updateBtn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                            // رفرش صفحه بعد از 2 ثانیه
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showMessage('success', '✅ شما از آخرین نسخه استفاده میکنید');
                        }
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-refresh-cw mr-1"></i> بررسی مجدد');
                    },
                    error: function() {
                        showMessage('error', '❌ خطا در بررسی نسخه جدید');
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-refresh-cw mr-1"></i> بررسی مجدد');
                    }
                });
            });

            // اگر در حال بروزرسانی هستیم، وضعیت را چک کن
            @if(isset($isProcessing) && $isProcessing)
                isProcessing = true;
            toggleProgress(true);
            $('#update-application').prop('disabled', true);
            $('#updater-after').prop('disabled', true);
            $('#update-application').html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');

            // شروع چک کردن وضعیت
            progressInterval = setInterval(checkUpdateStatus, 5000);
            setTimeout(checkUpdateStatus, 1000);
            @endif

            // پاکسازی interval در زمان خروج از صفحه
            $(window).on('beforeunload', function() {
                if (progressInterval) {
                    clearInterval(progressInterval);
                }
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
            });
        });
    </script>
@endpush
