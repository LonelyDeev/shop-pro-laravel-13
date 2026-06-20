$('.show-history').on('click', function () {
    var btn = $(this);

    $.ajax({
        url: btn.data('action'),
        type: 'GET',
        success: function (data) {
            $('#history-detail').empty();
            $('#history-detail').append(data);
            $('#show-modal').modal('show');

            // status_pay
            $('.status_pay').change(function () {
                var select = $(this);
                $.ajax({
                    url: select.data('action'),
                    type: 'post',
                    data:{'status_pay':select.val()},
                    success: function (data) {
                        $('#data-'+data.id).find('.status-pay').empty();
                        $('#data-'+data.id).find('.status-pay').html(data.status_pay);
                        $('.status_pay').remove();
                        Swal.fire({
                            type: 'success',
                            title: 'تغییرات با موفقیت ذخیره شد',
                            confirmButtonClass: 'btn btn-primary',
                            confirmButtonText: 'باشه',
                            buttonsStyling: false,
                        })
                    },
                    beforeSend: function (xhr) {
                        block('.modal-content');
                    },
                    complete: function () {
                        unblock('.modal-content');
                    },
                });
            });


        },
        beforeSend: function (xhr) {
            block(btn);
        },
        complete: function () {
            unblock(btn);
        },
    });
});



