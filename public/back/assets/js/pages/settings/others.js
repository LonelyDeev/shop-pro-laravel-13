$(document).ready(function() {

    $('#others-form').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                Swal.fire({
                    type: 'success',
                    title: 'تغییرات با موفقیت ذخیره شد',
                    confirmButtonClass: 'btn btn-primary',
                    confirmButtonText: 'باشه',
                    buttonsStyling: false,
                })
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
});

$('#user_referrals_gift_discount_type').on('change', function () {
    switch ($(this).val()) {
        case 'percent': {
            $('.discount_type_amount').addClass('d-none');
            $('.discount_type_percent').removeClass('d-none');
            break;
        }
        case 'amount': {
            $('.discount_type_amount').removeClass('d-none');
            $('.discount_type_percent').addClass('d-none');
            break;
        }
    }
});
$('#user_referrals_gift_discount_type').trigger('change');

$('#user_referrals_enable').on('change', function () {
    switch ($(this).val()) {
        case 'true': {
            $('#referrals_enable').removeClass('d-none');
            break;
        }
        case 'false': {
            $('#referrals_enable').addClass('d-none');
            break;
        }
    }
});
$('#user_referrals_enable').trigger('change');


function updateLabels() {
    var giftType = $('#user_referrals_gift_type').val();
    var discountType = $('#user_referrals_gift_discount_type').val();

    // مخفی کردن همه لیبل‌ها
    $('.discount-label').addClass('d-none');

    // تعیین نوع متن بر اساس شرایط
    var textType = '';
    if (giftType === 'wallet') {
        textType = 'amount-wallet';
        $('#conditions_gift').addClass('d-none');
    } else if (giftType === 'discount_code' && discountType === 'percent') {
        textType = 'percent';
    } else if (giftType === 'discount_code' && discountType === 'amount') {
        textType = 'amount-discount';
    }else {
        $('#conditions_gift').removeClass('d-none');
    }

    // نمایش متن مناسب
    $('.discount-label').each(function() {
        if (textType) {
            $(this).text($(this).data(textType));
            $(this).removeClass('d-none');
        }
    });
}

// رویدادها
$('#user_referrals_gift_type, #user_referrals_gift_discount_type').on('change', function() {
    // غیرفعال کردن گزینه درصد در صورت انتخاب کیف پول
    if ($('#user_referrals_gift_type').val() === 'wallet') {
        $('#user_referrals_gift_discount_type option[value="percent"]').prop('disabled', true);
        $('#user_referrals_gift_discount_type').val('amount');
        $('#conditions_gift').addClass('d-none');

    } else {
        $('#user_referrals_gift_discount_type option[value="percent"]').prop('disabled', false);
        $('#conditions_gift').removeClass('d-none');
    }

    updateLabels();
});

// راه‌اندازی اولیه
updateLabels();


