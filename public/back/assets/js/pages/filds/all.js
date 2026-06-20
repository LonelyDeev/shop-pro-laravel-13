$('#tags').tagsInput({
    defaultText: 'افزودن',
    width: '100%',
});

if ($('#type').val() === 'select') {
    $('#select_options').removeClass('d-none');
} else {
    $('#select_options').addClass('d-none');
}

$('#type').change(function () {
    if ($(this).val() === 'select') {
        $('#select_options').removeClass('d-none');
    } else {
        $('#select_options').addClass('d-none');
    }
});


$('#fild-create-form').submit(function (e) {
    e.preventDefault();

    var form = $(this);


    var formData = new FormData(this);


    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            if (data == 'success') {
                $('#post-create-form').data('disabled', true);
                window.location.href = form.data('redirect');
            }

        },
        beforeSend: function (xhr) {
            block('#main-card');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {

            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });

});


$(document).on('click', '.btn-delete', function() {
    $('#fild-delete-form').attr('action', BASE_URL + '/filds/' + $(this).data('post'));
    $('#fild-delete-form').data('id', $(this).data('id'));
});

$('#fild-delete-form').submit(function(e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var form = this;

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            //get current url
            var url = window.location.href;

            //remove post tr
            $('#fild-' + $(form).data('id') + '-tr').remove();

            toastr.success('فیلد با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });

            //refresh posts list
            $(".app-content").load(url + " .app-content > *");
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


$('#filds-multiple-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#multiple-delete-modal').modal('hide');

    var formData = new FormData(this);
    var ids = $('.checkbox-single input[type="checkbox"]:checked').map(function() {
        return $(this).val();
    }).get();

    ids.forEach(function (id) {
        formData.append('ids[]', id);
    });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            ids.forEach(function(id) {
                $('#fild-' + id + '-tr').fadeOut(300, function() { $(this).remove(); });
            });

            toastr.success('فیلدهای انتخاب شده با موفقیت حذف شدند.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
        },
        beforeSend: function (xhr) {
            block('#main-card');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {
            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });
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

    // مدیریت انتخاب/حذف همه چک‌باکس‌ها با کلیک روی چک‌باکس "انتخاب همه"
    $('.checkbox-all input[type="checkbox"]').change(function() {
        var isChecked = $(this).prop('checked');
        $('.checkbox-single input[type="checkbox"]').prop('checked', isChecked).trigger('change'); // تریگر change برای اجرای کد بالا
    });


