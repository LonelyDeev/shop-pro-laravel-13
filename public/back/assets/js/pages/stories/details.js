$(document).ready(function() {
    $(document).on('click', '.delete-comment', function() {
        $('#comment-delete-form').attr('action', $(this).data('action'));
        $('#comment-delete-form').data('id', $(this).data('id'));
    });

    $('.checkbox-all input[type="checkbox"]').change(function() {
        var isChecked = $(this).prop('checked');
        $('.checkbox-single input[type="checkbox"]').prop('checked', isChecked);

        var selectedCount = $('.checkbox-single input[type="checkbox"]:checked').length;
        if (selectedCount > 0) {
            $('.datatable-actions').collapse('show');
            $('#datatable-selected-rows').text(selectedCount);
        } else {
            $('.datatable-actions').collapse('hide');
            $('#datatable-selected-rows').text(0);
        }
    });

    $('.checkbox-single input[type="checkbox"]').change(function() {
        var totalCheckboxes = $('.checkbox-single input[type="checkbox"]').length; // تعداد کل چک‌باکس‌ها
        var selectedCount = $('.checkbox-single input[type="checkbox"]:checked').length; // تعداد انتخاب‌شده‌ها

        // نمایش یا مخفی کردن .datatable-actions
        if (selectedCount > 0) {
            $('.datatable-actions').collapse('show');
            $('#datatable-selected-rows').text(selectedCount);
        } else {
            $('.datatable-actions').collapse('hide');
            $('#datatable-selected-rows').text(0);
        }

        // بررسی انتخاب شدن تمام چک‌باکس‌ها
        if (selectedCount === totalCheckboxes) {
            $('.checkbox-all input[type="checkbox"]').prop('checked', true);
        } else {
            $('.checkbox-all input[type="checkbox"]').prop('checked', false);
        }
    });

    $('#comment-delete-form').on('submit', function(e) {
        e.preventDefault();

        $('#delete-modal').modal('hide');
        var form = this;
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data.success) {
                    toastr.success('دیدگاه با موفقیت حذف شد.', null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });

                    $('#comment-' + $(form).data('id')).remove();
                }
            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function() {
                unblock('#main-card');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    $('#story-multiple-operation-form').on('submit', function(e) {
        e.preventDefault();

        $('#multiple-operation-modal').modal('hide');

        var ids = $('.checkbox-single input[type="checkbox"]:checked').map(function() {
            return $(this).val();
        }).get();

        var comment_status=$('select[name=comment_status]').val()

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: {ids: ids,comment_status:comment_status},
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });

                    const selector = ids.map(id => '#comment-' + id).join(',');

                    if (response.status === "deleted") {
                        $(selector).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else if (response.status === "approved") {
                        $(selector).each(function() {
                            const $row = $(this);
                            const commentId = $row.data('comment-id');

                            // تغییر وضعیت
                            $row.find('.badge').removeClass('badge-warning badge-danger').addClass('badge-success').text('تایید شده');

                            // نمایش/مخفی کردن دکمه‌ها
                            $row.find('.approve-comment').hide();
                            $row.find('.reject-comment').show();
                        });
                    } else if (response.status === "rejected") {
                        $(selector).each(function() {
                            const $row = $(this);
                            const commentId = $row.data('comment-id');

                            // تغییر وضعیت
                            $row.find('.badge').removeClass('badge-warning badge-success').addClass('badge-danger').text('رد شده');

                            // نمایش/مخفی کردن دکمه‌ها
                            $row.find('.reject-comment').hide();
                            $row.find('.approve-comment').show();
                        });
                    }
                }

            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function() {
                unblock('#main-card');
            }
        });
    });


    // تایید کامنت
    $('.approve-comment').on('click', function() {
        changeStatusSoloComment($(this),'approved')
    });

    // رد کامنت
    $('.reject-comment').on('click', function() {
        changeStatusSoloComment($(this),'rejected')
    });


    function changeStatusSoloComment(btn,status) {
        const $blockRow = btn.parents('tr');
        block($blockRow)
        $.ajax({
            url: btn.data('action'),
            type: 'POST',
            data: {status:status},
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });

                    const rowId = btn.data('id');
                    const $row = $('#comment-' + rowId);

                    if (status === "approved") {
                        // تغییر وضعیت
                        $row.find('.badge').removeClass('badge-warning badge-danger').addClass('badge-success').text('تایید شده');

                        // تغییر دکمه‌ها
                        $row.find('.approve-comment').hide();
                        $row.find('.reject-comment').show();
                        if ($row.find('.reject-comment').length === 0) {
                            $row.find('.btn-group').prepend(`<button class="btn btn-warning reject-comment" data-id="${rowId}"><i class="fa fa-times"></i> رد</button>`);
                        }

                        // انیمیشن
                        $row.addClass('bg-success-light').delay(500).queue(function(next) {
                            $(this).removeClass('bg-success-light');
                            next();
                        });

                    } else if (status === "rejected") {
                        // تغییر وضعیت
                        $row.find('.badge').removeClass('badge-warning badge-success').addClass('badge-danger').text('رد شده');

                        // تغییر دکمه‌ها
                        $row.find('.reject-comment').hide();
                        $row.find('.approve-comment').show();
                        if ($row.find('.approve-comment').length === 0) {
                            $row.find('.btn-group').prepend(`<button class="btn btn-success approve-comment" data-id="${rowId}"><i class="fa fa-check"></i> تایید</button>`);
                        }

                        // انیمیشن
                        $row.addClass('bg-danger-light').delay(500).queue(function(next) {
                            $(this).removeClass('bg-danger-light');
                            next();
                        });

                    }
                }

            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function(xhr) {
                unblock($blockRow);
            },
            cache: false,
        });
    }
});
