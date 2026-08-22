$(document).ready(function() {
    // ========== اعتبارسنجی فرم ==========
    $('#warehouse-create-form').validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 255
            },
            type: {
                required: true
            },
            seller_id: {
                required: function() {
                    return $('#type').val() === 'seller';
                }
            },
            phone: {
                minlength: 6,
                maxlength: 20
            },
            address: {
                maxlength: 500
            }
        },
        messages: {
            name: {
                required: 'لطفا نام انبار را وارد کنید',
                minlength: 'نام انبار حداقل 3 کاراکتر باید باشد',
                maxlength: 'نام انبار حداکثر 255 کاراکتر می‌تواند باشد'
            },
            type: {
                required: 'لطفا نوع انبار را انتخاب کنید'
            },
            seller_id: {
                required: 'لطفا فروشنده را انتخاب کنید'
            }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });

    // ========== مدیریت نوع انبار ==========
    function toggleSellerField() {
        var type = $('#type').val();

        if (type === 'seller') {
            $('#seller-select-container').slideDown();
            $('#seller_id').prop('required', true);
            $('#temp-description').slideUp();
        } else if (type === 'temp') {
            $('#seller-select-container').slideUp();
            $('#seller_id').prop('required', false);
            $('#temp-description').slideDown();
        } else {
            $('#seller-select-container').slideUp();
            $('#seller_id').prop('required', false);
            $('#temp-description').slideUp();
        }

        // اعتبارسنجی مجدد
        $('#warehouse-create-form').validate().element('#seller_id');
    }

    $('#type').on('change', toggleSellerField);
    toggleSellerField();


    // ========== ارسال فرم با AJAX ==========
    $('#warehouse-create-form').submit(function(e) {
        e.preventDefault();

        if ($(this).valid() && !$(this).data('disabled')) {
            var formData = new FormData(this);
            var $form = $(this);
            var redirectUrl = $form.data('redirect');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    $form.data('disabled', true);
                    if (response.success) {
                        showCustomToast(response.message ?? 'انبار با موفقیت ایجاد شد','success')
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 1000);
                    } else {
                        showCustomToast(response.message,'error');
                        $form.data('disabled', false);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            showCustomToast(value[0]+ ' خطای اعتبارسنجی','error');
                        });
                    } else {
                        showCustomToast('خطا در ایجاد انبار','error');
                    }
                    $form.data('disabled', false);
                },
                beforeSend: function(xhr) {
                    block('.content-body');
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                complete: function() {
                    //unblock('.content-body');
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
    });
});
