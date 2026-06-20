

$(document).ready(function() {
    $('#province').change(function () {
        var id = $(this).find(':selected').val();

        $('#city').empty();

        if (!id) {
            return;
        }

        $.ajax({
            type: 'GET',
            url: $('#province').data('action'),
            data: {id: id},
            success: function (data) {
                $(data).each(function (i, item) {
                    $('#city').append(
                        '<option value="' + item.id + '">' + item.name + '</option>'
                    );
                });
            },
            beforeSend: function () {
                block('#city');
            },
            complete: function () {
                unblock('#city');
            }
        });
    });

    $('#type').on('change', function() {
        var type = $(this).val();

        if (type === 'seller') {
            $('#seller-select-container').show();
            $('#seller-required').show();
            $('#seller_id').prop('required', true);
            $('#temp-description').hide();
        } else if (type === 'temp') {
            $('#seller-select-container').hide();
            $('#seller-required').hide();
            $('#seller_id').prop('required', false);
            $('#temp-description').show();
        } else {
            $('#seller-select-container').hide();
            $('#seller-required').hide();
            $('#seller_id').prop('required', false);
            $('#temp-description').hide();
        }
    });

// اجرا در زمان لود صفحه
    $('#type').trigger('change');

});
