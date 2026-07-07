// validate form with jquery validation plugin
jQuery('#messages-create-form').validate({
    rules: {
        'title': {
            required: true,
        },
        'description': {
            required: true,
        },
    },
});



$('#messages-create-form').submit(function(e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data.status=="error"){
                    toastr.error(data.message);
                }
                if (data=="success"){
                    window.location.href = BASE_URL + "/messages";
                }

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
    }

});
$('.users').select2ToTree({
    rtl: true,
    width: '100%'
});

$('input[name=sms]').click(function () {
    if ($('input[name=sms]').is(':checked')) {
        $('#pattern-code-div').removeClass('d-none')
    }else {
        $('#pattern-code-div').addClass('d-none')
    }
});


$('.remove-variable-item').click(function() {
    $(this).parents('.variable-item').remove();
})

$(document).on('click', '.btn-delete', function () {
    $('#messages-delete-form').attr('action', $(this).data('action'));
    $('#messages-delete-form').data('id', $(this).data('review'));
});

$('#messages-delete-form').submit(function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            //remove review tr
            $(
                '#review-' + $('#messages-delete-form').data('id') + '-tr'
            ).remove();

            toastr.success('پیام با موفقیت حذف شد.');

            reloadDiv('.list-reviews');
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
$(function() {
    // -------- مدیریت نمایش بخش پترن بر اساس تیک پیامک --------
    var $smsCheckbox = $('input[name="sms"]');
    var $patternBox = $('#pattern-code-div');

    function togglePatternBox() {
        if ($smsCheckbox.is(':checked')) {
            $patternBox.removeClass('d-none');
        } else {
            $patternBox.addClass('d-none');
        }
    }

    // اجرا در بارگذاری اولیه
    togglePatternBox();

    // اتصال به تغییر وضعیت چک‌باکس
    $smsCheckbox.on('change', togglePatternBox);

    // -------- مدیریت افزودن و حذف متغیرها --------
    var $variablesContainer = $('#variables');
    var $template = $('.variable-template');
    var $emptyAlert = $('#vars-empty'); // اگر این آی‌دی وجود ندارد، آن را به alert مورد نظر اضافه کنید

    // در صورتی که alert دارای id="vars-empty" نیست، می‌توانید با کلاس یا محتوا آن را انتخاب کنید:
    // var $emptyAlert = $('.msg-alert:contains("متغیرهای موجود را حذف کنید")');

    function updateEmptyState() {
        var $visibleRows = $variablesContainer.find('.variable-item:not(.d-none)');
        if ($visibleRows.length === 0) {
            $emptyAlert.removeClass('d-none');
        } else {
            $emptyAlert.addClass('d-none');
        }
    }

    // افزودن متغیر جدید
    $(document).on('click', '.add-variable-item', function(e) {
        e.preventDefault();

        // clone از قالب
        var $newRow = $template.clone();
        $newRow.removeClass('d-none variable-template');
        // خالی کردن مقادیر ورودی‌ها
        $newRow.find('input').val('');
        // افزودن به انتهای ظرف
        $variablesContainer.append($newRow);
        // به‌روزرسانی وضعیت خالی بودن
        updateEmptyState();
    });

    // حذف متغیر
    $(document).on('click', '.remove-variable-item', function(e) {
        e.preventDefault();
        var $row = $(this).closest('.variable-item');
        // اگر ردیف قالب نباشد (یعنی قابل حذف باشد)
        if (!$row.hasClass('variable-template')) {
            $row.remove();
            updateEmptyState();
        }
    });

    // وضعیت اولیه پس از بارگذاری
    updateEmptyState();
});
