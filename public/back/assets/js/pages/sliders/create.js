
(function () {
    'use strict';

    function syncCounter(section) {
        var checked = section.querySelectorAll('.scs-input:checked').length;
        var subtitle = section.querySelector('.scs-subtitle');
        if (subtitle) {
            subtitle.textContent = checked + ' مورد انتخاب شده — می‌توانید چند مورد را همزمان انتخاب کنید';
        }
    }

    function bindCardClick(card) {
        var input = card.querySelector('.scs-input');
        if (!input) return;

        // وقتی کاربر روی کل کارت کلیک می‌کند، چک‌باکس toggle شود
        card.addEventListener('click', function (e) {
            // اگر خود input کلیک شد، اجازه دهیم پیش‌فرض کار کند
            if (e.target === input) return;

            e.preventDefault();
            input.checked = !input.checked;

            // trigger event برای کدهای دیگر
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        input.addEventListener('change', function () {
            card.classList.toggle('is-checked', input.checked);
            syncCounter(card.closest('.slider-checkbox-section'));
        });
    }

    function bindButtons(section) {
        var target   = section.dataset.target || section.querySelector('[data-target]')?.dataset.target;
        var selectAll = section.querySelector('.scs-btn--select-all');
        var clear     = section.querySelector('.scs-btn--clear');

        if (selectAll) {
            selectAll.addEventListener('click', function () {
                section.querySelectorAll('.scs-input').forEach(function (input) {
                    input.checked = true;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
        if (clear) {
            clear.addEventListener('click', function () {
                section.querySelectorAll('.scs-input').forEach(function (input) {
                    input.checked = false;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }
    }

    function init() {
        document.querySelectorAll('.slider-checkbox-section').forEach(function (section) {
            // دکمه‌ها داخل پارشال هستند و data-target روی خودشان می‌نشیند
            var target = section.querySelector('.scs-btn--select-all')?.dataset.target;
            if (target) section.dataset.target = target;

            section.querySelectorAll('.scs-card').forEach(bindCardClick);
            bindButtons(section);
            syncCounter(section);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

(function() {
    var input = document.getElementById('image-input');
    var wrap = document.getElementById('image-preview-wrap');
    var img = document.getElementById('image-preview');
    if (!input) return;

    input.addEventListener('change', function() {
        var file = input.files && input.files[0];
        if (!file) {
            wrap.style.display = 'none';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            wrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
})();
