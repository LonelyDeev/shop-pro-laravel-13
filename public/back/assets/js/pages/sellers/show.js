$('select[name=private_business]').click(function () {
    var item = this;
    var private_business = $(item).val()
    if (private_business == 'private') {
        $('#business-div').addClass('d-none');
        $('#private-div').removeClass('d-none');
    } else if (private_business == 'business') {
        $('#private-div').addClass('d-none');
        $('#business-div').removeClass('d-none');
    }
});

$('select[name=vat_free]').click(function () {
    var item = this;
    var vat_free = $(item).val()
    if (vat_free == '1') {
        $('#vat_free_div').removeClass('d-none');
    } else if (vat_free == '2') {
        $('#vat_free_div').addClass('d-none');
    }
});


jQuery('#seller-edit-form').validate({
    rules: {
        status: {
            required: true
        },
        status_register: {
            required: true
        },
        status_documents: {
            required: true
        },
        status_work: {
            required: true
        },
        private_business: {
            required: true
        },
        business_name: {
            required: true
        },
        first_name: {
            required: true
        },
        last_name: {
            required: true
        },
        gender: {
            required: true
        },
        birth_day: {
            required: true
        },
        national_identity_number: {
            required: true
        },
        identity_card_number: {
            required: true
        },
        shaba_number: {
            required: true
        },
        email: {
            required: true
        },
        mobile: {
            required: true
        },
        state_id: {
            required: true
        },
        city_id: {
            required: true
        },
        address: {
            required: true
        },
        post_code: {
            required: true
        },
        phone: {
            required: true
        },
        vat_free: {
            required: true
        },
    }
});

$('#seller-edit-form').submit(function (e) {
    e.preventDefault();

    var form = $(this);

    if (form.valid() && !form.data('disabled')) {

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
                block('#seller-edit-form');
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function () {
                unblock('#seller-edit-form');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }
});
$('.product-category').select2ToTree({
    rtl: true,
    width: '100%'
});
$('#product-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#delete-modal-product').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            toastr.success('محصول با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
            var url = window.location.href;
            $(".app-content").load(url + " .app-content > *");

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
});
$(document).on('click', '.btn-delete', function () {
    $('#product-delete-form').attr('action', $(this).data('action'));
});
$(document).on('click', '.btn-delete-ticket', function() {
    $('#ticket-delete-form').attr('action', $(this).data('action'));
});

$('#ticket-delete-form').submit(function(e) {
    e.preventDefault();

    $('#delete-ticket-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            //get current url
            var url = window.location.href;

            toastr.success('اعلان با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });

            //refresh tickets list
            $(".app-content").load(url + " .app-content > *");
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


});



$(document).on('click', '.btn-delete', function () {
    $('#carrier-delete-form').attr('action', $(this).data('action'));
});

$('#carrier-delete-form').submit(function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            if (data == 'success') {
                //get current url
                var url = window.location.href;

                toastr.success('برند با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });

                //refresh carriers list
                $('.app-content').load(url + ' .app-content > *');
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
});
