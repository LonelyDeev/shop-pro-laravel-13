/**
 * پنل اعلان‌های سیستم — خواندن تکی/همه با AJAX
 */
$(function () {
    'use strict';

    var R = window.NP_ROUTES || {};
    if (!R.read) return;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var $app = $('#np-app');
    var filter = $app.data('filter');

    function toast(message, type) {
        var icons = { success: 'icon-check-circle', error: 'icon-x-circle' };
        var $t = $(
            '<div class="np-toast' + (type === 'error' ? ' np-toast--error' : '') + '">' +
            '<i class="feather ' + (icons[type] || 'icon-check-circle') + '"></i>' +
            '<span>' + message + '</span></div>'
        );
        $('#np-toasts').append($t);
        requestAnimationFrame(function () { $t.addClass('np-toast--show'); });
        setTimeout(function () {
            $t.removeClass('np-toast--show');
            setTimeout(function () { $t.remove(); }, 350);
        }, 2800);
    }

    function decrementUnread(by) {
        by = by || 1;

        ['#np-stat-unread', '#np-tab-unread'].forEach(function (sel) {
            var $el = $(sel);
            if ($el.length) {
                $el.text(Math.max(0, (parseInt($el.text().replace(/[^\d]/g, ''), 10) || 0) - by).toLocaleString('en-US'));
            }
        });

        // اگر خوانده‌نشده‌ای نماند، بَج و دکمه را پنهان کن
        if (!$('#np-stat-unread').length || parseInt($('#np-stat-unread').text().replace(/[^\d]/g, ''), 10) === 0) {
            $('#np-read-all').fadeOut(200);
            $('.np-unread-badge').fadeOut(200);
            $('#np-tab-unread').fadeOut(200);
        }
    }

    // ---------- خواندن تکی ----------
    $(document).on('click', '.np-mark-read', function () {
        var $btn = $(this).prop('disabled', true);
        var id = $btn.data('id');

        $.post(R.read.replace(':id', id))
            .done(function (res) {
                var $item = $btn.closest('.np-item');

                if (filter === 'unread') {
                    // در فیلتر خوانده‌نشده، آیتم کامل حذف شود
                    $item.css({ transition: 'all .3s', opacity: 0, transform: 'translateX(-10px)' });
                    setTimeout(function () {
                        $item.remove();
                        if (!$('.np-item').length) location.reload(); // اگر لیست خالی شد
                    }, 300);
                } else {
                    $item.removeClass('np-item--unread').addClass('np-item--read');
                    $item.find('.np-dot').remove();
                    $btn.fadeOut(200, function () { $(this).remove(); });
                }

                decrementUnread(1);
                toast(res.message || 'خوانده‌شده علامت‌گذاری شد.', 'success');
            })
            .fail(function () {
                $btn.prop('disabled', false);
                toast('خطا در ثبت وضعیت!', 'error');
            });
    });

    // ---------- خواندن همه ----------
    $('#np-read-all').on('click', function () {
        var $btn = $(this).prop('disabled', true);

        $.post(R.readAll)
            .done(function (res) {
                toast(res.message || 'همه اعلان‌ها خوانده‌شده شدند.', 'success');
                setTimeout(function () { location.reload(); }, 900);
            })
            .fail(function () {
                $btn.prop('disabled', false);
                toast('خطا در انجام عملیات!', 'error');
            });
    });
});
