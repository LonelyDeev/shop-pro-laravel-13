$('#excel-create-form').submit(function(e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(data) {
                console.log(data)
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


(function () {
    'use strict';

    // Sample data for each column key
    const sampleData = {
        title: ['گوشی سامسونگ A54', 'لپ تاپ ایسوس X515', 'هدفون بی‌سیم سونی'],
        title_en: ['Samsung Galaxy A54', 'Asus X515 Laptop', 'Sony Wireless Headphone'],
        slug: ['samsung-a54', 'asus-x515', 'sony-wh-1000'],
        brand: ['سامسونگ', 'ایسوس', 'سونی'],
        weight: ['202', '1800', '254'],
        unit: ['گرم', 'گرم', 'گرم'],
        price: ['18500000', '32000000', '12500000'],
        stock: ['25', '10', '40'],
        short_description: ['گوشی هوشمند با دوربین ۵۰', 'لپ‌تاپ سبک و قدرتمند', 'هدفون نویزگیر فعال'],
        description: ['توضیحات کامل محصول ...', 'توضیحات کامل محصول ...', 'توضیحات کامل محصول ...'],
        special: ['1', '0', '1'],
        published: ['1', '1', '0'],
        image: ['https://site.com/a.jpg', 'https://site.com/b.jpg', 'https://site.com/c.jpg'],
        meta_title: ['خرید گوشی A54', 'خرید لپ‌تاپ ایسوس', 'خرید هدفون سونی'],
        meta_description: ['بهترین قیمت گوشی A54', 'بررسی و خرید لپ‌تاپ', 'هدفون باکیفیت سونی'],
        tags: ['موبایل,سامسونگ', 'لپ‌تاپ,ایسوس', 'هدفون,سونی'],
        publish_date: ['1403/04/01', '1403/04/02', '1403/04/03'],
        category: ['گوشی', 'لپ تاپ', 'هدفون'],
        type: ['فیزیکی', 'فیزیکی', 'فیزیکی'],
    };

    const checkboxes = document.querySelectorAll('.field-checkbox');
    const letterRow = document.getElementById('preview-letters');
    const headerRow = document.getElementById('preview-headers');
    const bodyEl = document.getElementById('preview-body');
    const colCountEl = document.getElementById('column-count');

    function colLetter(index) {
        let s = '', n = index;
        while (n >= 0) {
            s = String.fromCharCode(65 + (n % 26)) + s;
            n = Math.floor(n / 26) - 1;
        }
        return s;
    }

    function renderPreview() {
        const selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                selected.push({key: cb.dataset.key, label: cb.dataset.label});
            }
        });

        // Letters row (A, B, C, ...)
        letterRow.innerHTML = selected.map((_, i) => `<th>${colLetter(i)}</th>`).join('')
            || '<th class="text-muted">حداقل یک فیلد را انتخاب کنید</th>';

        // Header labels
        headerRow.innerHTML = selected.map(c => `<th>${c.label}</th>`).join('')
            || '<th class="text-muted">—</th>';

        // 3 sample rows
        let rowsHtml = '';
        for (let r = 0; r < 3; r++) {
            rowsHtml += '<tr>';
            if (selected.length === 0) {
                rowsHtml += '<td class="text-muted">—</td>';
            } else {
                selected.forEach(c => {
                    const val = (sampleData[c.key] && sampleData[c.key][r]) ?? '';
                    rowsHtml += `<td>${val}</td>`;
                });
            }
            rowsHtml += '</tr>';
        }
        bodyEl.innerHTML = rowsHtml;

        colCountEl.textContent = `${selected.length} ستون`;
    }

    // Listen on each checkbox
    checkboxes.forEach(cb => cb.addEventListener('change', function () {
        if (this.dataset.required === '1' && !this.checked) {
            this.checked = true;
            return; // required fields can't be unchecked
        }
        renderPreview();
    }));

    // Toggle all
    const toggleBtn = document.getElementById('toggle-all-fields');
    toggleBtn.addEventListener('click', function () {
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => {
            if (cb.dataset.required === '1') {
                cb.checked = true;
                return;
            }
            cb.checked = anyUnchecked;
        });
        renderPreview();
    });

    // Dropzone behavior
    const fileInput = document.getElementById('file');
    const dropzone = document.getElementById('upload-dropzone');
    const nameDisp = document.getElementById('file-name-display');

    dropzone.addEventListener('click', function (e) {
        if (e.target.tagName !== 'BUTTON') fileInput.click();
    });
    ['dragenter', 'dragover'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('dragover');
    }));
    ['dragleave', 'drop'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
    }));
    dropzone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            nameDisp.textContent = e.dataTransfer.files[0].name;
        }
    });
    fileInput.addEventListener('change', function () {
        nameDisp.textContent = this.files.length ? this.files[0].name : 'فایلی انتخاب نشده است';
    });

    // Initial render
    renderPreview();
})();

// حذف فایل خطا با تایید
document.getElementById('delete-error-file')?.addEventListener('click', function() {
    const fileName = this.dataset.file;
    if (!fileName) return;
    if (!confirm(`آیا از حذف فایل خطا "${fileName}" مطمئن هستید؟`)) return;

    fetch(DELETE_ERROR_API, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': X_CSRF_TOKEN
        },
        body: JSON.stringify({ file: fileName })
    })
.then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('خطا در حذف فایل: ' + data.message);
            }
        })
        .catch(err => alert('خطا در ارتباط با سرور'));
});
