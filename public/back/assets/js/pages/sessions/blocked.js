$(document).on('click', '.unblock-btn', function() {
    let button = $(this);
    let id=button.data('id')
    Swal.fire({
        title: 'رفع بلاک',
        text: 'آیا از رفع بلاک این دستگاه اطمینان دارید؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: button.data('action'),
                type: 'DELETE',
                success: function(response) {
                    toastr.success(response.message);
                    $('#row-'+id).remove()
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
            });
        }
    });
});
