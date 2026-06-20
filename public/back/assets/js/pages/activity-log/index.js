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



    // حذف فعالیت‌های قدیمی
    function deleteOldActivities() {
        $('#deleteOldActivitiesModal').modal('show');
    }

    function confirmDeleteOldActivities() {
        var days = $('#delete_days').val();

        if (!days || days < 1) {
            toastr.error('لطفاً تعداد روز معتبر وارد کنید');
            return;
        }

        if (!confirm(`آیا مطمئن هستید که می‌خواهید فعالیت‌های قدیمی‌تر از ${days} روز را حذف کنید؟`)) {
            return;
        }

        $.ajax({
            url: '{{ route("admin.activity-log.delete-old") }}',
            type: 'DELETE',
            data: {
                days: days,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#deleteOldActivitiesModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'خطا در حذف فعالیت‌ها');
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON?.message || 'خطا در حذف فعالیت‌ها';
                toastr.error(message);
            }
        });
    }

});
