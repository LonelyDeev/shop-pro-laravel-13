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
                if (select.val()=="pay"){
                    $('#trackingIdTr').removeClass('d-none');
                }else {
                    $('#trackingIdTr').addClass('d-none');
                    $('input[name=trackingId]').val(null);
                }
                $.ajax({
                    url: select.data('action'),
                    type: 'post',
                    data:{'status_pay':select.val()},
                    success: function (data) {
                        $('#data-'+data.id).find('.status-pay').empty();
                        $('#data-'+data.id).find('.status-pay').html(data.status_pay);
                        $('.status_pay').remove();
                        showCustomToast('تغییرات با موفقیت ذخیره شد','success');
                    },
                    beforeSend: function (xhr) {
                        block('.modal-content');
                    },
                    complete: function () {
                        unblock('.modal-content');
                    },
                });
            });




            $('.trackingIdBtn').on('click', function () {
                var select = $(this);
                var trackingId=$('input[name=trackingId]').val();
                $.ajax({
                    url: select.data('action'),
                    type: 'post',
                    data:{'trackingId':trackingId},
                    success: function (data) {
                        showCustomToast('تغییرات با موفقیت ذخیره شد','success');
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



$('#filter-comments-form select').change(function() {
    $('#filter-comments-form').submit();
});
