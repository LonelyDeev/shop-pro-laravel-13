$(document).ready(function() {
    // ================ افزودن دلیل جدید ================
    $('#reasonForm').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#submitBtn');
        const $titleError = $('#titleError');

        // پاک کردن خطاهای قبلی
        $titleError.text('');
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    // اضافه کردن ردیف جدید به جدول
                    $('#reasonsTableBody').prepend(response.html);

                    // پاک کردن فرم
                    $form[0].reset();
                    $('#is_active_reason').prop('checked', true);

                    // نمایش پیام موفقیت
                    showCustomToast(response.message,'success');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.title) {
                        $titleError.text(errors.title[0]);
                    }
                } else {
                    toastr.error('خطا در ثبت اطلاعات');
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('افزودن');
            }
        });
    });

    // ================ تغییر وضعیت (Toggle) ================
    $(document).on('click', '.toggle-reason', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const url = $btn.data('url');
        const $row = $('#reason-' + id);
        block($btn)
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // به‌روزرسانی وضعیت در جدول
                    const $badge = $row.find('.status-badge-' + id);
                    const isActive = response.is_active;
                    if (isActive) {
                        $badge.removeClass('badge-secondary').addClass('badge-success').text('فعال');
                        $btn.removeClass('btn-outline-secondary').addClass('btn-outline-success');
                        $btn.find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
                    } else {
                        $badge.removeClass('badge-success').addClass('badge-secondary').text('غیرفعال');
                        $btn.removeClass('btn-outline-success').addClass('btn-outline-secondary');
                        $btn.find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
                    }
                    unblock($btn)
                    showCustomToast(response.message,'success');
                }
            },
            error: function() {
                toastr.error('خطا در تغییر وضعیت');
            }
        });
    });

    // ================ حذف دلیل ================
    $(document).on('click', '.delete-reason', function() {
        if (!confirm('آیا از حذف این دلیل مطمئن هستید؟')) {
            return;
        }

        const $btn = $(this);
        const id = $btn.data('id');
        const url = $btn.data('url');
        const $row = $('#reason-' + id);

        $.ajax({
            url: url,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // حذف ردیف با انیمیشن
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                    showCustomToast(response.message,'success');
                }
            },
            error: function() {
                toastr.error('خطا در حذف دلیل');
            }
        });
    });
});
