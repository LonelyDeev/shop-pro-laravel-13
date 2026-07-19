/* ============================================================
   Packages - Show page (نصب / آپدیت / حذف / polling)
   ============================================================ */
(function ($) {
    "use strict";

    let pollTimer = null;
    const SLUG = window.PACKAGE_SLUG;

    $(function () {
        // باز کردن modal تأیید نصب
        $('.btn-install').on('click', function (e) {
            e.preventDefault();
            const name = $(this).data('name');
            const isFree = $(this).data('free') === '1' || $(this).data('free') === true;
            const price = parseInt($(this).data('price')) || 0;

            $('#confirm-pkg-name').text(name);

            if (isFree) {
                $('#confirm-payment-info').addClass('d-none');
                $('#confirm-btn-text').text('شروع نصب');
            } else {
                $('#confirm-payment-info').removeClass('d-none');
                $('#confirm-pkg-price').text(number_format(price));
                $('#confirm-btn-text').text('پرداخت و نصب');
            }

            $('#install-confirm-modal').modal('show');
        });

        // تأیید و شروع نصب
        $('#confirm-install-btn').on('click', function () {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ارسال...');

            $.ajax({
                url: `/admin/packages/${SLUG}/install`,
                method: 'POST',
                data: { _token: csrfToken() },
                success: function (resp) {
                    if (resp.success && resp.redirect_url) {
                        window.location.href = resp.redirect_url;
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور.', confirmButtonText: 'بستن' });
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="feather icon-check"></i> <span id="confirm-btn-text">شروع نصب</span>');
                    $('#install-confirm-modal').modal('hide');
                }
            });
        });

        // آپدیت
        $('.btn-update').on('click', function (e) {
            e.preventDefault();
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ارسال...');

            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'ماژول به نسخه جدید آپدیت خواهد شد.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'بله، آپدیت کن',
                cancelButtonText: 'انصراف'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/packages/${SLUG}/update`,
                        method: 'POST',
                        data: { _token: csrfToken() },
                        success: function (resp) {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'موفق',
                                    text: resp.message,
                                    confirmButtonText: 'بستن'
                                });
                                startPolling();
                            } else {
                                Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور.', confirmButtonText: 'بستن' });
                        },
                        complete: function () {
                            $btn.prop('disabled', false).html('<i class="feather icon-arrow-up"></i> آپدیت');
                        }
                    });
                } else {
                    $btn.prop('disabled', false).html('<i class="feather icon-arrow-up"></i> آپدیت');
                }
            });
        });

        // فعال/غیرفعال
        $('.btn-toggle').on('click', function () {
            const $btn = $(this);
            $.ajax({
                url: `/admin/packages/${SLUG}/toggle`,
                method: 'POST',
                data: { _token: csrfToken() },
                success: function (resp) {
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: resp.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message, confirmButtonText: 'بستن' });
                }
            });
        });

        // حذف
        $('.btn-uninstall').on('click', function () {
            $('#uninstall-pkg-name').text($(this).data('name'));
        });

        $('#uninstall-form').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال حذف...');

            $.ajax({
                url: `/admin/packages/${SLUG}/uninstall`,
                method: 'POST',
                data: { _token: csrfToken() },
                success: function (resp) {
                    if (resp.success) {
                        $('#uninstall-modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: resp.message,
                            confirmButtonText: 'بازگشت به لیست'
                        }).then(function () {
                            window.location.href = '/admin/packages/installed';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message, confirmButtonText: 'بستن' });
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="feather icon-trash-2"></i> بله، حذف شود');
                }
            });
        });

        // شروع polling اگر در حال نصب است
        if (window.PACKAGE_INSTALLED) {
            startPolling();
        }

        // اگر از طریق ?update=1 آمده، اسکرول به پنل عملیات
        if (window.location.search.indexOf('update=1') !== -1) {
            $('html, body').animate({ scrollTop: $('#action-panel').offset().top - 80 }, 500);
        }
    });

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(pollStatus, 3000);
        pollStatus();
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function pollStatus() {
        $.ajax({
            url: `/admin/packages/${SLUG}/status`,
            method: 'GET',
            success: function (resp) {
                const inst = resp.installed;
                if (!inst) {
                    stopPolling();
                    return;
                }

                if (inst.status === 'updating') {
                    // هنوز در حال نصب - ادامه polling
                    return;
                }

                // نصب تمام شده (موفق یا ناموفق)
                stopPolling();

                if (inst.status === 'installed') {
                    Swal.fire({
                        icon: 'success',
                        title: 'نصب با موفقیت انجام شد!',
                        text: 'نسخه ' + inst.version,
                        confirmButtonText: 'بارگذاری مجدد'
                    }).then(function () { location.reload(); });
                } else if (inst.status === 'failed') {
                    Swal.fire({
                        icon: 'error',
                        title: 'نصب ناموفق بود',
                        text: inst.error || 'خطای ناشناخته',
                        confirmButtonText: 'بارگذاری مجدد'
                    }).then(function () { location.reload(); });
                }
            },
            error: function () {
                // در صورت خطا، polling ادامه پیدا می‌کند
            }
        });
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function number_format(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

})(jQuery);
