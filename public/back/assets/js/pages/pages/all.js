// افکت هاور برای کارت‌های ویجت
$(document).ready(function() {
    $('.widget-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px)').css('box-shadow', '0 10px 20px rgba(79, 70, 229, 0.15)');
        },
        function() {
            $(this).css('transform', 'translateY(0)').css('box-shadow', '0 4px 6px -1px rgba(0, 0, 0, 0.1)');
        }
    );
});

// اسکریپت کپی کردن شورتکد
$(document).on('click', '.btn-copy-shortcode', function() {
    var shortcode = $(this).data('shortcode');
    var $btn = $(this);

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(shortcode).select();

    try {
        document.execCommand("copy");

        var originalHtml = $btn.html();
        var originalClass = $btn.attr('class');

        $btn.removeClass('btn-primary btn-info').addClass('btn-success');
        $btn.html('<i class="fas fa-check"></i> کپی شد!');

        if (typeof toastr !== 'undefined') {
            showCustomToast('شورتکد کپی شد: ' + shortcode, 'موفقیت','success');
        }

        setTimeout(function() {
            $btn.html(originalHtml);
            $btn.attr('class', originalClass);
        }, 2000);

    } catch(err) {
        alert('کپی نشد، لطفا دستی کپی کنید: ' + shortcode);
    }

    $temp.remove();
});

CKEDITOR.on('instanceReady', function(ev) {
    ev.editor.dataProcessor.htmlFilter.addRules({
        elements: {
            'div': function(element) {
                if (!element.attributes.class) return element;

                var classes = element.attributes.class;

                // 1. بررسی برای شورتکد فرم (با شناسه)
                if (classes.includes('shortcode-form')) {
                    var formId = classes.match(/shortcode-form-(\d+)/);
                    if (formId) {
                        element.children = [];
                        element.add({
                            type: CKEDITOR.NODE_TEXT,
                            value: '[form-' + formId[1] + ']'
                        });
                        return element;
                    }
                }

                // 2. بررسی برای شورتکد ویجت‌ها (عمومی - بدون شناسه)
                if (classes.includes('shortcode-widget')) {
                    var widgetKey = classes.match(/shortcode-widget-([a-zA-Z0-9_-]+)/);
                    if (widgetKey) {
                        element.children = [];
                        element.add({
                            type: CKEDITOR.NODE_TEXT,
                            value: '[widget-' + widgetKey[1] + ']'
                        });
                    }
                }

                return element;
            }
        }
    });
});

(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }
    ready(function () {
        var modal = document.getElementById('widgetsGuideModal');
        if (!modal) return;

        var searchInput    = modal.querySelector('.wg-search-input');
        var cardCols       = Array.prototype.slice.call(modal.querySelectorAll('.wg-card-col'));
        var chips          = Array.prototype.slice.call(modal.querySelectorAll('.wg-chip'));
        var emptyState     = modal.querySelector('.wg-empty-state');
        var footerCountNum = modal.querySelector('.wg-footer-count-num');
        var toast          = document.querySelector('.wg-toast');
        var currentCat     = 'all';
        var currentSearch  = '';
        var toastTimer     = null;

        function applyFilters() {
            var visible = 0;
            cardCols.forEach(function (col) {
                var cat = col.getAttribute('data-category');
                var hay = (col.getAttribute('data-search') || '').toString();
                var matchCat    = (currentCat === 'all') || (cat === currentCat);
                var matchSearch = !currentSearch || hay.indexOf(currentSearch) > -1;
                if (matchCat && matchSearch) {
                    col.classList.remove('d-none');
                    visible++;
                } else {
                    col.classList.add('d-none');
                }
            });
            if (emptyState) emptyState.classList.toggle('d-none', visible !== 0);
            if (footerCountNum) footerCountNum.textContent = visible;
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentSearch = this.value.trim().toLowerCase();
                applyFilters();
            });
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                currentCat = chip.getAttribute('data-filter');
                applyFilters();
            });
        });

        // Reset filters whenever the modal is reopened
        if (window.jQuery) {
            jQuery('#widgetsGuideModal').on('shown.bs.modal', function () {
                if (searchInput) searchInput.value = '';
                currentSearch = '';
                chips.forEach(function (c) { c.classList.remove('active'); });
                if (chips[0]) chips[0].classList.add('active');
                currentCat = 'all';
                applyFilters();
            });
        }

        // ---- Copy with feedback ----
        function fallbackCopy(text) {
            var t = document.createElement('textarea');
            t.value = text;
            t.style.position = 'fixed';
            t.style.opacity = '0';
            document.body.appendChild(t);
            t.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(t);
        }
        function showToast() {
            if (!toast) return;
            toast.classList.add('wg-toast-show');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(function () { toast.classList.remove('wg-toast-show'); }, 1800);
        }
        function markCopied(btn) {
            var label = btn.querySelector('span');
            var icon  = btn.querySelector('i');
            var origLabel = label ? label.textContent : '';
            var origIcon  = icon  ? icon.className      : '';
            btn.classList.add('wg-copied');
            if (icon)  icon.className = 'fas fa-check';
            if (label) label.textContent = 'کپی شد ✓';
            setTimeout(function () {
                btn.classList.remove('wg-copied');
                if (icon)  icon.className = origIcon;
                if (label) label.textContent = origLabel;
            }, 1600);
        }
        function doCopy(text, btn) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(function () { markCopied(btn); showToast(); })
                    .catch(function () { fallbackCopy(text); markCopied(btn); showToast(); });
            } else {
                fallbackCopy(text);
                markCopied(btn);
                showToast();
            }
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.wg-copy-btn');
            if (!btn || !modal.contains(btn)) return;
            doCopy(btn.getAttribute('data-shortcode') || '', btn);
        });
    });
})();



// افکت هاور برای کارت‌های ویجت
$(document).ready(function() {
    $('.widget-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px)').css('box-shadow', '0 10px 20px rgba(79, 70, 229, 0.15)');
        },
        function() {
            $(this).css('transform', 'translateY(0)').css('box-shadow', '0 4px 6px -1px rgba(0, 0, 0, 0.1)');
        }
    );
});

// اسکریپت کپی کردن شورتکد
$(document).on('click', '.btn-copy-shortcode', function() {
    var shortcode = $(this).data('shortcode');
    var $btn = $(this);

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(shortcode).select();

    try {
        document.execCommand("copy");

        var originalHtml = $btn.html();
        var originalClass = $btn.attr('class');

        $btn.removeClass('btn-primary btn-info').addClass('btn-success');
        $btn.html('<i class="fas fa-check"></i> کپی شد!');

        if (typeof toastr !== 'undefined') {
            showCustomToast('شورتکد کپی شد: ' + shortcode, 'موفقیت','success');
        }

        setTimeout(function() {
            $btn.html(originalHtml);
            $btn.attr('class', originalClass);
        }, 2000);

    } catch(err) {
        alert('کپی نشد، لطفا دستی کپی کنید: ' + shortcode);
    }

    $temp.remove();
});

CKEDITOR.on('instanceReady', function(ev) {
    ev.editor.dataProcessor.htmlFilter.addRules({
        elements: {
            'div': function(element) {
                if (!element.attributes.class) return element;

                var classes = element.attributes.class;

                // 1. بررسی برای شورتکد فرم (با شناسه)
                if (classes.includes('shortcode-form')) {
                    var formId = classes.match(/shortcode-form-(\d+)/);
                    if (formId) {
                        element.children = [];
                        element.add({
                            type: CKEDITOR.NODE_TEXT,
                            value: '[form-' + formId[1] + ']'
                        });
                        return element;
                    }
                }

                // 2. بررسی برای شورتکد ویجت‌ها (عمومی - بدون شناسه)
                if (classes.includes('shortcode-widget')) {
                    var widgetKey = classes.match(/shortcode-widget-([a-zA-Z0-9_-]+)/);
                    if (widgetKey) {
                        element.children = [];
                        element.add({
                            type: CKEDITOR.NODE_TEXT,
                            value: '[widget-' + widgetKey[1] + ']'
                        });
                    }
                }

                return element;
            }
        }
    });
});
