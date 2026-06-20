$(document).ready(function() {

    // ========== نمایش جزئیات در مودال ==========
    $('.btn-show-details').on('click', function(e) {
        e.stopPropagation();
        block('#search-details-container')
        var keyword = $(this).data('keyword');
        var type = $(this).data('type');
        var url=$(this).data('action');

        // تنظیم مقادیر در مودال
        $('#modal-keyword').text(keyword);
        $('#modal-type').text(type == 'products' ? 'محصولات' : 'پست‌ها');

        // نمایش مودال
        $('#searchDetailsModal').modal('show');

        // درخواست Ajax برای دریافت جزئیات
        $.ajax({
            url: url,
            type: 'post',
            data: {
                keyword: keyword,
                type: type
            },
            success: function(response) {
                if (response.success) {
                    $('#search-details-container').html(response.html);
                } else {
                    $('#search-details-container').html('<div class="alert alert-danger">خطا در دریافت اطلاعات</div>');
                }
                unblock('#search-details-container');
            },
            error: function() {
                $('#search-details-container').html('<div class="alert alert-danger">خطا در ارتباط با سرور</div>');
                unblock('#search-details-container');
            }
        });
    });

    // با کلیک روی ردیف جدول
    $('.search-row').on('click', function(e) {
        // اگر روی چک‌باکس یا دکمه کلیک نشده بود
        if (!$(e.target).is('input[type="checkbox"]') && !$(e.target).is('.btn-show-details') && !$(e.target).is('button')) {
            var keyword = $(this).data('keyword');
            var type = $(this).data('type');
            $('.btn-show-details[data-keyword="' + keyword + '"][data-type="' + type + '"]').click();
        }
    });

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

    $('#story-delete-form').on('submit', function(e) {
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
                    toastr.success('استوری با موفقیت حذف شد.', null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });

                    $('#post-' + $(form).data('id') + '-tr').remove();
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

    $('#story-multiple-delete-form').on('submit', function(e) {
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
                    toastr.success('موردهای انتخاب شده با موفقیت حذف شدند', null, {
                        positionClass: 'toast-bottom-left',
                        containerId: 'toast-bottom-left'
                    });
                    const selector = ids.map(id => '#row-' + id).join(',');
                    console.log(selector)
                    $(selector).remove();

                }
                unblock('#main-card');
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

});
