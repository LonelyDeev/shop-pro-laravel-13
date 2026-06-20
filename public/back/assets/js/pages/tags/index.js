$(document).ready(function() {
    // انتخاب همه
    $(document).on('click', '.btn-delete', function() {
        $('#story-delete-form').attr('action', $(this).data('action'));
        $('#story-delete-form').data('id', $(this).data('id'));
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

    $('#delete-form').on('submit', function(e) {
        e.preventDefault();

        $('#delete-modal').modal('hide');
        var form = this;
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data == 'success') {
                    toastr.success('تگ با موفقیت حذف شد.', null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });

                    $('#row-' + $(form).data('id')).remove();
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

    $('#multiple-delete-form').on('submit', function(e) {
        e.preventDefault();

        $('#multiple-delete-modal').modal('hide');

        var ids = $('.checkbox-single input[type="checkbox"]:checked').map(function() {
            return $(this).val();
        }).get();
        console.log(ids);
        console.log($(this).attr('action'));
        $.ajax({
            url: $(this).attr('action'),
            type: 'post',
            data: {ids: ids},
            success: function(data) {
                if (data == 'success') {
                    toastr.success('تگ های انتخاب شده با موفقیت حذف شدند.', null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });
                    const selector = ids.map(id => '#row-' + id).join(',');
                    $(selector).remove();
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

    // نمایش جزئیات
    $('.btn-show-details').on('click', function() {
        var action = $(this).data('action');

        $('#detailsModal').modal('show');
        $('#details-container').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">در حال بارگذاری...</p></div>');

        $.ajax({
            url: action,
            type: 'GET',
            success: function(response) {
                $('#details-container').html(response.html);
            },
            error: function() {
                $('#details-container').html('<div class="alert alert-danger">خطا در دریافت اطلاعات</div>');
            }
        });
    });

    // ویرایش تگ
    $('.btn-edit-tag').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var action = $(this).data('action');

        $('#edit-tag-name').val(name);
        $('#edit-tag-form').attr('action', action);
        $('#editTagModal').modal('show');
    });

    // ایجاد تگ با AJAX
    $('#create-tag-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('موفق', 'تگ با موفقیت ایجاد شد', 'success').then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                var errorMsg = 'خطا در ایجاد تگ';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                Swal.fire('خطا', errorMsg, 'error');
            }
        });
    });

    // ویرایش تگ با AJAX
    $('#edit-tag-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('موفق', 'تگ با موفقیت به‌روزرسانی شد', 'success').then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                var errorMsg = 'خطا در به‌روزرسانی تگ';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                Swal.fire('خطا', errorMsg, 'error');
            }
        });
    });

    // حذف تکی
    $('.btn-delete').on('click', function() {
        var action = $(this).data('action');
        $('#delete-form').attr('action', action);
    });


});
