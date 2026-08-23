CKEDITOR.replace('content');

$('#tags').tagsInput({
    defaultText: 'افزودن',
    width: '100%',
    autocomplete_url: BASE_URL + '/get-tags'
});

$('#category').select2({
    rtl: true,
    width: '100%'
});

jQuery('#post-create-form').validate({
    rules: {
        title: {
            required: true
        }
    }
});

$('#post-create-form').submit(function (e) {
    e.preventDefault();

    var form = $(this);

    if (form.valid() && !form.data('disabled')) {
        var date = $('#publish_date').val();
        $('#publish_date').val(date.toEnglishDigit());

        var formData = new FormData(this);
        formData.append('content', CKEDITOR.instances['content'].getData());

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                if (data == 'success') {
                    $('#post-create-form').data('disabled', true);
                    window.location.href = form.data('redirect');
                }
                if (data[0]=='Repetition-slug'){
                    showCustomToast('url از قبل وجود دارد.','error');
                    $('input[name=slug]').val(data[1]);
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
});

$('#publish_date_picker').pDatepicker({
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
    altField: '#publish_date',
    altFormat: 'YYYY-MM-DD HH:mm:ss',

    onSelect: function (unixDate) {
        var date = $('#publish_date').val();
        $('#publish_date').val(date.toEnglishDigit());
    }
});


$(document).ready(function() {
    $("select[name=created_by]").change(function() {

        // var selectedVal = $("#myselect option:selected").text();
        var selectedVal = $(this).val();
        if (selectedVal=="ai"){
            $('.show-ai').removeClass('d-none');
            $('.show-ai-des').removeClass('d-none');
            $('.show-ai-pro').addClass('d-none');
            $('.show-ai-pro-des').addClass('d-none');
        }else if (selectedVal=="ai-pro"){
            $('.show-ai').removeClass('d-none');
            $('.show-ai-pro').removeClass('d-none');
            $('.show-ai-pro-des').removeClass('d-none');
            $('.show-ai-des').addClass('d-none');
        }else {
            $('.show-ai').addClass('d-none');
            $('.show-ai-pro').addClass('d-none');
        }

    });
});
