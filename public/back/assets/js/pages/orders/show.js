$('#print-order').click(function () {
    window.print();
});

$('#shipping-status').change(function () {
    var select = this;

    if ($(select).val()=="post-sent"){
        $('#TrackingCode').removeClass('d-none');
    }else {
        $('#TrackingCode').addClass('d-none');
    }

    if ($(select).val()!="canceled"){
        $.ajax({
            url: $(select).data('action'),
            type: 'POST',
            data: {
                status: $(select).val(),
            },
            success: function (data) {
                Swal.fire({
                    type: 'success',
                    title: 'تغییرات با موفقیت ذخیره شد',
                    confirmButtonClass: 'btn btn-primary',
                    confirmButtonText: 'باشه',
                    buttonsStyling: false,
                })
            },
            beforeSend: function (xhr) {
                block('#main-card');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function () {
                unblock('#main-card');
            },

        });
    }else {
        var orderCanceled=$('input[name=orderCanceled]').val();

        if (orderCanceled!=1){
            $('#back-money-modal').modal('show');
            $('#back-money-modal #back-money-form button').click(function() {
                var back_money=$(this).val();
                var back_money_val='no';
                if (back_money=="yes"){
                    back_money_val="yes"
                }else if (back_money=="no"){
                    back_money_val="no"
                }

                $.ajax({
                    url: $(select).data('action'),
                    type: 'POST',
                    data: {
                        status: $(select).val(),
                        back_money_val: back_money_val,
                    },
                    success: function (data) {
                        Swal.fire({
                            type: 'success',
                            title: 'تغییرات با موفقیت ذخیره شد',
                            confirmButtonClass: 'btn btn-primary',
                            confirmButtonText: 'باشه',
                            buttonsStyling: false,
                        }).then((result) => {
                            window.location.reload();
                        });
                        $('#back-money-modal').modal('hide');
                    },
                    beforeSend: function (xhr) {
                        block('#main-card');
                        xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    complete: function () {
                        unblock('#main-card');
                    },

                });
            })

        }else {
            $.ajax({
                url: $(select).data('action'),
                type: 'POST',
                data: {
                    status: $(select).val(),
                    back_money_val: 'no',
                },
                success: function (data) {
                    Swal.fire({
                        type: 'success',
                        title: 'تغییرات با موفقیت ذخیره شد',
                        confirmButtonClass: 'btn btn-primary',
                        confirmButtonText: 'باشه',
                        buttonsStyling: false,
                    })
                    $('#back-money-modal').modal('hide');
                },
                beforeSend: function (xhr) {
                    block('#main-card');
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                complete: function () {
                    unblock('#main-card');
                },

            });
        }


    }

});

$('#TrackingCode button').click(function() {
    var code=$('#TrackingCode input').val();
    $.ajax({
        url: $('#TrackingCode').data('action'),
        type: 'POST',
        data: {
            code: code,
        },
        success: function (data) {
            Swal.fire({
                type: 'success',
                title: 'تغییرات با موفقیت ذخیره شد',
                confirmButtonClass: 'btn btn-primary',
                confirmButtonText: 'باشه',
                buttonsStyling: false,
            }).then((result) => {
                window.location.reload();
            });
            $('#back-money-modal').modal('hide');
        },
        beforeSend: function (xhr) {
            block('#main-card');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function () {
            unblock('#main-card');
        },

    });
})
