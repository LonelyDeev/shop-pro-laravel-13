$(document).ready(function() {
    jQuery('#update-story').validate({
        rules: {
            'title': {
                required: true,
            },
            'cover_image': {
                required: function() {
                    var coverFileName = $('#coverFileName').data('value');
                    // اگر مقدار data-value خالی یا undefined بود، required باشد
                    return !coverFileName || coverFileName === '';
                }
            },
            'image': {
                required: function(element) {
                    var mediaType = $('select[name="type"]').val();
                    var contentFileName = $('#contentFileName').data('value');

                    // فقط اگر type === 'image' و فایلی وجود نداشته باشد، required باشد
                    return mediaType === 'image' && (!contentFileName || contentFileName === '');
                }
            },
            'video': {
                required: function(element) {
                    var mediaType = $('select[name="type"]').val();
                    var videoUrlInput = $('#videoUrlInput').val();

                    // فقط اگر type === 'video' و فایلی وجود نداشته باشد، required باشد
                    return mediaType === 'video' && (!videoUrlInput || videoUrlInput === '');
                }
            },
            'expiry_date': {
                required: true,
            },
        },
        messages: {
            'cover_image': {
                required: "لطفاً تصویر کاور را انتخاب کنید",
            },
            'image': {
                required: "لطفاً فایل عکس را آپلود کنید",
            },
            'video': {
                required: "لطفاً فایل ویدیو را آپلود کنید",
            },
            'expiry_date': {
                required: "لطفاً تاریخ انقضا را انتخاب کنید",
            },
        },
        // تنظیمات اضافی برای فایل و هیدن فیلدها
        ignore: [], // این خط باعث می‌شود فیلدهای مخفی هم اعتبارسنجی شوند
    });

    $('#update-story').submit(function(e) {
        e.preventDefault();

        var form = $(this);

        if($(this).valid()) {



            if (form.valid() && !form.data('disabled')) {
                var date = $('#expiryDate').val();
                $('#expiryDate').val(date.toEnglishDigit());
                var formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if (data == 'success') {
                            $('#create-new-story-form').data('disabled', true);
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
            }

        }else {
            showCustomToast('همه مغادیر ستاره دارد باید پر شوند','error');
        }
    })

    var publishDatePicker;

    jQuery(function () {
        publishDatePicker = $('#expiry_date_picker').pDatepicker({
            timePicker: {
                enabled: true,
                meridian: {
                    enabled: false
                },
                second: {
                    enabled: false
                }
            },
            toolbox: {
                // enabled: true,
                calendarSwitch: {
                    enabled: false
                }
            },
            initialValue: false,
            initialValueType: 'persian',
            altField: '#expiryDate',
            altFormat: 'YYYY-MM-DD HH:mm:ss',

            onSelect: function (unixDate) {
                var date = $('#expiryDate').val();
                $('#expiryDate').val(date.toEnglishDigit());
            },
            onSet: function (unixDate) {
                var date = $('#expiryDate').val();
                $('#expiryDate').val(date.toEnglishDigit());
            }
        });

        var date = $('#expiry_date_picker').val();

        if (date) {
            publishDatePicker.setDate(parseInt(date + '000'));
        }
    });

});
