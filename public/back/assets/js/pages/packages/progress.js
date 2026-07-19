/* ============================================================
   Package Installation Progress Bar
   نمایش مراحل نصب با progress bar زیبا
   ============================================================ */
(function ($) {
    "use strict";

    const steps = [
        { key: 'download',   label: 'دانلود',      icon: 'icon-download-cloud' },
        { key: 'verify',     label: 'تأیید',       icon: 'icon-shield' },
        { key: 'extract',    label: 'استخراج',     icon: 'icon-archive' },
        { key: 'migrate',    label: 'دیتابیس',     icon: 'icon-database' },
        { key: 'publish',    label: 'انتشار',      icon: 'icon-upload-cloud' },
        { key: 'finalize',   label: 'نهایی‌سازی',  icon: 'icon-check-circle' },
    ];

    let $progress = null;
    let pollingTimer = null;

    /**
     * نمایش progress bar
     */
    function showProgress(packageName) {
        // حذف progress قبلی اگه هست
        hideProgress(false);

        const stepsHtml = steps.map((s, i) => `
            <div class="pkg-progress-step" data-step="${s.key}">
                <i class="feather ${s.icon}"></i>
                <span>${s.label}</span>
            </div>
        `).join('');

        const html = `
            <div class="pkg-install-progress" id="pkg-install-progress">
                <div class="pkg-install-progress-header">
                    <div class="pkg-install-progress-icon">
                        <i class="feather icon-download-cloud"></i>
                    </div>
                    <div>
                        <h6 class="pkg-install-progress-title">در حال نصب ${packageName ? '«' + packageName + '»' : 'پکیج'}</h6>
                        <div class="pkg-install-progress-subtitle">لطفاً صبر کنید...</div>
                    </div>
                </div>
                <div class="pkg-progress-bar-track">
                    <div class="pkg-progress-bar-fill" id="pkg-progress-fill"></div>
                </div>
                <div class="pkg-progress-steps">${stepsHtml}</div>
            </div>
        `;

        $('body').append(html);
        $progress = $('#pkg-install-progress');

        // انیمیشن ورود
        $progress.css({ opacity: 0, transform: 'translateX(-50%) translateY(20px)' })
                 .animate({ opacity: 1 }, 300)
                 .css({ transform: 'translateX(-50%) translateY(0)' });
    }

    /**
     * آپدیت progress بر اساس وضعیت نصب
     */
    function updateProgress(data) {
        if (!$progress) return;

        const status = data.installed?.status || data.status;
        const lastLog = data.last_log;

        // محاسبه درصد پیشرفت
        let percent = 0;
        let currentStep = null;

        if (lastLog) {
            if (lastLog.status === 'running') {
                percent = 50;
                currentStep = 'extract';
            } else if (lastLog.status === 'success') {
                percent = 100;
            } else if (lastLog.status === 'failed') {
                percent = 100;
            }
        }

        if (status === 'updating') {
            percent = Math.max(percent, 30);
        } else if (status === 'installed') {
            percent = 100;
        } else if (status === 'failed') {
            percent = 100;
        }

        // آپدیت نوار پیشرفت
        $('#pkg-progress-fill').css('width', percent + '%');

        // آپدیت مراحل
        if (currentStep) {
            $('.pkg-progress-step').removeClass('active done');
            const stepIdx = steps.findIndex(s => s.key === currentStep);
            steps.forEach((s, i) => {
                const $step = $('.pkg-progress-step[data-step="' + s.key + '"]');
                if (i < stepIdx) {
                    $step.addClass('done');
                } else if (i === stepIdx) {
                    $step.addClass('active');
                }
            });
        }

        if (percent >= 100) {
            if (status === 'installed' || (lastLog && lastLog.status === 'success')) {
                markSuccess();
            } else if (status === 'failed' || (lastLog && lastLog.status === 'failed')) {
                markError(lastLog?.message || 'خطا در نصب');
            }
        }
    }

    /**
     * نمایش وضعیت موفقیت
     */
    function markSuccess() {
        if (!$progress) return;
        $progress.addClass('success');
        $progress.find('.pkg-install-progress-icon i').removeClass('icon-download-cloud').addClass('icon-check-circle');
        $progress.find('.pkg-install-progress-subtitle').text('نصب با موفقیت کامل شد');
        $progress.find('.pkg-progress-step').removeClass('active').addClass('done');

        // آپدیت آیکون مراحل به check
        $progress.find('.pkg-progress-step i').removeClass((i, c) => {
            return (c.match(/icon-\\S+/g) || []).join(' ');
        }).addClass('icon-check');

        setTimeout(function () {
            hideProgress(true);
            Swal.fire({
                icon: 'success',
                title: 'نصب کامل شد!',
                text: 'پکیج با موفقیت نصب شد.',
                confirmButtonText: 'باشه'
            }).then(function () {
                location.reload();
            });
        }, 1500);
    }

    /**
     * نمایش وضعیت خطا
     */
    function markError(message) {
        if (!$progress) return;
        $progress.addClass('error');
        $progress.find('.pkg-install-progress-icon i').removeClass('icon-download-cloud').addClass('icon-alert-triangle');
        $progress.find('.pkg-install-progress-subtitle').text('خطا در نصب');

        setTimeout(function () {
            hideProgress(true);
            Swal.fire({
                icon: 'error',
                title: 'خطا در نصب',
                text: message || 'خطای ناشناخته',
                confirmButtonText: 'باشه'
            }).then(function () {
                location.reload();
            });
        }, 1500);
    }

    /**
     * مخفی کردن progress bar
     */
    function hideProgress(animate) {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
        if (!$progress) return;

        if (animate) {
            $progress.animate({ opacity: 0 }, 300, function () {
                $(this).remove();
                $progress = null;
            });
        } else {
            $progress.remove();
            $progress = null;
        }
    }

    /**
     * شروع polling وضعیت نصب
     */
    function startPolling(slug, packageName) {
        showProgress(packageName);

        let attempts = 0;
        const maxAttempts = 120; // حداکثر 4 دقیقه

        pollingTimer = setInterval(function () {
            attempts++;
            if (attempts > maxAttempts) {
                markError('زمان نصب过长 شد');
                return;
            }

            $.ajax({
                url: (window.routes?.status || '').replace(':slug', slug),
                method: 'GET',
                success: function (resp) {
                    updateProgress(resp);

                    const status = resp.installed?.status;
                    if (status === 'installed' || status === 'failed') {
                        clearInterval(pollingTimer);
                        pollingTimer = null;
                    }
                },
                error: function () {
                    // در صورت خطا، polling ادامه پیدا می‌کنه
                }
            });
        }, 2000);
    }

    // API عمومی
    window.PkgProgress = {
        show: showProgress,
        update: updateProgress,
        success: markSuccess,
        error: markError,
        hide: hideProgress,
        startPolling: startPolling
    };

})(jQuery);
