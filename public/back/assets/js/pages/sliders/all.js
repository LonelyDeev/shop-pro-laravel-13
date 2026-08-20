(function () {
    'use strict';

    /* ===== 1) چک‌باکس‌ها ===== */
    function syncCounter(section) {
        var checked = section.querySelectorAll('.sk-card-input:checked').length;
        var counter = section.querySelector('[data-counter]');
        if (counter) counter.textContent = checked;
    }

    function bindCard(card) {
        var input = card.querySelector('.sk-card-input');
        if (!input) return;

        card.addEventListener('click', function (e) {
            if (e.target === input) return;
            e.preventDefault();
            input.checked = !input.checked;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        input.addEventListener('change', function () {
            card.classList.toggle('is-checked', input.checked);
            syncCounter(card.closest('.sk-section'));
        });
    }

    function bindSectionButtons(section) {
        var selectAll = section.querySelector('.sk-btn-select-all');
        var clear     = section.querySelector('.sk-btn-clear');

        if (selectAll) {
            selectAll.addEventListener('click', function () {
                section.querySelectorAll('.sk-card-input').forEach(function (input) {
                    input.checked = true;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
        if (clear) {
            clear.addEventListener('click', function () {
                section.querySelectorAll('.sk-card-input').forEach(function (input) {
                    input.checked = false;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
    }

    function initCheckboxes() {
        document.querySelectorAll('.sk-section').forEach(function (section) {
            section.querySelectorAll('.sk-card').forEach(bindCard);
            bindSectionButtons(section);
            syncCounter(section);
        });
    }

    /* ===== 2) File Manager + Dropzone ===== */
    function initFileManager() {
        var button = document.getElementById('button-image');
        if (!button) return;

        button.addEventListener('click', function (event) {
            event.preventDefault();
            window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
        });

        var removeBtn = document.querySelector('.remove-img-uploader');
        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var uploader = document.querySelector('#button-image .img-uploader');
                if (uploader) uploader.style.display = 'none';

                var img = document.querySelector('#button-image img');
                if (img) img.src = '';

                document.getElementById('image_label').value = '';
                button.classList.remove('has-image');

                var empty = button.querySelector('.dz-empty');
                if (empty) empty.style.display = '';
            });
        }
    }

    // تابع سراسری که file manager صدا می‌زند
    window.fmSetLink = function ($url) {
        var button = document.getElementById('button-image');
        var input  = document.getElementById('image_label');
        if (!button || !input) return;

        input.value = $url;
        button.classList.add('has-image');

        var uploader = button.querySelector('.img-uploader');
        if (uploader) uploader.style.display = '';

        var img = button.querySelector('img');
        if (img) img.src = $url;

        var empty = button.querySelector('.dz-empty');
        if (empty) empty.style.display = 'none';
    };

    /* ===== 3) Autocomplete ===== */
    function initAutocomplete() {
        if (typeof $ === 'undefined' || typeof pages === 'undefined') return;
        $(".slider-link").autocomplete({ source: pages });
    }

    /* ===== 4) AJAX Form Submit ===== */
    function initFormSubmit() {
        if (typeof $ === 'undefined') return;

        $('#slider-edit-form').submit(function (e) {
            e.preventDefault();

            if (!$(this).data('disabled')) {
                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        $('#slider-edit-form').data('disabled', true);
                        window.location.href = BASE_URL + "/sliders";
                    },
                    beforeSend: function (xhr) {
                        block('#main-card');
                        xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    complete: function () {
                        unblock('#main-card');
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
        });
    }

    /* ===== Init ===== */
    function init() {
        initCheckboxes();
        initFileManager();
        initAutocomplete();
        initFormSubmit();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
