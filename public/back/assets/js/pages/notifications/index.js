$(document).on('click', '.btn-delete', function() {
    $('#ticket-delete-form').attr('action', $(this).data('action'));
});

$('#ticket-delete-form').submit(function(e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

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
