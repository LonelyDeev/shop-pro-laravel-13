// validate form with jquery validation plugin
jQuery('#slider-create-form').validate({
    errorClass: 'invalid-feedback animated fadeInDown',
    errorPlacement: function(error, e) {
        jQuery(e).parents('.form-group').append(error);
    },
    highlight: function(e) {
        jQuery(e).closest('.form-group').find('input').removeClass('is-invalid').addClass('is-invalid');
    },
    success: function(e) {
        jQuery(e).closest('.form-group').find('input').removeClass('is-invalid');
        jQuery(e).remove();
    },
    invalidHandler: function(form, validator) {

        if (!validator.numberOfInvalids())
            return;

        $('html, body').animate({
            scrollTop: $(validator.errorList[0].element).offset().top - 150
        }, 200);

        $(validator.errorList[0].element).focus();

    },
    rules: {
        'image': {
            required: true,
        },
        'group': {
            required: true,
        },
    },
});

$(".slider-link").autocomplete({
    source: pages
});

$('#slider-create-form').submit(function(e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#slider-create-form').data('disabled', true);
                window.location.href = BASE_URL + "/sliders";
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

$('select[name=group]').change(function () {
    var select = this;
    if ($(select).val()=="main_story"){
        $('#story-only').removeClass('d-none');
        $('.story-hide').addClass('d-none');
        $('.story-hide input').attr('disabled','disabled');
        $('#story-only input').removeAttr('disabled');
    }else {
        $('#story-only').addClass('d-none');
        $('.story-hide').removeClass('d-none');
        $('.story-hide input').removeAttr('disabled');
        $('#story-only input').attr('disabled','disabled');
    }
});


document.addEventListener("DOMContentLoaded", function () {

    document.getElementById('button-image').addEventListener('click', (event) => {
        event.preventDefault();

        window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });
});

// set file link
function fmSetLink($url) {
    document.getElementById('image_label').value = $url;
    $('#button-image .img-uploader').removeClass('display-hidden');
    $('.remove-img-uploader').removeClass('display-hidden');
    $('#button-image img').attr('src', $url)
}

$('.remove-img-uploader').click(function () {
    $('#button-image .img-uploader').addClass('display-hidden');
    $('.remove-img-uploader').addClass('display-hidden');
    document.getElementById('image_label').value = null;
})


// دریافت تمام گروه‌های اسلایدر از کانفیگ


// تابع برای آپدیت کردن گروه‌ها بر اساس صفحه انتخاب شده
function updateGroupsByPage(selectedPage) {
    var groupSelect = $('#group_select');
    groupSelect.html('');

    if(sliderGroups[selectedPage] && sliderGroups[selectedPage].length > 0) {
        $.each(sliderGroups[selectedPage], function(index, group) {
            groupSelect.append(
                $('<option></option>')
                    .val(group.group)
                    .text(group.name + ' (' + group.size + ')')
                    .data('width', group.width)
                    .data('height', group.height)
            );
        });

        // نمایش اطلاعات سایز برای گروه اول
        showImageSizeInfo(groupSelect.find('option:first'));
    } else {
        groupSelect.append(
            $('<option></option>')
                .val('')
                .text('هیچ گروهی برای این صفحه تعریف نشده است')
        );
        $('#image_size_info').hide();
    }
}

// تابع برای نمایش اطلاعات سایز تصویر
function showImageSizeInfo(selectedOption) {
    if(selectedOption && selectedOption.length > 0) {
        var width = selectedOption.data('width');
        var height = selectedOption.data('height');
        if(width && height) {
            $('#recommended_size').text(width + ' × ' + height + ' پیکسل');
            $('#image_size_info').show();
        } else {
            $('#image_size_info').hide();
        }
    } else {
        $('#image_size_info').hide();
    }
}

// رویداد تغییر صفحه
$('#page_select').on('change', function() {
    var selectedPage = $(this).val();
    updateGroupsByPage(selectedPage);
});

// رویداد تغییر گروه (برای نمایش سایز)
$('#group_select').on('change', function() {
    var selectedOption = $(this).find('option:selected');
    showImageSizeInfo(selectedOption);
});

// اجرای اولیه برای صفحه فعلی (در حالت ویرایش)
var initialPage = $('#page_select').val();
if(initialPage) {
    updateGroupsByPage(initialPage);

    // در حالت ویرایش، گروه فعلی را انتخاب کن

    if(currentGroup) {
        setTimeout(function() {
            $('#group_select').val(currentGroup).trigger('change');
        }, 100);
    }
}
