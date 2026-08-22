$(document).ready(function() {
    $('#activityModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const actionUrl = button.data('action');

        const modal = $(this);
        const modalContent = modal.find('#modalContent');

        // نمایش لودینگ
        modalContent.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <p class="mt-3 ">در حال دریافت اطلاعات...</p>
            </div>
        `);

        // یک درخواست هم برای دریافت HTML آماده
        $.ajax({
            url: actionUrl,
            method: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    modalContent.html(response.html);
                } else {
                    modalContent.html(`
                        <div class="alert alert-danger m-3">
                            خطا در دریافت اطلاعات
                        </div>
                    `);
                }
            },
            error: function() {
                modalContent.html(`
                    <div class="alert alert-danger m-3">
                        خطا در ارتباط با سرور
                    </div>
                `);
            }
        });
    });

    jQuery(function () {
        // تنظیمات مشترک
        var commonSettings = {
            timePicker: {
                enabled: false,
                meridian: { enabled: false },
                second: { enabled: false }
            },
            toolbox: {
                calendarSwitch: { enabled: false }
            },
            initialValue: false,
            initialValueType: 'persian',
            altFormat: 'YYYY-MM-DD',
            onSelect: function (unixDate) {
                var date = $(this).val();
                $(this).val(date.toEnglishDigit());
            },
            onSet: function (unixDate) {
                var date = $(this).val();
                $(this).val(date.toEnglishDigit());
            }
        };

        // اعمال به هر دو فیلد
        $('#from_date_picker, #to_date_picker').each(function() {
            $(this).pDatepicker({
                ...commonSettings,
                altField: $(this)
            });
        });
    });


    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    }

});
// نمایش مودال
function deleteOldActivities() {
    $('#deleteOldActivitiesModal').modal('show');
}


// تایید و ارسال درخواست
function confirmDeleteOldActivities(item) {
    var days = parseInt($('#delete_days').val(), 10);
    var $btn = $('#btnConfirmDelete');
    var url=$(item).data('action')

    // اعتبارسنجی
    if (!days || days < 1) {
        showCustomToast('لطفاً تعداد روز معتبر وارد کنید','error');
        $('#delete_days').focus();
        return;
    }

    // غیرفعال کردن دکمه و نمایش لودینگ
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> در حال حذف...');

    $.ajax({
        url: url,
        type: 'POST',  // ← Laravel معمولاً DELETE رو از طریق POST + _method می‌پذیره
        data: {
            _method: 'DELETE',
            days: days,
        },
        success: function(response) {
            if (response.success) {
                showCustomToast(response.message,'success');
                $('#deleteOldActivitiesModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showCustomToast(response.message || 'خطا در حذف فعالیت‌ها','error');
                $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> حذف');
            }
        },
        beforeSend: function (xhr) {
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        error: function(xhr) {
            var message = 'خطا در حذف فعالیت‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.status === 419) {
                message = 'خطای CSRF — لطفاً صفحه را رفرش کنید';
            } else if (xhr.status === 405) {
                message = 'متد HTTP مجاز نیست — Route رو چک کنید';
            } else if (xhr.status === 404) {
                message = 'Route پیدا نشد';
            }
            showCustomToast(message,'error');
            $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> حذف');
        }
    });
}

// ریست دکمه هنگام بسته شدن مودال
$('#deleteOldActivitiesModal').on('hidden.bs.modal', function() {
    $('#btnConfirmDelete').prop('disabled', false).html('<i class="fas fa-trash"></i> حذف');
    $('#delete_days').val(30);
});
