$(document).ready(function () {
    initAttributeSelects()
    function initAttributeSelects() {
        $('.modal').find('.price-attribute-select').each(function() {
            var $select = $(this);

            // اگر قبلاً select2ToTree روی این عنصر اعمال شده، آن را destroy کنید
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            var groupType = $select.data('group-type');

            $select.select2ToTree({
                rtl: true,
                width: '100%',
                templateResult: function(option) {
                    if (!option.element) return option.text;

                    var $element = $(option.element);
                    var isColor = ($element.data('is-color') === 'true') || (groupType === 'color');
                    var colorCode = $element.data('color');

                    if (isColor && colorCode) {
                        var $span = $('<span style="display: flex; align-items: center; gap: 8px;"><span style="display: inline-block; width: 20px; height: 20px; background-color: ' + colorCode + '; border-radius: 3px; border: 1px solid #ccc;"></span> ' + option.text + '</span>');
                        return $span;
                    }
                    return option.text;
                },
                templateSelection: function(option) {
                    if (!option.element) return option.text;

                    var $element = $(option.element);
                    var isColor = ($element.data('is-color') === 'true') || (groupType === 'color');
                    var colorCode = $element.data('color');

                    if (isColor && colorCode) {
                        return $('<span style="display: flex; align-items: center; gap: 5px;"><span style="display: inline-block; width: 16px; height: 16px; background-color: ' + colorCode + '; border-radius: 2px; border: 1px solid #ccc;"></span> ' + option.text + '</span>');
                    }
                    return option.text;
                }
            });
        });
    }


    $('.persian-date-picker').customPersianDate();

    $(document).on(
        'keyup',
        '.single-price .price, .single-price .discount',
        function () {


            let discount = $(this).closest('.single-price').find('.discount').val();

            let price = $(this).closest('.single-price').find('.price').val();

            price = price ? parseFloat(price) : 0;
            discount = discount ? parseFloat(discount) : 0;

            let finalPrice = (price - price * (discount / 100)) ;
            finalPrice = +finalPrice.toFixed(2);

            let finalPriceText = number_format(finalPrice) + ' تومان';

            $(this)
                .closest('.single-price')
                .find('.final-price')
                .val(finalPriceText);
        }
    );
    // ============================================================
    // کلیک روی دکمه ویرایش → لود داده با AJAX
    // ============================================================
    $(document).on('click', '.btn-edit-variation', function () {
        const action = $(this).data('action');
        const priceDataAPI = $(this).data('price-data');
        const priceId = $(this).data('price-id');
        $('#edit_price_id').val(priceId);

        // ریست فرم
        $('#editLoadingSpinner').show();
        $('#editFormContent').hide();
        $('#editErrorAlert').addClass('d-none').text('');

        // تنظیم action فرم
        $('#editVariationForm').attr('action',action);

        // لود داده از سرور
        $.get(priceDataAPI)
            .done(function (data) {
                $('#editFormContent').html(data.html)
                initAttributeSelects();
                $('.persian-date-picker').customPersianDate();
                $('#editLoadingSpinner').hide();
                $('#editFormContent').show();
            })
            .fail(function () {
                $('#editLoadingSpinner').hide();
                $('#editErrorAlert')
                    .removeClass('d-none')
                    .text('خطا در بارگذاری اطلاعات. لطفاً دوباره تلاش کنید.');
                $('#editFormContent').show();
            });
    });

    // ============================================================
    // ارسال فرم ویرایش با AJAX
    // ============================================================
    $('#editVariationForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#editSubmitBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ذخیره...');
        $('#editErrorAlert').addClass('d-none');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    // نمایش پیام موفق
                    toastr.success(response.message ?? 'تنوع با موفقیت ویرایش شد.');
                    $('#editVariationModal').modal('hide');
                    $('#variation-stats').html(response.stats_html)
                    // به‌روزرسانی ردیف جدول بدون reload
                    if (response.row_html) {
                        $('#variation-row-' + response.price_id).replaceWith(response.row_html);
                    } else {
                        location.reload(); // fallback
                    }
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = xhr.responseJSON?.message ?? 'خطایی رخ داد';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                $('#editErrorAlert').removeClass('d-none').html(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="feather icon-save"></i> ذخیره تغییرات');
            }
        });
    });

    // ============================================================
    // ارسال فرم افزودن تنوع جدید با AJAX
    // ============================================================
    $('#addVariationForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#addSubmitBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ذخیره...');
        $('#addErrorAlert').addClass('d-none');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message ?? 'تنوع جدید با موفقیت اضافه شد.');
                    $('#addVariationModal').modal('hide');
                    $('#addVariationForm')[0].reset();
                    $('#variation-stats').html(response.stats_html)
                    // اضافه کردن ردیف جدید به جدول
                    if (response.row_html) {
                        $('#empty-row').remove();
                        $('table#variationsTable tbody').append(response.row_html);
                    } else {
                        location.reload();
                    }
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                let msg = xhr.responseJSON?.message ?? 'خطایی رخ داد';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                $('#addErrorAlert').removeClass('d-none').html(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="feather icon-save"></i> افزودن تنوع');
            }
        });
    });

    // ============================================================
    // حذف تنوع
    // ============================================================
    $(document).on('click', '.btn-delete-variation', function () {
        const priceId = $(this).data('price-id');
        const url = $(this).data('url');

        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: 'این تنوع حذف خواهد شد و قابل بازگشت نیست.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.value) {

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success('تنوع با موفقیت حذف شد.');
                            $('#variation-stats').html(response.stats_html)
                            $('#variation-row-' + priceId).fadeOut(400, function () {
                                $(this).remove();
                            });
                        }
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    error: function () {
                        toastr.error('خطا در حذف تنوع.');
                    }
                });
            }
        });
    });

});
