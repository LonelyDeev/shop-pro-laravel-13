$(document).ready(function() {

    CKEDITOR.config.height = 400;
    CKEDITOR.replace('description');

    let fields = [];
    let sortable = null;

    // متغیرهای مودال
    let selectedType = 'text';
    let selectedTypeName = 'متن ساده';
    let selectedTypeIcon = 'fa-font';

    // ===============================================
    // اعتبارسنجی فرم اصلی
    // ===============================================
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

    // ===============================================
    // مودال ۱: انتخاب نوع فیلد
    // ===============================================

    // انتخاب کارت نوع فیلد
    $(document).on('click', '.fb-type-card', function() {
        $('.fb-type-card').removeClass('selected');
        $(this).addClass('selected');
        selectedType = $(this).data('type');
        selectedTypeName = $(this).data('name');
        selectedTypeIcon = $(this).data('icon');
        // فعال کردن دکمه ادامه
        $('#fb-continue-btn').prop('disabled', false).css({
            'opacity': 1,
            'cursor': 'pointer'
        });
    });

    // دکمه ادامه (رفتن به مودال ۲)
    $(document).on('click', '#fb-continue-btn', function() {
        if ($(this).prop('disabled')) return;
        openConfigModal();
    });

    // دابل‌کلیک روی کارت = انتخاب + ادامه
    $(document).on('dblclick', '.fb-type-card', function() {
        if ($('#fb-continue-btn').prop('disabled')) return;
        openConfigModal();
    });

    // دکمه بازگشت در مودال ۲
    $(document).on('click', '#fb-back-btn', function() {
        $('#fieldConfigModal').modal('hide');
        setTimeout(() => {
            $('#fieldTypeModal').modal('show');
        }, 350);
    });

    // دکمه تغییر نوع در مودال ۲
    $(document).on('click', '#fb-change-type', function() {
        $('#fieldConfigModal').modal('hide');
        setTimeout(() => {
            // پاک کردن انتخاب قبلی
            $('.fb-type-card').removeClass('selected');
            $('#fb-continue-btn').prop('disabled', true).css({
                'opacity': 0.5,
                'cursor': 'not-allowed'
            });
            $('#fieldTypeModal').modal('show');
        }, 350);
    });

    // باز کردن مودال ۲ (پیکربندی)
    function openConfigModal() {
        // به‌روزرسانی بنر نوع فیلد
        $('#fb-banner-icon i').attr('class', 'fa ' + selectedTypeIcon);
        $('#fb-banner-title').text(selectedTypeName);
        $('#field-type-select').val(selectedType);

        // نمایش/مخفی کردن بخش گزینه‌ها
        if (selectedType === 'select' || selectedType === 'radio' || selectedType === 'checkbox') {
            $('#options-container').show();
        } else {
            $('#options-container').hide();
        }

        // پاک کردن خطاهای قبلی
        $('.error-field-label, .error-field-name').html('');
        $('#field-label, #field-name').removeClass('is-invalid');

        // بستن مودال ۱ و باز کردن مودال ۲
        $('#fieldTypeModal').modal('hide');
        setTimeout(() => {
            $('#fieldConfigModal').modal('show');
            // فوکوس روی اولین فیلد
            setTimeout(() => $('#field-label').focus(), 300);
        }, 350);
    }

    // زمان باز شدن مودال ۱ از دکمه اصلی، ریست شود
    $('[data-target="#fieldTypeModal"]').on('click', function() {
        // فقط اگر مودال ۲ باز نباشد (یعنی از دکمه اصلی آمده)
        if (!$('#fieldConfigModal').hasClass('show')) {
            $('.fb-type-card').removeClass('selected');
            $('#fb-continue-btn').prop('disabled', true).css({
                'opacity': 0.5,
                'cursor': 'not-allowed'
            });
            clearFieldForm();
        }
    });

    // زمان بسته شدن کامل مودال ۲، فرم را پاک کن
    $('#fieldConfigModal').on('hidden.bs.modal', function() {
        // اگر مودال ۱ هم بسته شده، فرم را پاک کن
        if (!$('#fieldTypeModal').hasClass('show')) {
            clearFieldForm();
        }
    });

    // ===============================================
    // مدیریت گزینه‌ها
    // ===============================================
    $(document).on('click', '#add-option', function() {
        let count = $('.option-item').length + 1;
        let newOption = `
            <div class="input-group mb-2 option-item">
                <input type="text" class="form-control" placeholder="گزینه ${count}">
                <div class="input-group-append">
                    <button class="btn btn-danger remove-option" type="button">×</button>
                </div>
            </div>
        `;
        $('#options-list').append(newOption);
    });

    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-item').remove();
    });

    // ===============================================
    // اعتبارسنجی فیلد
    // ===============================================
    function validateField() {
        let isValid = true;
        let label = $('#field-label').val();
        let name = $('#field-name').val();
        let type = $('#field-type-select').val();

        $('.error-field-label, .error-field-name').html('');
        $('#field-label, #field-name').removeClass('is-invalid');

        if (!label) {
            $('.error-field-label').text('عنوان فیلد الزامی است');
            $('#field-label').addClass('is-invalid');
            isValid = false;
        }

        if (!name) {
            $('.error-field-name').html('نام فیلد الزامی است');
            $('#field-name').addClass('is-invalid');
            isValid = false;
        } else if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(name)) {
            $('.error-field-name').html('نام فیلد باید با حرف انگلیسی یا زیرخط شروع شود و فقط شامل حروف، اعداد و زیرخط باشد');
            $('#field-name').addClass('is-invalid');
            isValid = false;
        } else if (fields.some(f => f.name === name)) {
            $('.error-field-name').html('این نام فیلد قبلاً استفاده شده است');
            $('#field-name').addClass('is-invalid');
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

    // ===============================================
    // رندر فیلدها
    // ===============================================
    function renderFields(url) {
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                fields: JSON.stringify(fields),
            },
            success: function(response) {
                $('#fields-preview').html(response.html);

                // حذف فیلد
                $('.remove-field').off('click').on('click', function() {
                    let id = parseInt($(this).data('id'));
                    fields = fields.filter(f => f.id !== id);
                    renderFields(url);
                });
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
        });
    }

    // ===============================================
    // افزودن فیلد
    // ===============================================
    $('#add-field-btn').on('click', function() {
        if (!validateField()) return;

        let url = $(this).data('action');
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

        // بستن مودال ۲
        $('#fieldConfigModal').modal('hide');
        showCustomToast('فیلد با موفقیت اضافه شد','success');

    });

    // ===============================================
    // پاک کردن فرم فیلد
    // ===============================================
    function clearFieldForm() {
        $('#field-label').val('').removeClass('is-valid is-invalid');
        $('#field-name').val('').removeClass('is-valid is-invalid');
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

        // ریست به متن ساده
        selectedType = 'text';
        selectedTypeName = 'متن ساده';
        selectedTypeIcon = 'fa-font';
        $('#field-type-select').val('text');
        $('#fb-banner-icon i').attr('class', 'fa fa-font');
        $('#fb-banner-title').text('متن ساده');
        $('#options-container').hide();

        $('.error-field-label, .error-field-name').html('');
    }

    // ===============================================
    // ذخیره فرم
    // ===============================================
    $('#main-form').submit(function(e) {
        e.preventDefault();
        if (!$(this).closest('form').valid()) return;

        var exist_fields = $('#exist-fields .preview-field');

        if (fields.length === 0 && exist_fields.length === 0) {
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

    // ===============================================
    // Sortable
    // ===============================================
    if (document.getElementById('fields-preview')) {
        new Sortable(document.getElementById('fields-preview'), {
            group: {
                name: 'fields',
                pull: true,
                revertClone: false
            },
            animation: 150,
            onEnd: function() {
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
    }
});
