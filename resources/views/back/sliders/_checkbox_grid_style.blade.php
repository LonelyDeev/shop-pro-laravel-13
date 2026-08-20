{{--
    استایل اختصاصی چک‌باکس‌های اسلایدر
    در layout اصلی یا همین viewها include کنید.
    از متغیرهای CSS استفاده شده تا تم رنگی به‌راحتی قابل تغییر باشد.
--}}

<style>
    :root {
        --scs-radius:      14px;
        --scs-radius-sm:   10px;
        --scs-shadow:      0 1px 2px rgba(15, 23, 42, .04), 0 4px 16px rgba(15, 23, 42, .06);
        --scs-shadow-on:   0 4px 14px rgba(37, 99, 235, .18);
        --scs-border:      #e2e8f0;
        --scs-border-on:   #2563eb;
        --scs-text:        #1e293b;
        --scs-text-soft:   #64748b;
        --scs-bg:          #ffffff;
        --scs-bg-soft:     #f8fafc;
    }

    .slider-checkbox-section {
        background: var(--scs-bg);
        border: 1px solid var(--scs-border);
        border-radius: var(--scs-radius);
        box-shadow: var(--scs-shadow);
        padding: 1.25rem 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        transition: box-shadow .25s ease, border-color .25s ease;
    }
    .slider-checkbox-section:hover {
        box-shadow: 0 2px 4px rgba(15,23,42,.04), 0 10px 30px rgba(15,23,42,.08);
    }

    /* هدر بخش */
    .scs-header {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px dashed var(--scs-border);
    }
    .scs-icon {
        width: 42px; height: 42px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 12px;
        font-size: 1.1rem;
        color: #fff;
        flex-shrink: 0;
    }
    .slider-checkbox-section--pages-blue .scs-icon     { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .slider-checkbox-section--groups-green .scs-icon   { background: linear-gradient(135deg, #10b981, #059669); }
    .slider-checkbox-section--pages-purple .scs-icon   { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .slider-checkbox-section--groups-orange .scs-icon  { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .scs-titles { flex: 1 1 auto; min-width: 0; }
    .scs-title {
        margin: 0; font-size: 1rem; font-weight: 700;
        color: var(--scs-text); line-height: 1.4;
    }
    .scs-subtitle {
        display: block;
        font-size: .8rem;
        color: var(--scs-text-soft);
        margin-top: 2px;
    }
    .scs-actions { display: flex; gap: .35rem; flex-shrink: 0; }
    .scs-btn {
        border: 1px solid var(--scs-border);
        background: var(--scs-bg-soft);
        color: var(--scs-text-soft);
        border-radius: 8px;
        padding: .35rem .7rem;
        font-size: .75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }
    .scs-btn:hover {
        background: #fff;
        color: var(--scs-text);
        border-color: #cbd5e1;
    }
    .scs-btn--select-all:hover { color: var(--scs-border-on); border-color: var(--scs-border-on); }

    /* گرید کارت‌ها */
    .scs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: .75rem;
    }

    .scs-card {
        position: relative;
        display: block;
        cursor: pointer;
        user-select: none;
        margin: 0;
    }
    .scs-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0; height: 0;
    }
    .scs-card-inner {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .7rem .85rem;
        border: 1.5px solid var(--scs-border);
        border-radius: var(--scs-radius-sm);
        background: var(--scs-bg-soft);
        transition: all .22s cubic-bezier(.4, 0, .2, 1);
        min-height: 52px;
    }
    .scs-card:hover .scs-card-inner {
        border-color: #cbd5e1;
        background: #fff;
        transform: translateY(-1px);
    }

    /* چک‌باکس سفارشی */
    .scs-checkbox {
        width: 20px; height: 20px;
        border-radius: 6px;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all .2s ease;
    }
    .scs-checkbox .fa {
        font-size: .65rem;
        color: #fff;
        opacity: 0;
        transform: scale(.6);
        transition: all .2s ease;
    }

    .scs-label {
        flex: 1 1 auto;
        font-size: .85rem;
        font-weight: 600;
        color: var(--scs-text);
        line-height: 1.3;
    }
    .scs-key {
        font-size: .65rem;
        color: var(--scs-text-soft);
        background: #eef2f7;
        padding: 2px 6px;
        border-radius: 5px;
        font-family: 'Vazirmatn', monospace;
        direction: ltr;
        flex-shrink: 0;
    }

    /* حالت انتخاب شده */
    .slider-checkbox-section--pages-blue   .scs-card.is-checked .scs-card-inner,
    .slider-checkbox-section--pages-purple .scs-card.is-checked .scs-card-inner {
        border-color: #2563eb;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        box-shadow: var(--scs-shadow-on);
    }
    .slider-checkbox-section--pages-blue   .scs-card.is-checked .scs-checkbox,
    .slider-checkbox-section--pages-purple .scs-card.is-checked .scs-checkbox {
        background: #2563eb;
        border-color: #2563eb;
    }

    .slider-checkbox-section--groups-green  .scs-card.is-checked .scs-card-inner,
    .slider-checkbox-section--groups-orange .scs-card.is-checked .scs-card-inner {
        border-color: #059669;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        box-shadow: 0 4px 14px rgba(5, 150, 105, .18);
    }
    .slider-checkbox-section--groups-green  .scs-card.is-checked .scs-checkbox,
    .slider-checkbox-section--groups-orange .scs-card.is-checked .scs-checkbox {
        background: #059669;
        border-color: #059669;
    }

    .scs-card.is-checked .scs-checkbox .fa {
        opacity: 1;
        transform: scale(1);
    }
    .scs-card.is-checked .scs-key {
        background: rgba(37, 99, 235, .12);
        color: #1e40af;
    }
    .slider-checkbox-section--groups-green  .scs-card.is-checked .scs-key,
    .slider-checkbox-section--groups-orange .scs-card.is-checked .scs-key {
        background: rgba(5, 150, 105, .12);
        color: #047857;
    }

    /* حالت focus برای دسترس‌پذیری */
    .scs-input:focus + .scs-card-inner {
        outline: 2px solid #60a5fa;
        outline-offset: 2px;
    }

    /* پیام خطا */
    .scs-error {
        margin-top: .85rem;
        padding: .6rem .85rem;
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    /* ریسپانسیو */
    @@media (max-width: 640px) {
        .scs-grid {
            grid-template-columns: 1fr;
        }
        .scs-header {
            flex-wrap: wrap;
        }
        .scs-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<script>
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
</script>
