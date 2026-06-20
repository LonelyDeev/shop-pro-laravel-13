$('#slider-edit-form').submit(function(e) {
    e.preventDefault();

    if (!$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#slider-edit-form').data('disabled', true);
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

$(".slider-link").autocomplete({
    source: pages
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


// تابع برای آپدیت کردن گروه‌ها بر اساس صفحه انتخاب شده
function updateGroupsByPage(selectedPage, selectedGroup = null) {
    var groupSelect = $('#group_select');
    groupSelect.html('');

    if(sliderGroups[selectedPage] && sliderGroups[selectedPage].length > 0) {
        $.each(sliderGroups[selectedPage], function(index, group) {
            var option = $('<option></option>')
                .val(group.group)
                .text(group.name + ' (' + group.size + ')');

            if(selectedGroup && selectedGroup == group.group) {
                option.attr('selected', 'selected');
            }

            groupSelect.append(option);
        });
    } else {
        groupSelect.append(
            $('<option></option>')
                .val('')
                .text('هیچ گروهی برای این صفحه تعریف نشده است')
        );
    }
}

// رویداد تغییر صفحه
$('#page_select').on('change', function() {
    var selectedPage = $(this).val();
    updateGroupsByPage(selectedPage);
});

// اجرای اولیه برای صفحه فعلی (در حالت ویرایش)
var initialPage = $('#page_select').val();

if(initialPage) {
    updateGroupsByPage(initialPage, currentGroup);
}

