$(document).ready(function() {

    CKEDITOR.config.height = 400;
    CKEDITOR.replace('description');

    let fields = [];
    let sortable = null;

    // اعتبارسنجی فرم اصلی
    $('#main-form').validate({
        rules: {
            'title': {
                required: true,
                minlength: 3,
                maxlength: 255
            }
        },
        messages: {
            'title': {
                required: 'عنوان فرم الزامی است',
                minlength: 'عنوان فرم حداقل باید 3 کاراکتر باشد',
                maxlength: 'عنوان فرم حداکثر 255 کاراکتر می‌تواند باشد'
            },
            'slug': {
                required: 'اسلاگ فرم الزامی است',
                pattern: 'اسلاگ فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد',
                remote: 'این اسلاگ قبلاً استفاده شده است'
            }
        },
        errorClass: 'error',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });

    // اعتبارسنجی فیلد فرم
    function validateField() {
        let isValid = true;
        let label = $('#field-label').val();
        let name = $('#field-name').val();
        let type = $('#field-type-select').val();

        $('.error-field-fields').html('');

        if (!label) {
            $('.error-field-label').text('عنوان فیلد الزامی است');
            isValid = false;
        }

        if (!name) {
            $('.error-field-name').html('نام فیلد الزامی است');
            isValid = false;
        } else if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(name)) {
            $('.error-field-name').html('نام فیلد باید با حرف انگلیسی یا زیرخط شروع شود و فقط شامل حروف، اعداد و زیرخط باشد');
            isValid = false;
        } else if (fields.some(f => f.name === name)) {
            $('.error-field-name').html('این نام فیلد قبلاً استفاده شده است');
            isValid = false;
        }

        if ((type === 'select' || type === 'radio' || type === 'checkbox')) {
            let hasOption = false;
            $('.option-item input').each(function() {
                if ($(this).val()) hasOption = true;
            });
            if (!hasOption) {
                toastr.error('حداقل یک گزینه وارد کنید', 'پیغام', {
                    positionClass: 'toast-bottom-left',
                    containerId: 'toast-bottom-left'
                });
                isValid = false;
            }
        }

        return isValid;
    }

    // نمایش/مخفی کردن گزینه‌ها بر اساس نوع فیلد
    $('#field-type-select').on('change', function() {
        let type = $(this).val();
        if (type === 'select' || type === 'radio' || type === 'checkbox') {
            $('#options-container').slideDown();
        } else {
            $('#options-container').slideUp();
        }
    });

    // افزودن گزینه جدید
    $('#add-option').on('click', function() {
        let newOption = `
            <div class="input-group mb-2 option-item">
                <input type="text" class="form-control" placeholder="گزینه جدید">
                <div class="input-group-append">
                    <button class="btn btn-danger remove-option" type="button">×</button>
                </div>
            </div>
        `;
        $('#options-list').append(newOption);
    });

    // حذف گزینه
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-item').remove();
    });

    // رندر فیلدها
    function renderFields(url) {
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                fields: JSON.stringify(fields),
            },
            success: function(response) {
                $('#fields-preview').html(response.html);

                // راه‌اندازی Sortable بعد از رندر
             /*   if (fields.length > 0) {
                    if (sortable) {
                        sortable.destroy();
                    }
                    sortable = new Sortable(document.getElementById('fields-preview'), {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: function() {
                            // به‌روزرسانی ترتیب فیلدها
                            let newOrder = [];
                            $('#fields-preview .preview-field').each(function() {
                                let id = parseInt($(this).data('id'));
                                newOrder.push(id);
                            });

                            let sortedFields = [];
                            $.each(newOrder, function(index, id) {
                                let field = fields.find(f => f.id === id);
                                if (field) sortedFields.push(field);
                            });
                            fields = sortedFields;
                        }
                    });
                }*/

                // حذف فیلد
                $('.remove-field').on('click', function() {
                    let id = $(this).data('id');
                    fields = fields.filter(f => f.id !== id);
                    renderFields(url);
                });

            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
        });
    }

    // افزودن فیلد
    $('#add-field-btn').on('click', function() {
        if (!validateField()) return;

        let url=$(this).data('action')
        let type = $('#field-type-select').val();
        let label = $('#field-label').val();
        let name = $('#field-name').val();
        let placeholder = $('#field-placeholder').val();
        let required = $('#field-required').is(':checked');
        let helpText = $('#field-help').val();
        let defaultValue = $('#field-default').val();
        let cssClass = $('#field-class').val();
        let validation = $('#field-validation').val();

        // گرفتن گزینه‌ها
        let options = [];
        if (type === 'select' || type === 'radio' || type === 'checkbox') {
            $('.option-item input').each(function() {
                let val = $(this).val();
                if (val) options.push(val);
            });
        }

        // ایجاد فیلد جدید
        let field = {
            id: Date.now(),
            type: type,
            label: label,
            name: name,
            placeholder: placeholder,
            required: required,
            help_text: helpText,
            default_value: defaultValue,
            class: cssClass,
            validation: validation,
            options: options
        };

        fields.push(field);
        renderFields(url);
        clearFieldForm();

        toastr.success('فیلد با موفقیت اضافه شد', 'پیغام', {
            positionClass: 'toast-bottom-left',
            containerId: 'toast-bottom-left'
        });


    });

    // پاک کردن فرم فیلد
    function clearFieldForm() {
        $('.form-group .form-control').removeClass('is-valid');
        $('#field-label').val('');
        $('#field-name').val('');
        $('#field-placeholder').val('');
        $('#field-required').prop('checked', false);
        $('#field-help').val('');
        $('#field-default').val('');
        $('#field-class').val('');
        $('#field-validation').val('');
        $('#options-list').html(`
            <div class="input-group mb-2 option-item">
                <input type="text" class="form-control" placeholder="گزینه 1">
                <div class="input-group-append">
                    <button class="btn btn-danger remove-option" type="button">×</button>
                </div>
            </div>
        `);
        $('#field-type-select').trigger('change');
        $('.error-field-fields').html('');
    }

    // ذخیره فرم
    $('#main-form').submit(function(e) {
        e.preventDefault();
        if (!$(this).closest('form').valid()) return;

        var exist_fields = $('#exist-fields .preview-field');

        if (fields.length === 0 && exist_fields.length===0) {
            toastr.error('حداقل یک فیلد به فرم اضافه کنید', 'پیغام', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
            return;
        }

        $('#fields-data').val(JSON.stringify(fields));


        if ($(this).valid() && !$(this).data('disabled')) {
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(data) {
                    $('#submit-form').data('disabled', true);
                    window.location.href = BASE_URL + "/forms";
                },
                beforeSend: function(xhr) {
                    block('.content-body');
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                complete: function() {
                    unblock('.content-body');
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }

    });



    new Sortable(document.getElementById('fields-preview'), {
        group: {
            name: 'fields',
            pull: true,
            revertClone: false
        },
        animation: 150,
        onEnd: function() {
            // مرتب‌سازی آرایه fields بر اساس ترتیب DOM
            let newOrder = [];

            $('#fields-preview .preview-field').each(function() {
                let id = parseInt($(this).data('id'));
                let field = fields.find(f => f.id === id);
                if (field) {
                    newOrder.push(field);
                }
            });

            fields = newOrder;
        }
    });


});
