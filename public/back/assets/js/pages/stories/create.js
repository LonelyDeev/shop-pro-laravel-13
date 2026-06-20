$(document).ready(function() {
    jQuery('#create-new-story').validate({
        rules: {
            'title': {
                required: true,
            },
            'cover_image': {
                required: true,
            },
            'image': {
                required: function(element) {
                    var mediaType = $('select[name="type"]').val(); // حذف :checked
                    return mediaType === 'image';
                }
            },
            'video': {
                required: function(element) {
                    var mediaType = $('select[name="type"]').val(); // حذف :checked
                    return mediaType === 'video';
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

    $('#create-new-story').submit(function(e) {
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
            toastr.error('همه مغادیر ستاره دارد باید پر شوند', 'پیغام', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
        }
        })


});
