/**
 * مدیریت اعلان‌ها — CRUD کامل با AJAX + پیکر گیرندگان
 */
$(function () {
    'use strict';

    var R = window.NOTIF_ROUTES || {};
    if (!R.list) return;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var TYPES = {
        info:    { label: 'اطلاعیه', icon: 'icon-info',           color: '#2563EB', bg: '#EFF6FF' },
        success: { label: 'موفقیت',  icon: 'icon-check-circle',   color: '#059669', bg: '#ECFDF5' },
        warning: { label: 'هشدار',   icon: 'icon-alert-triangle', color: '#D97706', bg: '#FFFBEB' },
        danger:  { label: 'مهم',     icon: 'icon-alert-octagon',  color: '#DC2626', bg: '#FEF2F2' }
    };

    var state = { items: [], type: 'all', search: '' };

    // ---------- helpers ----------
    function esc(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getItem(batchId) {
        return state.items.filter(function (n) { return n.batch_id === batchId; })[0];
    }

    function toast(message, type) {
        var icons = { success: 'icon-check-circle', error: 'icon-x-circle', info: 'icon-info' };
        var $t = $(
            '<div class="nt-toast nt-toast--' + (type || 'success') + '">' +
            '<i class="feather ' + (icons[type] || 'icon-check-circle') + '"></i>' +
            '<span>' + esc(message) + '</span></div>'
        );
        $('#nt-toasts').append($t);
        requestAnimationFrame(function () { $t.addClass('nt-toast--show'); });
        setTimeout(function () {
            $t.removeClass('nt-toast--show');
            setTimeout(function () { $t.remove(); }, 350);
        }, 3200);
    }

    // ================================================================
    // پیکر گیرندگان (جستجو + چیپ) — برای هر کارت یک نمونه
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

        function renderResults(users) {
            if (!users.length) {
                $results.html('<div class="nt-picker__empty">کاربری یافت نشد!</div>');
                return;
            }
            $results.html(users.map(function (u) {
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
            selected[id] = { id: id, name: $item.find('.nt-picker__name').text(), mobile: $item.find('.nt-picker__mobile').text() };
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

    // فعال/غیرفعال شدن کارت با تیک
    $('.nt-target__head input').on('change', function () {
        $(this).closest('.nt-target').toggleClass('nt-target--active', this.checked);
        if (!this.checked) {
            // با برداشتن تیک، انتخاب‌ها پاک شود
            var idx = $('.nt-target').index($(this).closest('.nt-target'));
            if (pickers[idx]) pickers[idx].reset();
        }
    });

    // ---------- رندر لیست ----------
    function renderStats() {
        var recipients = 0, read = 0;
        state.items.forEach(function (n) {
            recipients += n.recipients;
            read += n.read_count;
        });
        $('#nt-stat-total').text(state.items.length.toLocaleString('en-US'));
        $('#nt-stat-recipients').text(recipients.toLocaleString('en-US'));
        $('#nt-stat-read').text(read.toLocaleString('en-US'));
        $('#nt-stat-unread').text((recipients - read).toLocaleString('en-US'));
    }

    function targetBadges(n) {
        var b = '';
        if (n.targets && n.targets.users)   b += '<span class="nt-tbadge nt-tbadge--users"><i class="feather icon-users"></i> کاربران</span> ';
        if (n.targets && n.targets.sellers) b += '<span class="nt-tbadge nt-tbadge--sellers"><i class="feather icon-shopping-bag"></i> فروشندگان</span>';
        return b || '<span class="nt-tbadge nt-tbadge--count">—</span>';
    }

    function rowTemplate(n) {
        var t = TYPES[n.type] || TYPES.info;
        var pct = n.recipients > 0 ? Math.round(n.read_count / n.recipients * 100) : 0;

        return (
            '<tr data-id="' + esc(n.batch_id) + '">' +
            '<td>' +
            '<div class="nt-title-cell">' +
            '<span class="nt-type-icon" style="color:' + t.color + ';background:' + t.bg + '"><i class="feather ' + t.icon + '"></i></span>' +
            '<span class="nt-title-text" title="' + esc(n.title) + '">' + esc(n.title) + '</span>' +
            '</div>' +
            '</td>' +
            '<td><span class="nt-msg" title="' + esc(n.message) + '">' + esc(n.message) + '</span></td>' +
            '<td><span class="nt-type-badge" style="color:' + t.color + ';background:' + t.bg + '">' + t.label + '</span></td>' +
            '<td>' +
            '<div class="nt-rc">' +
            '<span class="nt-rc__num">' + n.recipients.toLocaleString('en-US') + ' نفر</span>' +
            '<span class="nt-rc__bar"><span style="width:' + pct + '%"></span></span>' +
            '<span class="nt-rc__read">' + n.read_count.toLocaleString('en-US') + ' خوانده‌شده (' + pct + '%)</span>' +
            '</div>' +
            '</td>' +
            '<td>' + targetBadges(n) + '</td>' +
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
            if (state.type !== 'all' && n.type !== state.type) return false;
            if (state.search && (n.title + ' ' + n.message).indexOf(state.search) === -1) return false;
            return true;
        });

        if (!items.length) {
            var msg = state.items.length ? 'نتیجه‌ای برای فیلترهای فعلی یافت نشد!' : 'هنوز اعلانی ارسال نشده است!';
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
            .fail(function () { $('#nt-tbody').empty(); toast('خطا در دریافت لیست اعلان‌ها!', 'error'); });
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
            $form.find('input[name=batch_id]').val(item.batch_id);
            $form.find('input[name=title]').val(item.title);
            $form.find('textarea[name=message]').val(item.message);
            $form.find('select[name=type]').val(item.type);
            $form.find('input[name=link]').val(item.link || '');

            // گیرندگان قابل تغییر نیستند
            $('#nt-targets').hide();
            $('#nt-edit-targets').show();
            $('#nt-edit-badges').html(
                targetBadges(item) +
                ' <span class="nt-tbadge nt-tbadge--count"><i class="feather icon-users"></i> ' +
                item.recipients.toLocaleString('en-US') + ' دریافت‌کننده</span>'
            );
        } else {
            // ---- ارسال جدید ----
            $('#nt-modal-title').text('ارسال اعلان جدید');
            $('#nt-submit-btn').html('<i class="feather icon-send"></i> ارسال');
            $form.find('input[name=batch_id]').val('');

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
        $('#nt-delete-modal').data('id', item.batch_id).modal('show');
    });

    $('#nt-confirm-delete').on('click', function () {
        var id = $('#nt-delete-modal').data('id');
        var $btn = $(this).prop('disabled', true);

        $.ajax({ url: R.destroy.replace(':id', id), method: 'DELETE' })
            .done(function (res) {
                $('#nt-delete-modal').modal('hide');
                state.items = state.items.filter(function (n) { return n.batch_id !== id; });
                render();
                toast(res.message || 'اعلان حذف شد.', 'success');
            })
            .fail(function (xhr) {
                toast((xhr.responseJSON && xhr.responseJSON.message) || 'خطا در حذف!', 'error');
            })
            .always(function () { $btn.prop('disabled', false); });
    });

    // ارسال فرم
    $('#nt-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var batchId = $form.find('input[name=batch_id]').val();
        var isEdit = !!batchId;
        var $btn = $('#nt-submit-btn').prop('disabled', true);

        // اعتبارسنجی سمت کلاینت برای گروه گیرنده
        if (!isEdit && !$form.find('input[name=send_users]').prop('checked') && !$form.find('input[name=send_sellers]').prop('checked')) {
            $btn.prop('disabled', false);
            $('#nt-error-send_users').text('حداقل یکی از گروه‌های گیرنده را فعال کنید.');
            toast('گروه گیرنده را انتخاب کنید!', 'error');
            return;
        }

        $.ajax({
            url: isEdit ? R.update.replace(':id', batchId) : R.store,
            method: isEdit ? 'PATCH' : 'POST',
            data: $form.serialize()
        })
            .done(function (res) {
                $('#nt-form-modal').modal('hide');
                load(); // دریافت مجدد لیست و آمار
                toast(res.message || 'با موفقیت انجام شد.', 'success');
            })
            .fail(function (xhr) {
                if (xhr.status === 422) {
                    showErrors(xhr.responseJSON && xhr.responseJSON.errors);
                    toast('خطا در اعتبارسنجی فرم!', 'error');
                } else {
                    toast((xhr.responseJSON && xhr.responseJSON.message) || 'خطایی رخ داد!', 'error');
                }
            })
            .always(function () { $btn.prop('disabled', false); });
    });

    $('#nt-form').on('input change', '[name]', function () {
        $('#nt-error-' + this.name).text('');
    });

    // فیلتر نوع
    $('.nt-pill[data-type]').on('click', function () {
        $('.nt-pill[data-type]').removeClass('nt-pill--active');
        $(this).addClass('nt-pill--active');
        state.type = $(this).data('type');
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
