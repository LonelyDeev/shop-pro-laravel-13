$(document).on('click', '.btn-delete', function() {
    $('#attribute-delete-form').attr('action', BASE_URL + '/attributes/' + $(this).data('attribute'));
    $('#attribute-delete-form').data('id', $(this).data('id'));
});

$('#attribute-delete-form').submit(function(e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var form = this;
    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            //get current url
            var url = window.location.href;

            //remove attribute tr
            $('#attribute-' + $(form).data('id') + '-tr').remove();

            toastr.success('ویژگی با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });

            //refresh attributes list
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
