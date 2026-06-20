$('#excel-create-form').submit(function(e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#excel-create-form').data('disabled', true);
                if (data.error){
                    toastr.error(data.error, null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
                }else if (data.success){
                    Swal.fire({
                            title: data.success,
                            type: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'باشه',
                            closeOnConfirm: false,
                            closeOnCancel: false
                        }
                    ).then((result) => {
                        window.location.reload();
                    });
                }
            },
            beforeSend: function(xhr) {
                block('#main-card');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function() {

                unblock('#main-card');
                $('#form-progress').hide();
                $('#form-progress').find('.progress-bar').css('width', '0%');
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                //Upload progress
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;

                        $('#form-progress').show();
                        $('#form-progress').find('.progress-bar').css('width', percentComplete * 100 + '%');
                        $('#form-progress').find('.progress-bar').text(Math.round(percentComplete * 100) + '%');
                    }
                }, false);

                return xhr;
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

});
