$(document).ready(function() {
    let sortable = null;

    // نمایش فرم بعد از آماده شدن
    $('#form-container').show();

    // راه‌اندازی Sortable برای جابجایی فیلدها
    function initSortable() {
        if (sortable) {
            sortable.destroy();
        }

        sortable = new Sortable(document.getElementById('fields-container'), {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                updateFieldsOrder();
            }
        });
    }

    // اضافه کردن دسته drag به هر فیلد
    function addDragHandles() {
        $('.field-item').each(function() {
            if (!$(this).find('.drag-handle').length) {
                $(this).prepend('<i class="fa fa-arrows-alt drag-handle"></i>');
            }
        });
    }

    addDragHandles();
    initSortable();

    // به روز رسانی ترتیب فیلدها
    function updateFieldsOrder() {
        let order = [];
        $('#fields-container .field-item').each(function(index) {
            order.push({
                id: $(this).data('id'),
                column_class: $(this).attr('class').match(/col-\S+/g).join(' ')
            });
        });

        $.ajax({
            url: $('#fields-container').data('action'),
            type: 'POST',
            data: {
                order: order,
            },
            success: function(response) {
                showCustomToast('ترتیب فیلد ها بروز شد','success');

            },
            beforeSend: function(xhr) {
                block('.nestable');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
        });
    }

    // تنظیمات فرم
    $('#save-settings').on('click', function() {
        let settings = {
            form_position: $('#form-position').val(),
            form_width: $('#form-width').val(),
            form_alignment: $('#form-alignment').val(),
            form_class: $('#form-class').val(),
            custom_css: $('#custom-css').val(),
            default_column_class: $('#default-column-class').val()
        };

        $.ajax({
            url: $('#save-settings').data('action'),
            type: 'POST',
            data: {
                ...settings,
            },
            success: function(response) {
                showCustomToast('تنظیمات فرم ذخیره شد','success');
                // به روز رسانی ظاهر فرم
                updateFormAppearance();
            },
            beforeSend: function(xhr) {
                block('.nestable');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
        });
    });

    // به روز رسانی ظاهر فرم
    updateFormAppearance()
    function updateFormAppearance() {
        let width = $('#form-width').val();
        let alignment = $('#form-alignment').val();
        let formBox = $('#form-container');

        // به روز رسانی کلاس‌ها
        formBox.removeClass('full-width half-width third-width mx-auto ms-auto me-auto');

        if (width === 'full') formBox.addClass('full-width');
        else if (width === 'half') formBox.addClass('half-width');
        else if (width === 'third') formBox.addClass('third-width');

        if (alignment === 'center') formBox.addClass('mx-auto');
        else if (alignment === 'right') formBox.addClass('ms-auto');
        else if (alignment === 'left') formBox.addClass('me-auto');
        console.log(width);
        // موقعیت فرم
        let position = $('#form-position').val();
        let descriptionBox = $('.form-description-box');

        if (position === 'top') {
            descriptionBox.before(formBox);
        } else {
            descriptionBox.after(formBox);

        }

        // اعمال CSS سفارشی
        let customCss = $('#custom-css').val();
        $('#custom-css-style').remove();
        if (customCss) {
            $('head').append('<style id="custom-css-style">' + customCss + '</style>');
        }
    }

    // رویدادهای تغییر ظاهر
    $('#form-width, #form-alignment, #form-position').on('change', updateFormAppearance);

    // تنظیمات فیلد
    $('.field-item').on('dblclick', function() {
        let fieldId = $(this).data('id');
        let currentClass = $(this).attr('class');
        let showLabel = $(this).find('label').length > 0;

        $('#current-field-id').val(fieldId);
        $('#field-show-label').prop('checked', showLabel);

        // استخراج کلاس ستون
        let columnMatch = currentClass.match(/col-\S+/g);
        if (columnMatch) {
            $('#field-column-class').val(columnMatch.join(' '));
        }

        $('#fieldSettingsModal').modal('show');
    });

    // ذخیره تنظیمات فیلد
    $('#save-field-settings').on('click', function() {
        let fieldId = $('#current-field-id').val();
        let columnClass = $('#field-column-class').val();
        let showLabel = $('#field-show-label').is(':checked');
        let wrapperClass = $('#field-wrapper-class').val();

        let $field = $(`.field-item[data-id="${fieldId}"]`);

        // به روز رسانی کلاس‌ها
        $field.removeClass().addClass('field-item ' + columnClass + ' ' + wrapperClass);

        // نمایش/مخفی کردن لیبل
        if (!showLabel) {
            $field.find('label').hide();
        } else {
            $field.find('label').show();
        }

        // ذخیره در دیتابیس
        $.ajax({
            url: $('#fields-container').data('action'),
            type: 'POST',
            data: {
                order: [{
                    id: fieldId,
                    column_class: columnClass,
                    show_label: showLabel,
                    wrapper_class: wrapperClass
                }],
            },
            success: function() {
                showCustomToast('تنظیمات فیلد ذخیره شد','success');
                $('#fieldSettingsModal').modal('hide');
            }, beforeSend: function(xhr) {
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
        });
    });
});
