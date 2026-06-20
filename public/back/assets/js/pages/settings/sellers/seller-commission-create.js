// validate form with jquery validation plugin
jQuery('#seller-commission-create-form').validate({
    rules: {
        question: {
            required: true
        },
        answer: {
            required: true
        }
    }
});


$('#seller-commission-create-form').submit(function (e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                $('#brand-create-form').data('disabled', true);
                window.location.href = BASE_URL + '/settings/seller-commission';
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
