/* ============================================================
   Packages - Installed page (toggle / uninstall)
   ============================================================ */
(function ($) {
    "use strict";

    $(function () {
        // فعال/غیرفعال
        $('.btn-toggle').on('click', function () {
            const slug = $(this).data('slug');
            const $btn = $(this);

            $.ajax({
                url: `/admin/packages/${slug}/toggle`,
                method: 'POST',
                data: { _token: csrfToken() },
                success: function (resp) {
                    if (resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: resp.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message, confirmButtonText: 'بستن' });
                }
            });
        });

        // حذف - باز کردن modal
        $('.btn-uninstall').on('click', function () {
            const slug = $(this).data('slug');
            const name = $(this).data('name');
            $('#uninstall-pkg-name').text(name);
            $('#uninstall-form').data('slug', slug);
        });

        // ارسال فرم حذف
        $('#uninstall-form').on('submit', function (e) {
            e.preventDefault();
            const slug = $(this).data('slug');
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال حذف...');

            $.ajax({
                url: `/admin/packages/${slug}/uninstall`,
                method: 'POST',
                data: { _token: csrfToken() },
                success: function (resp) {
                    if (resp.success) {
                        $('#uninstall-modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'موفق',
                            text: resp.message,
                            confirmButtonText: 'بستن'
                        }).then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message, confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message, confirmButtonText: 'بستن' });
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="feather icon-trash-2"></i> بله، حذف شود');
                }
            });
        });
    });

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

})(jQuery);
