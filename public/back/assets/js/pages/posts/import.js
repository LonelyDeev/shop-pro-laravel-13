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
                    showCustomToast(data.error,'error');
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
    const colLetter = (i) => {
        let s = '';
        i = i + 1;
        while (i > 0) {
            const m = (i - 1) % 26;
            s = String.fromCharCode(65 + m) + s;
            i = Math.floor((i - 1) / 26);
        }
        return s;
    };

    const lettersRow = document.getElementById('excel-col-letters');
    const headersRow = document.getElementById('excel-col-headers');
    const bodyRows   = document.getElementById('excel-sample-rows');
    const countBadge = document.getElementById('column-count-badge');

    function renderPreview() {
        const checked = Array.from(document.querySelectorAll('.field-checkbox:checked'));

        lettersRow.innerHTML = '';
        headersRow.innerHTML = '';
        bodyRows.innerHTML   = '';

        if (checked.length === 0) {
            lettersRow.innerHTML = '<th>—</th>';
            headersRow.innerHTML = '<th class="text-muted">هیچ فیلدی انتخاب نشده است</th>';
            countBadge.textContent = '0 ستون';
            return;
        }

        checked.forEach((cb, idx) => {
            const th1 = document.createElement('th');
            th1.textContent = colLetter(idx);
            lettersRow.appendChild(th1);

            const th2 = document.createElement('th');
            th2.textContent = cb.dataset.label;
            headersRow.appendChild(th2);
        });

        for (let r = 0; r < 3; r++) {
            const tr = document.createElement('tr');
            checked.forEach((cb) => {
                const td = document.createElement('td');
                const sample = cb.dataset.sample || '';
                td.textContent = r === 0 ? sample : (sample ? sample + ' ' + (r + 1) : '');
                tr.appendChild(td);
            });
            bodyRows.appendChild(tr);
        }

        countBadge.textContent = checked.length + ' ستون';
    }

    document.querySelectorAll('.field-checkbox').forEach((cb) => {
        cb.addEventListener('change', function () {
            if (this.dataset.required === '1' && !this.checked) {
                this.checked = true;
                return;
            }
            renderPreview();
        });
    });

    const toggleBtn = document.getElementById('toggle-all-fields');
    toggleBtn.addEventListener('click', function () {
        const boxes = document.querySelectorAll('.field-checkbox');
        const allChecked = Array.from(boxes).every(b => b.checked);
        boxes.forEach(b => {
            if (b.dataset.required === '1') { b.checked = true; return; }
            b.checked = !allChecked;
        });
        renderPreview();
    });

    // Drag & drop
    const dz = document.getElementById('dropzone-area');
    const fileInput = document.getElementById('file');
    const fileName  = document.getElementById('dropzone-filename');

    ['dragenter','dragover'].forEach(ev =>
        dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.add('is-dragover'); })
    );
    ['dragleave','drop'].forEach(ev =>
        dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.remove('is-dragover'); })
    );
    dz.addEventListener('drop', (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileName.textContent = e.dataTransfer.files[0].name;
        }
    });
    fileInput.addEventListener('change', function () {
        fileName.textContent = this.files[0] ? this.files[0].name : '';
    });

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
