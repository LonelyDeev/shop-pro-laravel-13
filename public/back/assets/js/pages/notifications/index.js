/**
 * مدیریت اعلان‌ها — CRUD کامل با AJAX (جدول‌های notification_manages)
 */
$(function () {
    'use strict';

    var R = window.NOTIF_ROUTES || {};
    if (!R.list) return;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var PRIORITIES = {
        high:   { label: 'فوری',   icon: 'icon-alert-octagon',  color: '#DC2626', bg: '#FEF2F2' },
        medium: { label: 'متوسط',  icon: 'icon-alert-triangle', color: '#D97706', bg: '#FFFBEB' },
        low:    { label: 'عادی',   icon: 'icon-info',           color: '#059669', bg: '#ECFDF5' }
    };

    var state = { items: [], priority: 'all', search: '' };

    // ---------- helpers ----------
    function esc(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getItem(id) {
        return state.items.filter(function (n) { return String(n.id) === String(id); })[0];
    }


    // ================================================================
    // پیکر گیرندگان (جستجوی AJAX + چیپ‌ها)
    // ================================================================
    function initPicker($target) {
        var group    = $target.find('.nt-picker').data('group');
        var $picker  = $target.find('.nt-picker');
        var $search  = $picker.find('.nt-picker__search');
        var $results = $picker.find('.nt-picker__results');
        var $chips   = $target.find('.nt-chips');
        var selected = {};
        var timer    = null;

        var inputName = group === 'sellers' ? 'seller_ids' : 'user_ids';

        function renderChips() {
            $chips.empty();
            $picker.find('input[type=hidden]').remove();

            $.each(selected, function (id, u) {
                $chips.append(
                    '<span class="nt-chip" data-id="' + id + '">' +
                    '<span class="nt-chip__avatar">' + esc(String(u.name).charAt(0)) + '</span>' +
                    esc(u.name) +
                    '<button type="button" class="nt-chip__remove" title="حذف"><i class="feather icon-x"></i></button>' +
                    '</span>'
                );
                $picker.append('<input type="hidden" name="' + inputName + '[]" value="' + id + '">');
            });
        }

        $chips.on('click', '.nt-chip__remove', function () {
            delete selected[$(this).closest('.nt-chip').data('id')];
            renderChips();
        });

        function renderResults(rows) {
            if (!rows.length) {
                $results.html('<div class="nt-picker__empty">موردی یافت نشد!</div>');
                return;
            }
            $results.html(rows.map(function (u) {
                var isSel = !!selected[u.id];
                return (
                    '<button type="button" class="nt-picker__item' + (isSel ? ' nt-picker__item--selected' : '') + '" data-id="' + u.id + '"' + (isSel ? ' disabled' : '') + '>' +
                    '<span class="nt-picker__avatar">' + esc(String(u.name).charAt(0)) + '</span>' +
                    '<span>' +
                    '<span class="nt-picker__name">' + esc(u.name) + '</span>' +
                    '<span class="nt-picker__mobile">' + esc(u.mobile || '') + '</span>' +
                    '</span>' +
                    (isSel ? '<i class="feather icon-check nt-picker__status"></i>' : '') +
                    '</button>'
                );
            }).join(''));
        }

        function search() {
            $picker.addClass('nt-picker--loading');
            $.getJSON(R.recipients, { group: group, q: $search.val().trim() })
                .done(function (res) { renderResults(res.data || []); })
                .fail(function () { renderResults([]); })
                .always(function () {
                    $picker.removeClass('nt-picker--loading');
                    $picker.addClass('nt-picker--open');
                });
        }

        $search.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(search, 300);
        });

        $search.on('focus', function () { search(); });

        $results.on('click', '.nt-picker__item:not(.nt-picker__item--selected)', function () {
            var $item = $(this);
            var id = $item.data('id');
            selected[id] = {
                id: id,
                name: $item.find('.nt-picker__name').text(),
                mobile: $item.find('.nt-picker__mobile').text()
            };
            renderChips();
            $search.val('');
            $picker.removeClass('nt-picker--open');
        });

        return {
            reset: function () {
                selected = {};
                renderChips();
                $search.val('');
                $picker.removeClass('nt-picker--open');
            }
        };
    }

    var pickers = [];
    $('.nt-target').each(function () { pickers.push(initPicker($(this))); });

    // بستن نتایج با کلیک بیرون
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.nt-picker').length) {
            $('.nt-picker').removeClass('nt-picker--open');
        }
    });

    // فعال/غیرفعال شدن کارت گیرنده
    $('.nt-target__head input').on('change', function () {
        var $target = $(this).closest('.nt-target');
        $target.toggleClass('nt-target--active', this.checked);
        if (!this.checked) {
            var idx = $('.nt-target').index($target);
            if (pickers[idx]) pickers[idx].reset();
        }
    });

    // ---------- رندر ----------
    function renderStats() {
        var popup = 0, broadcast = 0, high = 0;
        state.items.forEach(function (n) {
            if (n.popup) popup++;
            if ((n.targets.users && n.targets.users.mode === 'all') ||
                (n.targets.sellers && n.targets.sellers.mode === 'all')) broadcast++;
            if (n.priority === 'high') high++;
        });
        $('#nt-stat-total').text(state.items.length.toLocaleString('en-US'));
        $('#nt-stat-popup').text(popup.toLocaleString('en-US'));
        $('#nt-stat-broadcast').text(broadcast.toLocaleString('en-US'));
        $('#nt-stat-high').text(high.toLocaleString('en-US'));
    }

    function targetBadges(n) {
        var b = '';
        if (n.targets.users) {
            b += '<span class="nt-tbadge nt-tbadge--users"><i class="feather icon-users"></i> ' +
                (n.targets.users.mode === 'all' ? 'همه کاربران' : 'کاربران انتخابی') +
                ' (' + n.targets.users.count.toLocaleString('en-US') + ')</span> ';
        }
        if (n.targets.sellers) {
            b += '<span class="nt-tbadge nt-tbadge--sellers"><i class="feather icon-shopping-bag"></i> ' +
                (n.targets.sellers.mode === 'all' ? 'همه فروشندگان' : 'فروشندگان انتخابی') +
                ' (' + n.targets.sellers.count.toLocaleString('en-US') + ')</span>';
        }
        return b || '<span class="nt-tbadge nt-tbadge--read">—</span>';
    }

    function rowTemplate(n) {
        var p = PRIORITIES[n.priority] || PRIORITIES.low;

        return (
            '<tr data-id="' + n.id + '">' +
            '<td>' +
            '<div class="nt-title-cell">' +
            '<span class="nt-pr-icon" style="color:' + p.color + ';background:' + p.bg + '"><i class="feather ' + p.icon + '"></i></span>' +
            '<span class="nt-title-text" title="' + esc(n.title) + '">' + esc(n.title) + '</span>' +
            '</div>' +
            '</td>' +
            '<td><span class="nt-msg" title="' + esc(n.message) + '">' + esc(n.message) + '</span></td>' +
            '<td><span class="nt-pr-badge" style="color:' + p.color + ';background:' + p.bg + '">' + p.label + '</span></td>' +
            '<td class="text-center">' +
            '<label class="nt-switch">' +
            '<input type="checkbox"' + (n.popup ? ' checked' : '') + '>' +
            '<span class="nt-switch__slider"></span>' +
            '</label>' +
            '</td>' +
            '<td><div class="nt-tbadges">' + targetBadges(n) +
            (n.read_count ? '<span class="nt-tbadge nt-tbadge--read"><i class="feather icon-eye"></i> ' + n.read_count.toLocaleString('en-US') + ' خوانده</span>' : '') +
            '</div></td>' +
            '<td><span class="nt-time" title="' + esc(n.date) + '"><i class="feather icon-clock"></i>' + esc(n.ago) + '</span></td>' +
            '<td class="text-center">' +
            '<div class="nt-actions">' +
            '<button type="button" class="nt-act nt-act--edit" title="ویرایش"><i class="feather icon-edit-2"></i></button>' +
            '<button type="button" class="nt-act nt-act--del" title="حذف"><i class="feather icon-trash-2"></i></button>' +
            '</div>' +
            '</td>' +
            '</tr>'
        );
    }

    function renderRows() {
        var items = state.items.filter(function (n) {
            if (state.priority !== 'all' && n.priority !== state.priority) return false;
            if (state.search && (n.title + ' ' + n.message).indexOf(state.search) === -1) return false;
            return true;
        });

        if (!items.length) {
            var msg = state.items.length ? 'نتیجه‌ای برای فیلترهای فعلی یافت نشد!' : 'هنوز اعلانی ایجاد نشده است!';
            $('#nt-tbody').html(
                '<tr><td colspan="7"><div class="nt-empty">' +
                '<div class="nt-empty__icon"><i class="feather icon-inbox"></i></div>' +
                '<h5>' + msg + '</h5></div></td></tr>'
            );
            return;
        }

        $('#nt-tbody').html(items.map(rowTemplate).join(''));
    }

    function render() { renderStats(); renderRows(); }

    function load() {
        $('#nt-tbody').html('<tr><td colspan="7" class="nt-loading"><span class="nt-spinner"></span>در حال بارگذاری…</td></tr>');

        $.getJSON(R.list)
            .done(function (res) { state.items = res.data || []; render(); })
            .fail(function () { $('#nt-tbody').empty(); showCustomToast('خطا در دریافت لیست اعلان‌ها!', 'error'); });
    }

    // ---------- مودال فرم ----------
    function openFormModal(item) {
        var $form = $('#nt-form');
        $form[0].reset();
        $form.find('.nt-error').text('');

        if (item) {
            // ---- ویرایش ----
            $('#nt-modal-title').text('ویرایش اعلان');
            $('#nt-submit-btn').html('<i class="feather icon-save"></i> ذخیره تغییرات');
            $form.find('input[name=id]').val(item.id);
            $form.find('input[name=title]').val(item.title);
            $form.find('textarea[name=message]').val(item.message);
            $form.find('select[name=priority]').val(item.priority);
            $form.find('input[name=popup]').prop('checked', !!item.popup);

            $('#nt-targets').hide();
            $('#nt-edit-targets').show();
            $('#nt-edit-badges').html(targetBadges(item));
        } else {
            // ---- ایجاد ----
            $('#nt-modal-title').text('اعلان جدید');
            $('#nt-submit-btn').html('<i class="feather icon-send"></i> ذخیره و ارسال');
            $form.find('input[name=id]').val('');

            $('#nt-targets').show();
            $('#nt-edit-targets').hide();

            // ریست کارت‌ها و پیکرها
            $('.nt-target__head input').prop('checked', false).trigger('change');
            pickers.forEach(function (p) { p.reset(); });
        }

        $('#nt-form-modal').modal('show');
    }

    function showErrors(errors) {
        $('#nt-form').find('.nt-error').text('');
        $.each(errors || {}, function (field, messages) {
            $('#nt-error-' + field).text(messages[0]);
        });
    }

    // ---------- رویدادها ----------
    $('#nt-add-btn').on('click', function () { openFormModal(null); });

    $('#nt-tbody').on('click', '.nt-act--edit', function () {
        openFormModal(getItem($(this).closest('tr').data('id')));
    });

    // حذف
    $('#nt-tbody').on('click', '.nt-act--del', function () {
        var item = getItem($(this).closest('tr').data('id'));
        if (!item) return;
        $('#nt-delete-title').text(item.title);
        $('#nt-delete-modal').data('id', item.id).modal('show');
    });

    $('#nt-confirm-delete').on('click', function () {
        var id = $('#nt-delete-modal').data('id');
        var $btn = $(this).prop('disabled', true);

        $.ajax({ url: R.destroy.replace(':id', id), method: 'DELETE' })
            .done(function (res) {
                $('#nt-delete-modal').modal('hide');
                state.items = state.items.filter(function (n) { return String(n.id) !== String(id); });
                render();
                showCustomToast(res.message || 'اعلان حذف شد.', 'success');
            })
            .fail(function (xhr) {
                showCustomToast((xhr.responseJSON && xhr.responseJSON.message) || 'خطا در حذف!', 'error');
            })
            .always(function () { $btn.prop('disabled', false); });
    });

    // سوییچ پاپ‌آپ — AJAX
    $('#nt-tbody').on('change', '.nt-switch input', function () {
        var $input = $(this);
        var id = $input.closest('tr').data('id');

        $input.prop('disabled', true);

        $.ajax({ url: R.togglePopup.replace(':id', id), method: 'PATCH' })
            .done(function (res) {
                var item = getItem(id);
                if (item) item.popup = res.popup;
                renderStats();
                showCustomToast(res.message, 'success');
            })
            .fail(function () {
                $input.prop('checked', !$input.prop('checked'));
                showCustomToast('خطا در تغییر وضعیت پاپ‌آپ!', 'error');
            })
            .always(function () { $input.prop('disabled', false); });
    });

    // ارسال فرم
    $('#nt-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var id = $form.find('input[name=id]').val();
        var isEdit = !!id;
        var $btn = $('#nt-submit-btn').prop('disabled', true);

        // اعتبارسنجی گروه گیرنده (فقط هنگام ایجاد)
        if (!isEdit &&
            !$form.find('input[name=send_users]').prop('checked') &&
            !$form.find('input[name=send_sellers]').prop('checked')) {
            $btn.prop('disabled', false);
            $('#nt-error-send_users').text('حداقل یکی از گروه‌های گیرنده را فعال کنید.');
            showCustomToast('گروه گیرنده را انتخاب کنید!', 'warning');
            return;
        }

        $.ajax({
            url: isEdit ? R.update.replace(':id', id) : R.store,
            method: isEdit ? 'PATCH' : 'POST',
            data: $form.serialize()
        })
            .done(function (res) {
                $('#nt-form-modal').modal('hide');
                load();
                showCustomToast(res.message || 'با موفقیت انجام شد.', 'success');
            })
            .fail(function (xhr) {

            })
            .always(function () { $btn.prop('disabled', false); });
    });

    $('#nt-form').on('input change', '[name]', function () {
        $('#nt-error-' + this.name).text('');
    });

    // فیلتر اولویت
    $('.nt-pill[data-type]').on('click', function () {
        $('.nt-pill[data-type]').removeClass('nt-pill--active');
        $(this).addClass('nt-pill--active');
        state.priority = $(this).data('type');
        renderRows();
    });

    // جستجو
    var searchTimer = null;
    $('#nt-search').on('input', function () {
        var value = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            state.search = value.trim();
            renderRows();
        }, 250);
    });

    $('#nt-form-modal').on('hidden.bs.modal', function () {
        $('#nt-form').find('.nt-error').text('');
    });

    load();

});
