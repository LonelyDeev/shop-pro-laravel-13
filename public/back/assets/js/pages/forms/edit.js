$(document).ready(function() {

    $('#tags').tagsInput({
        defaultText: 'افزودن',
        width: '100%',
        autocomplete_url: BASE_URL + '/get-tags'
    });

    $(document).on('click', '.remove-exist-field', function() {
        $('#delete-form').attr('action', $(this).data('action'));
        $('#delete-form').data('id', $(this).data('id'));
    });

    $('#delete-form').on('submit', function(e) {
        e.preventDefault();

        $('#delete-modal').modal('hide');
        var form = this;
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                if (data == 'success') {
                    showCustomToast('فیلد با موفقیت حذف شد.','success')
                    $('#preview-field-' + $(form).data('id')).remove();
                }
            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader(
                    'X-CSRF-TOKEN',
                    $('meta[name="csrf-token"]').attr('content')
                );
            },
            complete: function() {
                unblock('#main-card');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });



    new Sortable(document.getElementById('exist-fields'), {
        group: {
            name: 'fields',
            pull: true,
            revertClone: false
        },
        handle: '.dd-handle',  // فقط با این کلاس جابجایی انجام شود
        animation: 150,
        onEnd: function() {
            saveChanges();
        }
    });

    new Sortable(document.getElementById('fields-preview'), {
        group: {
            name: 'fields',
            pull: true,
            revertClone: false
        },
        handle: '.dd-handle',  // فقط با این کلاس جابجایی انجام شود
        animation: 150,
        onEnd: function() {
            saveChanges();
        }
    });

    function saveChanges() {
        var orderIds = [];

        $('#exist-fields .preview-field, #fields-preview .preview-field').each(function(index) {
            orderIds.push($(this).data('id'));
        });

        $.ajax({
            url: $('.nestable').data('action'),
            type: 'POST',
            data: {order: orderIds},
            success: function(data) {
                showCustomToast('ترتیب فیلد ها بروز شد','success');
            },
            beforeSend: function(xhr) {
                block('.nestable');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function() {
                unblock('.nestable');
            },
            cache: false,
        });

    }

});
