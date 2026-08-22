$(document).ready(function() {

    // ========== حذف یک نشست (خروج اجباری) ==========
    $(document).on('click', '.delete-session', function() {
        let button = $(this);
        let sessionId = button.data('session-id');
        let adminName = button.data('admin-name');

        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: `ادمین ${adminName} از دستگاه خود خارج خواهد شد.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، خارج شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.value) {
                block('#main-card');

                $.ajax({
                    url: button.data('action'),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showCustomToast(response.message,'success');
                            $(`#session-${sessionId}`).fadeOut(300, function() {
                                $(this).remove();
                            });

                            // بروزرسانی تعداد
                            updateSessionCount();
                        } else {
                            showCustomToast(response.message,'error');
                        }
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    error: function(xhr) {
                        let errorMsg = 'خطا در حذف نشست';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showCustomToast(errorMsg,'error');
                    },
                    complete: function() {
                        unblock('#main-card');
                    }
                });
            }
        });
    });

    // ========== حذف تمام نشست‌های یک ادمین ==========
    $(document).on('click', '.delete-all-admin-sessions', function() {
        let button = $(this);
        let adminId = button.data('admin-id');
        let adminName = button.data('admin-name');

        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: `تمامی نشست‌های فعال ادمین "${adminName}" به جز جلسه فعلی (اگر خودتان هستید) حذف خواهد شد.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.value) {
                block('#main-card');

                $.ajax({
                    url: button.data('action'),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showCustomToast(response.message,'success');

                            // حذف ردیف‌های مربوط به این ادمین
                            $(`tr[id^="session-"]`).each(function() {
                                let sessionRow = $(this);
                                let sessionAdminId = sessionRow.find('.delete-all-admin-sessions').data('admin-id');
                                if (sessionAdminId == adminId) {
                                    let sessionIdText = sessionRow.find('.delete-session').data('session-id');
                                    if (sessionIdText) {
                                        sessionRow.fadeOut(300, function() {
                                            $(this).remove();
                                        });
                                    }
                                }
                            });

                            updateSessionCount();
                        }
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    error: function(xhr) {
                        showCustomToast('خطا در حذف نشست‌ها','error');
                    },
                    complete: function() {
                        unblock('#main-card');
                    }
                });
            }
        });
    });

    // ========== خروج از سایر دستگاه‌های ادمین فعلی ==========
    $('#logout-other-devices-btn').on('click', function() {
        let button = $(this);

        Swal.fire({
            title: 'خروج از سایر دستگاه‌ها',
            text: 'آیا از خارج شدن از تمام دستگاه‌های دیگر (به غیر از دستگاه فعلی) اطمینان دارید؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، خارج شوند',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.value) {
                button.prop('disabled', true).html('<i class="feather icon-loader fa-spin"></i> در حال اجرا...');
                block('#main-card');

                $.ajax({
                    url: button.data('action'),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            showCustomToast(response.message,'success');

                            // حذف ردیف‌های نشست‌های دیگر (غیر از جلسه فعلی)
                            $('tr[id^="session-"]').each(function() {
                                let sessionRow = $(this);
                                let statusBadge = sessionRow.find('.badge');
                                if (!statusBadge.hasClass('badge-success')) {
                                    sessionRow.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                }
                            });

                            updateSessionCount();
                        }
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    error: function(xhr) {
                        showCustomToast('خطا در خروج از دستگاه‌ها','error');
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="feather icon-log-out"></i> خروج از سایر دستگاه‌ها');
                        unblock('#main-card');
                    }
                });
            }
        });
    });

    // ========== پاکسازی نشست‌های غیرفعال ==========
    $('#clear-inactive-btn').on('click', function() {
        let button = $(this);

        Swal.fire({
            title: 'پاکسازی نشست‌های غیرفعال',
            text: 'آیا از حذف نشست‌هایی که بیش از 30 روز فعال نبوده‌اند اطمینان دارید؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، پاکسازی شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.value) {
                button.prop('disabled', true).html('<i class="feather icon-loader fa-spin"></i> در حال پاکسازی...');
                block('#main-card');

                $.ajax({
                    url: button.data('action'),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            showCustomToast(response.message,'success');
                            if (response.deleted_count > 0) {
                                setTimeout(() => location.reload(), 1500);
                            }
                        }
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    error: function(xhr) {
                        showCustomToast('خطا در پاکسازی نشست‌ها','error');
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="feather icon-trash-2"></i> پاکسازی غیرفعال');
                        unblock('#main-card');
                    }
                });
            }
        });
    });

    // ========== بروزرسانی تعداد نشست‌ها ==========
    function updateSessionCount() {
        let remainingRows = $('tbody tr:visible').length;
        $('.text-muted.small').html(`تعداد کل نشست‌های فعال: ${remainingRows}`);

        if (remainingRows === 0) {
            location.reload();
        }
    }



    // ========== بلاک دستگاه ==========
    $(document).on('click', '.block-session', function() {
        let button = $(this);
        let sessionId = button.data('session-id');
        let sessionIp = button.data('session-ip');
        let adminName = button.data('admin-name');

        $('#block-form').attr('action',button.data('action'));
        $('#block-session-id').val(sessionId);
        $('#block-type option[value="session"]').text(`فقط این دستگاه (${adminName})`);
        $('#block-type option[value="ip"]').text(`فقط این آیپی (${sessionIp})`);

        $('#block-modal').modal('show');
    });

    $('#confirm-block-btn').on('click', function() {
        let sessionId = $('#block-session-id').val();
        let blockType = $('#block-type').val();
        let duration = $('#block-duration').val();
        let reason = $('#reason').val();
        let button = $(this);
        button.prop('disabled', true).html('<i class="feather icon-loader fa-spin"></i> در حال بلاک...');

        $.ajax({
            url:  $('#block-form').attr('action'),
            type: 'POST',
            data: {
                block_type: blockType,
                duration: duration,
                reason: reason,
            },
            success: function(response) {
                if (response.success) {
                    showCustomToast(response.message,'success');
                    $('#block-form')[0].reset();
                    $('#block-modal').modal('hide');

                    // حذف ردیف نشست
                    $(`#session-${sessionId}`).fadeOut(300, function() {
                        $(this).remove();
                    });

                    updateSessionCount();
                }
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            error: function(xhr) {
                let errorMsg = 'خطا در بلاک دستگاه';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showCustomToast(errorMsg,'error');
            },
            complete: function() {
                button.prop('disabled', false).html('بلاک شود');
            }
        });
    });


});
