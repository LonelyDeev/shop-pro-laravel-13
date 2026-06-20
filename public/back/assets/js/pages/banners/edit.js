$('#banner-edit-form').submit(function(e) {
    e.preventDefault();

    if (!$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#banner-edit-form').data('disabled', true);
                window.location.href = BASE_URL + "/banners";
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

$(".banner-link").autocomplete({
    source: pages
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





function updateGroupsByPage(selectedPage, selectedGroup = null) {
    var groupSelect = $('#group_select');
    groupSelect.html('');

    if(bannerGroups[selectedPage] && bannerGroups[selectedPage].length > 0) {
        $.each(bannerGroups[selectedPage], function(index, group) {
            var option = $('<option></option>')
                .val(group.group)
                .text(group.name + ' (' + group.size + ')')
                .data('width', group.width)
                .data('height', group.height);

            if(selectedGroup && selectedGroup == group.group) {
                option.attr('selected', 'selected');
            }

            groupSelect.append(option);
        });

        var selectedOption = groupSelect.find('option:selected');
        if(selectedOption.length === 0) {
            selectedOption = groupSelect.find('option:first');
        }
        showSizeInfo(selectedOption);
    } else {
        groupSelect.append(
            $('<option></option>')
                .val('')
                .text('هیچ گروه بنری برای این صفحه تعریف نشده است')
        );
        $('#size_info').hide();
    }
}

function showSizeInfo(selectedOption) {
    if(selectedOption && selectedOption.length > 0) {
        var width = selectedOption.data('width');
        var height = selectedOption.data('height');

        if(width && height) {
            $('#recommended_size').text(width + ' × ' + height + ' پیکسل');
            $('#size_info').show();
        } else {
            $('#size_info').hide();
        }
    } else {
        $('#size_info').hide();
    }
}

$('#page_select').on('change', function() {
    var selectedPage = $(this).val();
    updateGroupsByPage(selectedPage);
});

$('#group_select').on('change', function() {
    var selectedOption = $(this).find('option:selected');
    showSizeInfo(selectedOption);
});

// اجرای اولیه برای ویرایش
var initialPage = $('#page_select').val();

if(initialPage) {
    updateGroupsByPage(initialPage, currentGroup);
}
});
