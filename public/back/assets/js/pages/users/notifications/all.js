jQuery('#notifications-create-form').validate({
    rules: {
        message: {
            required: true
        }
    }
});
$('#notifications-create-form').submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);


    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            if (data == 'success') {
                $('#post-create-form').data('disabled', true);
                window.location.href = form.data('redirect');
            }
        },
        beforeSend: function (xhr) {
            block('#notifications-create-form');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {
            unblock('#notifications-create-form');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});


