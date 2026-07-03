$(document).ready(function() {

    let deleteId = null;
    let deleteAction = null;

    // -------------------------------------------
    // 1. نمایش تعداد آیتم‌های انتخاب شده (چک‌باکس)
    // -------------------------------------------
    $(document).on('change', '.faq-checkbox', function() {
        let checkedCount = $('.faq-checkbox:checked').length;
        $('#datatable-selected-rows').text(checkedCount);
        if (checkedCount > 0) {
            $('#multiple-actions-bar').slideDown();
        } else {
            $('#multiple-actions-bar').slideUp();
        }
    });

    // -------------------------------------------
    // 2. باز کردن مودال ایجاد
    // -------------------------------------------
    $(document).on('click', '#btn-create-faq', function() {
        $('#faq-form')[0].reset();
        $('#faq_id').val('');
        $('#faq-modal-title').text('افزودن سوال جدید');
        $('#faq-form-modal').modal('show');
        $('#faq-form').attr('action', $(this).data('action'));

        // انتقال فوکوس به مودال
        setTimeout(function() {
            $('#faq-form-modal').find('input:not([type="hidden"]), textarea, select').first().focus();
        }, 300);
    });

    // -------------------------------------------
    // 3. باز کردن مودال ویرایش (دریافت اطلاعات با AJAX)
    // -------------------------------------------
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let action = $(this).data('action');
        let updateAction = $(this).data('action-update');

        block('#main-card');

        $.get(action, function(data) {
            $('#faq_id').val(data.id);
            $('#faq_question').val(data.question);
            $('#faq_answer').val(data.answer);
            $('#faq_order').val(data.order);
            $('#faq_published').val(data.published);
            $('#faq-modal-title').text('ویرایش سوال');
            $('#faq-form-modal').modal('show');
            $('#faq-form').attr('action', updateAction);
            unblock('#main-card');

            // انتقال فوکوس به مودال
            setTimeout(function() {
                $('#faq-form-modal').find('input:not([type="hidden"]), textarea, select').first().focus();
            }, 300);
        }).fail(function() {
            customToast('❌', 'خطا در دریافت اطلاعات');
            unblock('#main-card');
        });
    });

    // -------------------------------------------
    // 4. ارسال فرم ایجاد/ویرایش (AJAX)
    // -------------------------------------------
    $(document).on('submit', '#faq-form', function(e) {
        e.preventDefault();
        let id = $('#faq_id').val();
        let method = id ? 'PUT' : 'POST';
        let $form = $(this);

        $.ajax({
            url: $form.attr('action'),
            method: method,
            data: $form.serialize(),
            success: function(response) {
                $('#faq-form-modal').modal('hide');
                if (id) {
                    let $oldItem = $(`#faq-${response.faq.id}-tr`);

                    if ($oldItem.length) {
                        // به‌روزرسانی سوال
                        $oldItem.find('.faq-question-text').html(response.faq.question);

                        // به‌روزرسانی جواب
                        $oldItem.find('.faq-answer-text').html(response.faq.answer);

                        // به‌روزرسانی وضعیت انتشار (badge)
                        if (response.faq.published==1) {
                            $oldItem.find('.faq-status-badge')
                                .removeClass('badge-danger')
                                .addClass('badge-success')
                                .text('فعال');
                        } else {
                            $oldItem.find('.faq-status-badge')
                                .removeClass('badge-success')
                                .addClass('badge-danger')
                                .text('غیرفعال');
                        }


                    }
                } else {
                    $('#faqAccordion').prepend(response.html);
                }

                customToast('✅', response.success);
                unblock('#faq-form');
            },
            beforeSend: function(xhr) {
                block('#faq-form');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            error: function(xhr) {
                let errors = '';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value[0] + '\n';
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errors = xhr.responseJSON.message;
                } else {
                    errors = 'خطایی رخ داده است.';
                }
                customToast('❌', errors);
                unblock('#faq-form');
            }
        });
    });

    // -------------------------------------------
    // 5. باز کردن مودال تایید حذف تکی
    // -------------------------------------------
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        deleteId = $(this).data('id');
        deleteAction = $(this).data('action');
        $('#delete-modal').modal('show');
        $('#btn-confirm-delete').data('action', deleteAction);
        $('#btn-confirm-delete').data('id', deleteId);

        // فوکوس روی دکمه تایید
        setTimeout(function() {
            $('#btn-confirm-delete').focus();
        }, 300);
    });

    // -------------------------------------------
    // 6. حذف تکی (AJAX)
    // -------------------------------------------
    $(document).on('click', '#btn-confirm-delete', function() {
        let deleteAction = $(this).data('action');
        let deleteId = $(this).data('id');

        if (deleteId) {
            $.ajax({
                url: deleteAction,
                method: 'DELETE',
                success: function(response) {
                    $('#delete-modal').modal('hide');
                    // حذف با انیمیشن
                    let $item = $(`.faq-item[data-id="${deleteId}"]`);
                    if ($item.length) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            // به‌روزرسانی تعداد آیتم‌های انتخاب شده
                            let checkedCount = $('.faq-checkbox:checked').length;
                            $('#datatable-selected-rows').text(checkedCount);
                            if (checkedCount === 0) {
                                $('#multiple-actions-bar').slideUp();
                            }
                        });
                    }
                    customToast('✅', response.success);
                    unblock('#delete-modal');
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    block('#delete-modal');
                },
                error: function(xhr) {
                    customToast('❌', 'خطا در حذف آیتم');
                    unblock('#delete-modal');
                }
            });
        }
    });

    // -------------------------------------------
    // 7. حذف گروهی
    // -------------------------------------------
    $(document).on('click', '#btn-multiple-delete', function() {
        let checkedCount = $('.faq-checkbox:checked').length;
        if (checkedCount === 0) {
            customToast('⚠️', 'لطفاً حداقل یک آیتم را انتخاب کنید.');
            return;
        }
        $('#multiple-delete-modal').modal('show');
        $('#btn-confirm-multiple-delete').data('action', $(this).data('action'));

        setTimeout(function() {
            $('#btn-confirm-multiple-delete').focus();
        }, 300);
    });

    // -------------------------------------------
    // 8. تایید حذف گروهی
    // -------------------------------------------
    $(document).on('click', '#btn-confirm-multiple-delete', function() {
        let deleteAction = $(this).data('action');
        let ids = [];
        $('.faq-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length > 0) {
            $.ajax({
                url: deleteAction,
                method: 'DELETE',
                data: { ids: ids },
                success: function(response) {
                    $('#multiple-delete-modal').modal('hide');

                    // حذف همه آیتم‌های انتخاب شده
                    ids.forEach(function(id) {
                        let $item = $(`.faq-item[data-id="${id}"]`);
                        if ($item.length) {
                            $item.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    });

                    // مخفی کردن نوار ابزار چندگانه
                    $('#multiple-actions-bar').slideUp();
                    $('.faq-checkbox').prop('checked', false);
                    $('#datatable-selected-rows').text(0);

                    customToast('✅', response.success);
                    unblock('#multiple-delete-modal');
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    block('#multiple-delete-modal');
                },
                error: function(xhr) {
                    customToast('❌', 'خطا در حذف گروهی');
                    unblock('#multiple-delete-modal');
                }
            });
        }
    });

    // -------------------------------------------
    // 9. مدیریت فوکوس در مودال‌ها (رفع خطای ARIA)
    // -------------------------------------------
    $(document).on('shown.bs.modal', '.modal', function() {
        // فوکوس روی اولین input یا دکمه در مودال
        const $firstInput = $(this).find('input:not([type="hidden"]), textarea, select, button:not(.close):not(.btn-close)').first();
        if ($firstInput.length) {
            $firstInput.focus();
        }
    });

    // -------------------------------------------
    // 10. بستن مودال با کلیک روی دکمه بستن (اختیاری)
    // -------------------------------------------
    $(document).on('click', '.btn-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    });

    // -------------------------------------------
    // 11. ریست کردن فرم بعد از بستن مودال
    // -------------------------------------------
    $(document).on('hidden.bs.modal', '#faq-form-modal', function() {
        // پاک کردن خطاها و ریست فرم
        $('#faq-form').find('.is-invalid').removeClass('is-invalid');
        $('#faq-form').find('.invalid-feedback').remove();
        // ریست کردن فرم (اختیاری)
        // $('#faq-form')[0].reset();
    });

});

