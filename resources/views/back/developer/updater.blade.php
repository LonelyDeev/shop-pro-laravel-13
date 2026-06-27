@extends('back.layouts.master')
@push('styles')
    <style>
        .update-description-card{
            border: 1px solid #e3e1fc !important;
        }
        .update-description-card h5{
            font-size: 1rem;
            color: #4C4993;
        }
        .update-description {
            font-size: 14px;
            line-height: 1.8;
        }

        .update-description ul {
            padding-right: 0;
        }

        .update-description ul li {
            padding: 5px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .update-description ul li:last-child {
            border-bottom: none;
        }
        .update-description ul li i{
            margin: 6px;
            color: #4C4993;
        }
        .update-description .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .timeline-item {
            position: relative;
            padding-right: 20px;
        }

        .timeline-item:last-child .timeline-line {
            display: none;
        }

        .timeline-marker {
            width: 16px;
            height: 16px;
            min-width: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 2px;
        }

        .timeline-content {
            padding-right: 15px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
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

                                    <!-- نمایش توضیحات و تغییرات -->
                                    @if(!empty($description))
                                        <div class="card mb-4 border-primary update-description-card">
                                            <div class="card-header alert alert-primary p-1 m-0">
                                                <h5 class="mb-0">
                                                    <i class="feather icon-git-merge mr-2"></i>
                                                    تغییرات نسخه {{ $versionAvailable }}
                                                </h5>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="update-description ">
                                                    @php
                                                        // تبدیل توضیحات به لیست اگر با خط جدید جدا شده باشد
                                                        $lines = explode("\n", trim($description));
                                                        $hasList = false;
                                                        foreach ($lines as $line) {
                                                            if (preg_match('/^[\s]*[-*•]/', $line)) {
                                                                $hasList = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp

                                                    @if($hasList)
                                                        <ul class="list-unstyled mb-0 ml-2">
                                                            @foreach($lines as $line)
                                                                @if(trim($line))
                                                                    <li class="d-flex align-items-start">
                                                                        <i class="feather icon-check-circle "></i>
                                                                        <span>{{ trim(preg_replace('/^[\s]*[-*•]\s*/', '', $line)) }}</span>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <div class="p-3 bg-light rounded">
                                                            {!! nl2br(e($description)) !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

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

                <!-- Log Messages -->
                <div class="card" id="log-card" style="display: none;">
                    <div class="card-header">
                        <h4 class="card-title">گزارش بروزرسانی</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div id="log-content" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 13px; direction: ltr;">
                            </div>
                        </div>
                    </div>
                </div>

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
            let isProcessing = false;
            let logMessages = [];

            // تابع اضافه کردن لاگ
            function addLog(message, type = 'info') {
                const colors = {
                    info: '#17a2b8',
                    success: '#28a745',
                    warning: '#ffc107',
                    error: '#dc3545'
                };

                const icon = {
                    info: '📘',
                    success: '✅',
                    warning: '⚠️',
                    error: '❌'
                };

                const logEntry = `<div style="color: ${colors[type] || '#000'}; padding: 5px 0; border-bottom: 1px solid #e9ecef;">
                    <span style="font-weight: bold;">${icon[type] || '📌'}</span>
                    ${message}
                    <span style="float: left; color: #6c757d; font-size: 11px;">${new Date().toLocaleTimeString('fa-IR')}</span>
                </div>`;

                $('#log-content').append(logEntry);
                $('#log-content').scrollTop($('#log-content')[0].scrollHeight);
                $('#log-card').slideDown(300);
            }

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

                // اضافه کردن به لاگ
                const logType = type === 'success' ? 'success' :
                    type === 'error' ? 'error' :
                        type === 'warning' ? 'warning' : 'info';
                addLog(message, logType);
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
            // تابع دریافت وضعیت آپدیت
            function checkUpdateStatus() {
                $.ajax({
                    url: '{{ route("admin.developer.updateStatus") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.status === 'processing') {
                            isProcessing = true;
                            toggleProgress(true);

                            // نمایش پیام دقیق مرحله
                            const progressText = response.step || 'در حال بروزرسانی...';
                            updateProgress(response.progress || 0,
                                `<i class="feather icon-loader mr-1"></i> ${progressText} (${response.progress || 0}%)`);

                            // غیرفعال کردن دکمه‌ها
                            $('#update-application').prop('disabled', true);
                            $('#updater-after').prop('disabled', true);
                            $('#check-update').prop('disabled', true);
                            $('#update-application').html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');

                            // اگر مرحله جدید است، به لاگ اضافه کن
                            if (response.step && response.step !== lastStep) {
                                addLog(`📌 ${response.step}`, 'info');
                                lastStep = response.step;
                            }

                        } else if (response.status === 'waiting') {
                            isProcessing = true;
                            toggleProgress(true);
                            updateProgress(0, `<i class="feather icon-loader mr-1"></i> ${response.step || 'در انتظار شروع...'}`);
                            addLog(`⏳ ${response.message || 'در انتظار شروع بروزرسانی...'}`, 'warning');

                        } else if (response.status === 'success') {
                            isProcessing = false;
                            toggleProgress(false);

                            const successMsg = `✅ بروزرسانی با موفقیت انجام شد! نسخه جدید: ${response.version || 'نامشخص'}`;
                            showMessage('success', successMsg);
                            addLog(`✅ ${successMsg}`, 'success');

                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#check-update').prop('disabled', false);
                            $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');

                            setTimeout(() => location.reload(), 3000);

                            if (progressInterval) {
                                clearInterval(progressInterval);
                                progressInterval = null;
                            }

                        } else if (response.status === 'error') {
                            isProcessing = false;
                            toggleProgress(false);

                            const errorMsg = `❌ خطا در بروزرسانی: ${response.message || 'خطای ناشناخته'}`;
                            showMessage('error', errorMsg);
                            addLog(`❌ ${errorMsg}`, 'error');

                            if (response.details) {
                                addLog(`📋 جزئیات: ${response.details}`, 'warning');
                            }

                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#check-update').prop('disabled', false);
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
                                $('#check-update').prop('disabled', false);
                                $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                            }
                        }
                    },
                    error: function(xhr) {
                        if (isProcessing) {
                            isProcessing = false;
                            toggleProgress(false);
                            showMessage('error', '❌ خطا در ارتباط با سرور برای بررسی وضعیت');
                            addLog('❌ خطا در ارتباط با سرور', 'error');
                            $('#update-application').prop('disabled', false);
                            $('#updater-after').prop('disabled', false);
                            $('#check-update').prop('disabled', false);
                            $('#update-application').html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                        }
                    }
                });
            }

// متغیر برای ذخیره آخرین مرحله
            let lastStep = '';

            // تابع شروع بروزرسانی
            $('#update-application').on('click', function() {
                const $btn = $(this);

                if ($btn.prop('disabled') || isProcessing) {
                    showMessage('warning', '⚠️ در حال حاضر بروزرسانی در حال انجام است. لطفاً صبر کنید.');
                    return;
                }

                // پاک کردن لاگ‌های قبلی
                $('#log-content').empty();
                $('#status-messages').empty();

                // غیرفعال کردن دکمه
                $btn.prop('disabled', true);
                $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال شروع...');

                // نمایش پیشرفت
                toggleProgress(true);
                updateProgress(0, '<i class="feather icon-loader mr-1"></i> در حال شروع بروزرسانی...');
                addLog('🚀 شروع فرآیند بروزرسانی', 'info');

                // ارسال درخواست بروزرسانی
                $.ajax({
                    url: '{{ route("admin.developer.updateApplication") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'started') {
                            const msg = `✅ بروزرسانی شروع شد! نسخه ${response.version || 'جدید'} در حال دانلود...`;
                            showMessage('success', msg);
                            addLog(`✅ ${msg}`, 'success');
                            addLog('⏳ لطفاً منتظر بمانید... این فرآیند ممکن است چند دقیقه طول بکشد', 'warning');

                            isProcessing = true;

                            // شروع چک کردن وضعیت
                            if (progressInterval) {
                                clearInterval(progressInterval);
                            }

                            // هر 3 ثانیه وضعیت را بررسی کن
                            progressInterval = setInterval(checkUpdateStatus, 3000);

                            // بلافاصله یک بار چک کن
                            setTimeout(checkUpdateStatus, 1000);

                            // تغییر متن دکمه
                            $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');
                            $('#updater-after').prop('disabled', true);
                            $('#check-update').prop('disabled', true);

                        } else if (response.status === 'queued') {
                            // Job در صف قرار گرفته
                            const msg = `⏳ درخواست بروزرسانی در صف قرار گرفت. لطفاً صبر کنید...`;
                            showMessage('info', msg);
                            addLog(`⏳ ${msg}`, 'warning');
                            addLog(`📋 شناسه Job: ${response.job_id || 'نامشخص'}`, 'info');

                            isProcessing = true;

                            // شروع چک کردن وضعیت
                            if (progressInterval) {
                                clearInterval(progressInterval);
                            }

                            progressInterval = setInterval(checkUpdateStatus, 5000);
                            setTimeout(checkUpdateStatus, 2000);

                            $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');
                            $('#updater-after').prop('disabled', true);
                            $('#check-update').prop('disabled', true);

                        } else {
                            showMessage('error', `❌ ${response.message || 'خطا در شروع بروزرسانی'}`);
                            addLog(`❌ ${response.message || 'خطا در شروع بروزرسانی'}`, 'error');
                            $btn.prop('disabled', false);
                            $btn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                            toggleProgress(false);
                            $('#check-update').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const errorMsg = response?.message || 'خطا در ارتباط با سرور';
                        showMessage('error', `❌ ${errorMsg}`);
                        addLog(`❌ ${errorMsg}`, 'error');
                        addLog(`📋 کد خطا: ${xhr.status}`, 'error');

                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');
                        toggleProgress(false);
                        isProcessing = false;
                        $('#check-update').prop('disabled', false);
                    }
                });
            });

            // دکمه اجرای دستورات بعد از بروزرسانی
            $('#updater-after').on('click', function() {
                const $btn = $(this);

                if ($btn.prop('disabled')) {
                    showMessage('warning', '⚠️ لطفاً ابتدا بروزرسانی را کامل کنید.');
                    return;
                }

                $btn.prop('disabled', true);
                $btn.html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال اجرا...');

                addLog('🔄 شروع اجرای دستورات پس از بروزرسانی', 'info');

                $.ajax({
                    url: '{{ route("admin.developer.updaterAfter") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            const msg = `✅ ${response.message || 'دستورات با موفقیت اجرا شدند'}`;
                            showMessage('success', msg);
                            addLog(`✅ ${msg}`, 'success');
                            if (response.details) {
                                addLog(`📋 ${response.details}`, 'info');
                            }
                        } else {
                            const msg = `❌ ${response.message || 'خطا در اجرای دستورات'}`;
                            showMessage('error', msg);
                            addLog(`❌ ${msg}`, 'error');
                            if (response.details) {
                                addLog(`📋 ${response.details}`, 'error');
                            }
                        }
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-settings mr-1"></i> اجرای دستورات بعد از بروزرسانی');
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const msg = response?.message || 'خطا در ارتباط با سرور';
                        showMessage('error', `❌ ${msg}`);
                        addLog(`❌ ${msg}`, 'error');
                        addLog(`📋 کد خطا: ${xhr.status}`, 'error');

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
                addLog('🔍 بررسی مجدد نسخه جدید...', 'info');

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
                            const msg = `📦 نسخه جدید ${response.version} موجود است!`;
                            showMessage('info', msg);
                            addLog(`📦 ${msg}`, 'info');
                            if (response.changelog) {
                                addLog(`📋 تغییرات: ${response.changelog}`, 'info');
                            }

                            // تغییر وضعیت دکمه بروزرسانی
                            const updateBtn = $('#update-application');
                            updateBtn.prop('disabled', false);
                            updateBtn.html('<i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی');

                            // رفرش صفحه بعد از 2 ثانیه
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            const msg = '✅ شما از آخرین نسخه استفاده میکنید';
                            showMessage('success', msg);
                            addLog(`✅ ${msg}`, 'success');
                        }
                        $btn.prop('disabled', false);
                        $btn.html('<i class="feather icon-refresh-cw mr-1"></i> بررسی مجدد');
                    },
                    error: function() {
                        const msg = '❌ خطا در بررسی نسخه جدید';
                        showMessage('error', msg);
                        addLog(`❌ ${msg}`, 'error');
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
            $('#check-update').prop('disabled', true);
            $('#update-application').html('<i class="feather icon-refresh-cw fa-spin mr-1"></i> در حال بروزرسانی...');

            addLog('🔄 بازیابی وضعیت بروزرسانی...', 'info');

            // شروع چک کردن وضعیت
            progressInterval = setInterval(checkUpdateStatus, 5000);
            setTimeout(checkUpdateStatus, 1000);
            @endif

            // پاکسازی interval در زمان خروج از صفحه
            $(window).on('beforeunload', function() {
                if (progressInterval) {
                    clearInterval(progressInterval);
                }
            });
        });
    </script>
@endpush
