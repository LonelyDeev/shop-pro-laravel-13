$(document).ready(function() {
    // انتخاب همه
    $(document).on('click', '.btn-delete', function() {
        $('#delete-form').attr('action', $(this).data('action'));
        $('#delete-form').data('id', $(this).data('id'));
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

                    showCustomToast('استوری با موفقیت حذف شد','success');
                    $('.datatable-actions').collapse('hide');
                    $('#datatable-selected-rows').text(0);
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


        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: {ids: ids},
            success: function(data) {
                if (data == 'success') {

                    showCustomToast('موارد انتخاب شده با موفقیت حذف شدند','success');
                    const selector = ids.map(id => '#row-' + id).join(',');
                    $(selector).remove();
                    $('.datatable-actions').collapse('hide');
                    $('#datatable-selected-rows').text(0);
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
        var id = $(this).data('id');
        var action = $(this).data('action');

        $('#submissionDetailsModal').modal('show');
        $('#submission-details-container').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">در حال بارگذاری...</p></div>');

        $.ajax({
            url: action,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.html) {
                    $('#submission-details-container').html(response.html);
                } else {
                    $('#submission-details-container').html('<div class="alert alert-danger">خطا در دریافت اطلاعات</div>');
                }
            },
            error: function() {
                $('#submission-details-container').html('<div class="alert alert-danger">خطا در ارتباط با سرور</div>');
            }
        });
    });

});
