// back/assets/js/pages/comments/index.js

$(document).ready(function() {

    // ========== حذف کامنت اصلی ==========
    $(document).on('click', '.btn-delete', function() {
        $('#comment-delete-form').attr('action', $(this).data('action'));
        $('#comment-delete-form').data('id', $(this).data('comment'));
    });

    $('#comment-delete-form').submit(function(e) {
        e.preventDefault();
        $('#delete-modal').modal('hide');

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#comment-' + $('#comment-delete-form').data('id') + '-tr').remove();
                showCustomToast('دیدگاه با موفقیت حذف شد','success');
                reloadDiv('.list-comments');
            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function() {
                unblock('#main-card');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    // ========== نمایش جزئیات کامنت ==========
    $(document).on('click', '.show-comment', function() {
        $.ajax({
            url: BASE_URL + '/comments/' + $(this).data('comment'),
            type: 'GET',
            success: function(data) {
                $('#comment-detail').empty();
                $('#comment-detail').append(data);
                $('#show-modal').modal('show');

                // بعد از بارگذاری محتوا، رویدادها را متصل کن (قبل از اتصال، جدا کن)
                bindCommentDetailEvents();
            },
            beforeSend: function(xhr) {
                block('#main-card');
            },
            complete: function() {
                unblock('#main-card');
            }
        });
    });

    // ========== فیلتر کردن ==========
    $('#filter-comments-form select').change(function() {
        $('#filter-comments-form').submit();
    });

    // ========== ویرایش کامنت اصلی ==========
    $(document).on('submit', '#comment-edit-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                reloadDiv('.list-comments');
                $('#show-modal').modal('hide');
                showCustomToast('تغییرات با موفقیت انجام شد','success');
            },
            beforeSend: function(xhr) {
                block('.comment-show-modal');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function() {
                unblock('.comment-show-modal');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    $(document).on('click', '#edit-comment-btn', function() {
        $('#edit-comment-body').show();
        $('#comment-body').hide();
        autosize(document.querySelectorAll('textarea'));
    });

    $(document).on('click', '#comment-form-submit-btn', function() {
        $('#comment-edit-form').trigger('submit');
    });

    // ==================== رویدادهای مربوط به پاسخ‌ها ====================

    function bindCommentDetailEvents() {

        // IMPORTANT: قبل از اتصال مجدد، رویدادهای قبلی را حذف کن
        $(document).off('click', '#submit-reply-btn');
        $(document).off('click', '.edit-reply-btn');
        $(document).off('click', '.save-reply-edit');
        $(document).off('click', '.cancel-reply-edit');
        $(document).off('click', '.delete-reply-btn');

        // ---------- ارسال پاسخ جدید ----------
        $(document).on('click', '#submit-reply-btn', function() {
            let button = $(this);
            let commentId = button.data('comment-id');
            let actionUrl = button.data('action');
            let reply = $('#reply-textarea').val();

            if (!reply || !reply.trim()) {
                showCustomToast('لطفا متن پاسخ را وارد کنید','error');
                return false;
            }

            // جلوگیری از ارسال همزمان چند بار
            if (button.data('processing') === true) {
                return false;
            }

            button.data('processing', true);
            button.prop('disabled', true).html('<i class="feather icon-loader fa-spin"></i> در حال ارسال...');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {
                    reply: reply,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showCustomToast('پاسخ با موفقیت ثبت شد','success');
                        // پاک کردن textarea
                        $('#reply-textarea').val('');
                        // بستن مودال و ریلود لیست
                        setTimeout(function() {
                            $('#show-modal').modal('hide');
                            reloadDiv('.list-comments');
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'خطا در ثبت پاسخ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showCustomToast(errorMsg,'error');
                },
                complete: function() {
                    button.data('processing', false);
                    button.prop('disabled', false).html('<i class="feather icon-send"></i> ارسال پاسخ');
                }
            });
        });

        // ---------- ویرایش پاسخ ----------
        $(document).on('click', '.edit-reply-btn', function(e) {
            e.preventDefault();

            let replyId = $(this).data('reply-id');
            let replyBody = $(this).data('reply-body');

            // مخفی کردن متن پاسخ
            $(`#reply-body-${replyId}`).hide();

            // قرار دادن متن در textarea
            $(`#reply-edit-textarea-${replyId}`).val(replyBody);

            // نمایش فرم ویرایش
            $(`#edit-reply-form-${replyId}`).show();
        });

        // ---------- ذخیره ویرایش پاسخ ----------
        $(document).on('click', '.save-reply-edit', function(e) {
            e.preventDefault();

            let button = $(this);
            let replyId = button.data('reply-id');
            let actionUrl = button.data('action');
            let newBody = $(`#reply-edit-textarea-${replyId}`).val();

            if (!newBody || !newBody.trim()) {
                showCustomToast('لطفا متن پاسخ را وارد کنید','error');
                return false;
            }

            if (button.data('processing') === true) {
                return false;
            }

            button.data('processing', true);
            button.prop('disabled', true).html('<i class="feather icon-loader fa-spin"></i>');

            $.ajax({
                url: actionUrl,
                type: 'PUT',
                data: {
                    body: newBody,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showCustomToast('پاسخ با موفقیت ویرایش شد','success');
                        setTimeout(function() {
                            $('#show-modal').modal('hide');
                            reloadDiv('.list-comments');
                        }, 800);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'خطا در ویرایش پاسخ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showCustomToast(errorMsg,'error');
                },
                complete: function() {
                    button.data('processing', false);
                    button.prop('disabled', false).html('<i class="feather icon-check"></i> ذخیره');
                }
            });
        });

        // ---------- انصراف از ویرایش پاسخ ----------
        $(document).on('click', '.cancel-reply-edit', function(e) {
            e.preventDefault();

            let replyId = $(this).data('reply-id');

            // نمایش متن اصلی
            $(`#reply-body-${replyId}`).show();

            // مخفی کردن فرم ویرایش
            $(`#edit-reply-form-${replyId}`).hide();
        });

        // ---------- حذف پاسخ با تایید ----------
        $(document).on('click', '.delete-reply-btn', function(e) {
            e.preventDefault();

            let button = $(this);
            let replyId = button.data('reply-id');
            let actionUrl = button.data('action');

            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'با حذف این پاسخ، دیگر قادر به بازیابی آن نخواهید بود!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.value) {
                    block('#comment-detail')
                    $.ajax({
                        url: actionUrl,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                showCustomToast('پاسخ با موفقیت حذف شد','success');
                                setTimeout(function() {
                                   $('#reply-card-'+replyId).remove()
                                    reloadDiv('.list-comments');
                                }, 500);
                                unblock('#comment-detail')
                            }
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                        },
                        error: function(xhr) {
                            let errorMsg = 'خطا در حذف پاسخ';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showCustomToast(errorMsg,'error');
                        },
                        complete: function(xhr) {
                            unblock('#comment-detail')
                        }
                    });
                }
            });
        });
    }

    $(document).on('change', '.reply-status-select', function() {
        let select = $(this);
        let replyId = select.data('reply-id');
        let actionUrl = select.data('action');
        let newStatus = select.val();
        let oldStatus = select.find('option:selected').data('old-status');

        // نمایش لودینگ
        select.prop('disabled', true);

        $.ajax({
            url: actionUrl,
            type: 'PUT',
            data: {
                status: newStatus,
            },
            success: function(response) {
                if (response.success) {
                    showCustomToast('وضعیت پاسخ با موفقیت تغییر کرد','success');
                    // بروزرسانی Badge وضعیت
                    let statusBadge = $(`#reply-card-${replyId} .badge`);
                    if (newStatus === 'pending') {
                        statusBadge.removeClass('badge-success badge-danger').addClass('badge-warning');
                        statusBadge.html('⏳ منتظر تایید');
                    } else if (newStatus === 'accepted') {
                        statusBadge.removeClass('badge-warning badge-danger').addClass('badge-success');
                        statusBadge.html('✓ تایید شده');
                    } else {
                        statusBadge.removeClass('badge-success badge-warning').addClass('badge-danger');
                        statusBadge.html('✗ تایید نشده');
                    }
                }
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            error: function(xhr) {
                let errorMsg = 'خطا در تغییر وضعیت';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showCustomToast(errorMsg,'error');

                // برگرداندن مقدار قبلی
                select.val(oldStatus);
            },
            complete: function() {
                select.prop('disabled', false);
            }
        });
    });

});
