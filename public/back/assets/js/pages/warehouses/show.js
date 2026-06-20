/**
 * warehouses/show.js
 * صفحه نمایش انبار — JS یکپارچه و تمیز
 */

'use strict';

// ============================================================
// ۱. نمودارها
// ============================================================
(function initCharts() {
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx && typeof topProducts !== 'undefined' && topProducts.length > 0) {
        new Chart(topProductsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: topProducts.map(p =>
                    p.name?.length > 18 ? p.name.substring(0, 18) + '…' : (p.name || 'نامشخص')
                ),
                datasets: [{
                    label: 'تعداد فروش',
                    data: topProducts.map(p => p.sold || 0),
                    backgroundColor: 'rgba(37,99,235,.45)',
                    borderColor: 'rgba(37,99,235,1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {legend: {position: 'top'}},
                scales: {y: {beginAtZero: true}}
            }
        });
    }

    const monthlySalesCtx = document.getElementById('monthlySalesChart');
    if (monthlySalesCtx && typeof monthlySales !== 'undefined' && monthlySales.length > 0) {
        new Chart(monthlySalesCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: monthlySales.map(m => m.month || ''),
                datasets: [{
                    label: 'تعداد فروش',
                    data: monthlySales.map(m => m.total_sold || 0),
                    fill: true,
                    backgroundColor: 'rgba(75,192,192,.15)',
                    borderColor: 'rgba(75,192,192,1)',
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(75,192,192,1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {legend: {position: 'top'}},
                scales: {y: {beginAtZero: true}}
            }
        });
    }
})();


// ============================================================
// ۲. لیست محصولات (jQuery)
// ============================================================
$(function() {
    let currentPage = 1;
    let isLoading = false;
    let searchDelay = null;

    function loadProducts() {
        if (isLoading) return;
        isLoading = true;
        $('#products-container').html(
            '<div class="text-center py-4"><i class="feather icon-loader fa-spin fa-2x text-muted"></i><p class="text-muted mt-2 small">در حال بارگذاری...</p></div>'
        );
        $.ajax({
            url: warehousesProducts,
            type: 'GET',
            data: {
                search: $('#search-product').val(),
                stock_status: $('#stock-filter').val(),
                sort_by: $('#sort-filter').val(),
                page: currentPage
            },
            success: function(res) {
                if (res.html) {
                    $('#products-container').html(res.html);
                    bindPagination();
                } else {
                    $('#products-container').html(
                        '<div class="alert alert-warning">هیچ داده‌ای یافت نشد</div>'
                    );
                }
            },
            error: function(xhr) {
                let msg = 'خطا در بارگذاری اطلاعات';
                if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                else if (xhr.status === 404) msg = 'صفحه مورد نظر یافت نشد';
                else if (xhr.status === 500) msg = 'خطای سرور، لطفاً مجدداً تلاش کنید';
                $('#products-container').html('<div class="alert alert-danger">' + msg + '</div>');
            },
            complete: function() {
                isLoading = false;
            }
        });
    }

    function bindPagination() {
        $('.pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const page = url ? new URL(url).searchParams.get('page') : null;
            if (page) {
                currentPage = parseInt(page);
                loadProducts();
                $('html, body').animate({scrollTop: $('#products-container').offset().top - 80}, 250);
            }
        });
    }

    // رویدادها
    $('#search-product').on('input', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(() => {
            currentPage = 1;
            loadProducts();
        }, 400);
    });
    $('#stock-filter, #sort-filter').on('change', function() {
        currentPage = 1;
        loadProducts();
    });
    $('#filter-btn').on('click', function() {
        currentPage = 1;
        loadProducts();
    });
    $('#search-product').on('keypress', function(e) {
        if (e.which === 13) {
            currentPage = 1;
            loadProducts();
        }
    });
    $('#refresh-btn').on('click', function() {
        $('#search-product').val('');
        $('#stock-filter').val('all');
        $('#sort-filter').val('title_asc');
        currentPage = 1;
        loadProducts();
        toastr.info('فیلترها بازنشانی شد');
    });

    // خروجی‌ها
    $('#export-submit').on('click', function() {
        $('#export-form').submit();
        $('#exportExcelModal').modal('hide');
    });
    $('#pdf-submit').on('click', function() {
        $('#pdf-form').submit();
        $('#exportPdfModal').modal('hide');
    });

    // bind اولیه pagination
    bindPagination();
});


// ============================================================
// ۳. بروزرسانی گروهی موجودی (BSM)
// ============================================================
(function() {

    // ===== state =====
    let bsmPage = 1;
    let bsmSearch = '';
    let bsmSearchTimer = null;
    let bsmChanges = {};        // { priceId: { priceId, currentStock, newStock } }
    let bsmSelected = new Set(); // همه صفحات

    // ===== helpers =====
    const el = id => document.getElementById(id);

    function bsmLoadPage(page = 1) {
        bsmPage = page;
        el('bsm-loading').style.display = 'block';
        el('bsm-content').innerHTML = '';
        el('bsm-empty-state').style.display = 'none';
        el('bsm-pagination').style.display = 'none';

        const params = new URLSearchParams({page});
        if (bsmSearch) params.set('search', bsmSearch);

        fetch(`${bulkStockDataAPI}?${params}`, {
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}
        })
            .then(r => r.json())
            .then(data => {
                el('bsm-loading').style.display = 'none';
                bsmRenderContent(data.variations);
            })
            .catch(() => {
                el('bsm-loading').style.display = 'none';
                el('bsm-content').innerHTML =
                    '<div class="bsm-loading" style="color:#dc2626;">خطا در بارگذاری</div>';
            });
    }

    function bsmRenderContent(paginated) {
        const items = paginated.data || [];
        if (!items.length) {
            el('bsm-empty-state').style.display = 'block';
            return;
        }

        // گروه‌بندی بر product_id
        const groups = {};
        items.forEach(price => {
            const pid = price.product_id;
            if (!groups[pid]) groups[pid] = {product: price.product, variations: []};
            groups[pid].variations.push(price);
        });

        const frag = document.createDocumentFragment();
        Object.values(groups).forEach(g => frag.appendChild(bsmBuildCard(g)));
        el('bsm-content').innerHTML = '';
        el('bsm-content').appendChild(frag);

        bsmRestoreState();
        bsmRenderPagination(paginated);
        bsmSyncSelectAll();
    }

    function bsmBuildCard(group) {
        const card = document.createElement('div');
        card.className = 'bsm-product-card';
        const p = group.product || {};

        const imgHtml = p.image
            ? `<img src="${FRONT_URL + '/' + p.image}" class="bsm-product-thumb" alt="">`
            : `<div class="bsm-product-thumb-ph"><i class="feather icon-box"></i></div>`;

        const brandChip = p.brand?.name
            ? `<span class="bsm-chip bsm-chip-primary"><i class="feather icon-tag" style="font-size:.6rem;"></i>${p.brand.name}</span>` : '';
        const catChip = p.category?.title
            ? `<span class="bsm-chip bsm-chip-info"><i class="feather icon-folder" style="font-size:.6rem;"></i>${p.category.title}</span>` : '';

        card.innerHTML = `
            <div class="bsm-product-head">
                <input type="checkbox" class="product-select-all"
                       data-product-id="${p.id}" title="انتخاب همه تنوع‌های این محصول">
                ${imgHtml}
                <div class="bsm-product-info">
                    <div class="bsm-product-name">${p.title || '—'}</div>
                    <div class="bsm-product-meta">
                        <span class="bsm-chip bsm-chip-neutral"># ${p.id}</span>
                        ${brandChip}${catChip}
                    </div>
                </div>
                <div class="bsm-var-count">
                    <i class="feather icon-layers" style="font-size:.7rem;"></i> ${group.variations.length} تنوع
                </div>
            </div>
            <div class="bsm-col-header">
                <span></span>
                <span style="text-align:right;">ویژگی‌ها</span>
                <span>قیمت</span>
                <span>موجودی</span>
                <span>موجودی جدید</span>
                <span>تغییر</span>
            </div>
            <div class="bsm-variations-list">
                ${group.variations.map(bsmBuildRow).join('')}
            </div>
            <div class="bsm-product-foot">
                مجموع: <strong>${group.variations.reduce((s, v) => s + (v.stock || 0), 0).toLocaleString('fa-IR')}</strong>
            </div>
        `;
        return card;
    }

    function bsmBuildRow(price) {
        const sys = parseInt(price.stock) || 0;
        const attrsHtml = (price.attributes?.length)
            ? `<div class="bsm-attr-list">${price.attributes.map(a => {
                const dot = a.group?.type === 'color'
                    ? `<span class="bsm-color-dot" style="background:${a.code || a.value || '#6c757d'};"></span>` : '';
                return `<span class="bsm-attr-badge">${dot}${a.name}</span>`;
            }).join('')}</div>`
            : `<span style="font-size:.74rem;color:#94a3b8;font-style:italic;">تنوع پایه</span>`;

        const soldHtml = price.sold_count > 0
            ? `<div class="bsm-sold-info"><i class="feather icon-shopping-cart" style="font-size:.62rem;"></i> ${price.sold_count}</div>` : '';

        return `
            <div class="bsm-var-row" data-price-id="${price.id}" data-current-stock="${sys}">
                <input type="checkbox" class="price-checkbox"
                       value="${price.id}" data-price-id="${price.id}">
                <div>
                    ${attrsHtml}
                    <div class="bsm-var-code">کد: ${price.id}</div>
                </div>
                <div class="bsm-price-cell">
                    <div class="bsm-price-val">${Number(price.price).toLocaleString('fa-IR')}
                        <small style="font-weight:400;font-size:.62rem;">ت</small></div>
                    ${price.discount > 0 ? `<div class="bsm-price-dis">${price.discount}% تخفیف</div>` : ''}
                </div>
                <div class="bsm-stock-cell">
                    <span class="bsm-stock-badge ${sys > 0 ? 'in-stock' : 'out-stock'}">${sys.toLocaleString('fa-IR')}</span>
                    ${soldHtml}
                </div>
                <div>
                    <input type="number" class="bsm-new-stock-input new-stock"
                           data-price-id="${price.id}" value="${sys}" min="0" step="1">
                </div>
                <div style="text-align:center;">
                    <span class="bsm-diff-badge neutral" data-diff-for="${price.id}">0</span>
                </div>
            </div>
        `;
    }

    function bsmRestoreState() {
        document.querySelectorAll('.bsm-var-row').forEach(row => {
            const pid = row.dataset.priceId;
            if (bsmChanges[pid] !== undefined) {
                const inp = row.querySelector('.new-stock');
                if (inp) {
                    inp.value = bsmChanges[pid].newStock;
                    inp.classList.toggle('changed', bsmChanges[pid].newStock !== bsmChanges[pid].currentStock);
                    bsmUpdateDiff(pid, bsmChanges[pid].newStock - bsmChanges[pid].currentStock);
                }
            }
            if (bsmSelected.has(pid)) {
                const chk = row.querySelector('.price-checkbox');
                if (chk) chk.checked = true;
                row.classList.add('is-checked');
            }
        });
        document.querySelectorAll('.product-select-all').forEach(bsmSyncProductChk);
    }

    function bsmRenderPagination(meta) {
        if (!meta || meta.last_page <= 1) return;
        el('bsm-pagination').style.display = 'flex';
        el('bsm-page-info').textContent = `صفحه ${meta.current_page} از ${meta.last_page} — ${meta.total} آیتم`;

        const range = buildPageRange(meta.current_page, meta.last_page);
        const btns = [];

        btns.push(`<button class="bsm-page-btn" data-page="${meta.current_page - 1}" ${meta.current_page === 1 ? 'disabled' : ''}>
            <i class="feather icon-chevron-right" style="font-size:.8rem;"></i></button>`);

        range.forEach(p => {
            if (p === '...') btns.push(`<span class="bsm-page-btn" style="cursor:default;border:none;">…</span>`);
            else btns.push(`<button class="bsm-page-btn ${p === meta.current_page ? 'active' : ''}" data-page="${p}">${p}</button>`);
        });

        btns.push(`<button class="bsm-page-btn" data-page="${meta.current_page + 1}" ${meta.current_page === meta.last_page ? 'disabled' : ''}>
            <i class="feather icon-chevron-left" style="font-size:.8rem;"></i></button>`);

        el('bsm-page-btns').innerHTML = btns.join('');
    }

    function bsmUpdateDiff(priceId, diff) {
        const badge = document.querySelector(`[data-diff-for="${priceId}"]`);
        if (!badge) return;
        badge.textContent = diff > 0 ? '+' + diff : String(diff);
        badge.className = 'bsm-diff-badge ' + (diff > 0 ? 'positive' : diff < 0 ? 'negative' : 'neutral');
    }

    function bsmUpdateSelectedCount() {
        const n = bsmSelected.size;
        ['selected-count', 'selected-count-footer'].forEach(id => {
            const e = el(id);
            if (e) e.textContent = n;
        });
        ['selected-count-badge', 'selected-count-badge-footer'].forEach(id => {
            el(id)?.classList.toggle('has-selection', n > 0);
        });
    }

    function bsmSyncSelectAll() {
        const all = document.querySelectorAll('.price-checkbox');
        const sa = el('bsm-select-all');
        if (sa) sa.checked = all.length > 0 && [...all].every(c => bsmSelected.has(c.dataset.priceId));
    }

    function bsmSyncProductChk(chk) {
        const card = chk.closest('.bsm-product-card');
        if (!card) return;
        const all = card.querySelectorAll('.price-checkbox');
        chk.checked = all.length > 0 && [...all].every(c => bsmSelected.has(c.dataset.priceId));
    }

    function bsmSaveCurrentPage() {
        document.querySelectorAll('.bsm-var-row').forEach(row => {
            const pid = row.dataset.priceId;
            const sys = parseInt(row.dataset.currentStock) || 0;
            const val = parseInt(row.querySelector('.new-stock')?.value) || 0;
            if (val !== sys) bsmChanges[pid] = {priceId: pid, currentStock: sys, newStock: val};
            else delete bsmChanges[pid];
        });
    }

    function bsmApplyOperation() {
        const operationType = el('operation-type').value;
        const bulkValue = parseFloat(el('bulk-value').value);
        const applyScope = el('apply-scope').value;
        let targets = [];

        if (applyScope === 'all') {
            targets = [...document.querySelectorAll('.bsm-var-row')];
        } else if (applyScope === 'product') {
            document.querySelectorAll('.product-select-all:checked').forEach(chk => {
                chk.closest('.bsm-product-card')?.querySelectorAll('.bsm-var-row')
                    .forEach(r => targets.push(r));
            });
        } else {
            targets = [...document.querySelectorAll('.price-checkbox:checked')]
                .map(c => c.closest('.bsm-var-row')).filter(Boolean);
        }

        if (!targets.length) {
            toastr.warning('حداقل یک تنوع را انتخاب کنید');
            return;
        }
        if (isNaN(bulkValue) && operationType !== 'set') {
            toastr.warning('لطفاً مقدار را وارد کنید');
            return;
        }

        targets.forEach(row => {
            const sys = parseInt(row.dataset.currentStock) || 0;
            const inp = row.querySelector('.new-stock');
            if (!inp) return;
            let newStock = sys;
            switch (operationType) {
                case 'set':
                    newStock = bulkValue;
                    break;
                case 'add':
                    newStock = sys + bulkValue;
                    break;
                case 'subtract':
                    newStock = sys - bulkValue;
                    break;
                case 'percentage_add':
                    newStock = sys + (sys * bulkValue / 100);
                    break;
                case 'percentage_subtract':
                    newStock = sys - (sys * bulkValue / 100);
                    break;
            }
            newStock = Math.max(0, Math.floor(newStock));
            inp.value = newStock;
            inp.classList.toggle('changed', newStock !== sys);
            const pid = row.dataset.priceId;
            bsmUpdateDiff(pid, newStock - sys);
            if (newStock !== sys) bsmChanges[pid] = {priceId: pid, currentStock: sys, newStock};
            else delete bsmChanges[pid];
        });
        toastr.success('تغییرات اعمال شد');
    }

    function bsmSubmit() {
        bsmSaveCurrentPage();
        if (!Object.keys(bsmChanges).length) {
            toastr.warning('هیچ تغییری ایجاد نشده است');
            return;
        }
        const btn = el('submit-bulk-update');
        btn.disabled = true;
        btn.innerHTML = '<i class="feather icon-loader" style="animation:bsm-spin .8s linear infinite;font-size:.8rem;"></i> در حال ذخیره...';

        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('description', el('bsm-description').value || '');
        Object.values(bsmChanges).forEach(c => {
            fd.append(`stocks[${c.priceId}][price_id]`, c.priceId);
            fd.append(`stocks[${c.priceId}][stock]`, c.newStock);
        });

        fetch(SUBMIT_BSM_URL, {method: 'POST', body: fd})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    toastr.success(res.message);
                    $('#bulkStockModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.message || 'خطا در بروزرسانی');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="feather icon-save" style="font-size:.8rem;"></i> ذخیره تغییرات';
                }
            })
            .catch(() => {
                toastr.error('خطا در ارتباط با سرور');
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-save" style="font-size:.8rem;"></i> ذخیره تغییرات';
            });
    }

    // ===== event listeners =====

    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('new-stock')) return;
        const row = e.target.closest('.bsm-var-row');
        if (!row) return;
        const pid = row.dataset.priceId;
        const sys = parseInt(row.dataset.currentStock) || 0;
        const val = parseInt(e.target.value) || 0;
        e.target.classList.toggle('changed', val !== sys);
        bsmUpdateDiff(pid, val - sys);
        if (val !== sys) bsmChanges[pid] = {priceId: pid, currentStock: sys, newStock: val};
        else delete bsmChanges[pid];
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('price-checkbox')) {
            const pid = e.target.dataset.priceId;
            const row = e.target.closest('.bsm-var-row');
            if (e.target.checked) {
                bsmSelected.add(pid);
                row?.classList.add('is-checked');
            } else {
                bsmSelected.delete(pid);
                row?.classList.remove('is-checked');
            }
            bsmUpdateSelectedCount();
            const pc = e.target.closest('.bsm-product-card')?.querySelector('.product-select-all');
            if (pc) bsmSyncProductChk(pc);
            bsmSyncSelectAll();
        }

        if (e.target.classList.contains('product-select-all')) {
            e.target.closest('.bsm-product-card')?.querySelectorAll('.price-checkbox').forEach(chk => {
                chk.checked = e.target.checked;
                const row = chk.closest('.bsm-var-row');
                if (e.target.checked) {
                    bsmSelected.add(chk.dataset.priceId);
                    row?.classList.add('is-checked');
                } else {
                    bsmSelected.delete(chk.dataset.priceId);
                    row?.classList.remove('is-checked');
                }
            });
            bsmUpdateSelectedCount();
            bsmSyncSelectAll();
        }
    });

    el('bsm-select-all')?.addEventListener('change', function() {
        document.querySelectorAll('.price-checkbox').forEach(chk => {
            chk.checked = this.checked;
            const row = chk.closest('.bsm-var-row');
            if (this.checked) {
                bsmSelected.add(chk.dataset.priceId);
                row?.classList.add('is-checked');
            } else {
                bsmSelected.delete(chk.dataset.priceId);
                row?.classList.remove('is-checked');
            }
        });
        document.querySelectorAll('.product-select-all').forEach(c => c.checked = this.checked);
        bsmUpdateSelectedCount();
    });

    el('bsm-search')?.addEventListener('input', function() {
        bsmSearch = this.value.trim();
        clearTimeout(bsmSearchTimer);
        bsmSearchTimer = setTimeout(() => bsmLoadPage(1), 400);
    });

    el('bsm-pagination')?.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-page]');
        if (!btn || btn.disabled) return;
        bsmSaveCurrentPage();
        bsmLoadPage(parseInt(btn.dataset.page));
        el('bsm-scroll-wrap').scrollTop = 0;
    });

    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('new-stock')) return;
        if (!['Enter', 'ArrowDown', 'ArrowUp'].includes(e.key)) return;
        e.preventDefault();
        const inputs = [...document.querySelectorAll('.new-stock')];
        const next = inputs[inputs.indexOf(e.target) + (e.key === 'ArrowUp' ? -1 : 1)];
        if (next) {
            next.focus();
            next.select();
        }
    });

    el('apply-bulk-update')?.addEventListener('click', bsmApplyOperation);
    el('submit-bulk-update')?.addEventListener('click', bsmSubmit);

    $('#bulkStockModal').on('shown.bs.modal', function() {
        bsmChanges = {};
        bsmSelected = new Set();
        bsmPage = 1;
        bsmSearch = '';
        el('bsm-search').value = '';
        el('bsm-description').value = '';
        el('bsm-select-all').checked = false;
        bsmUpdateSelectedCount();
        bsmLoadPage(1);
    });

})();


// ============================================================
// ۴. سرشماری انبار (STM)
// ============================================================
(function() {

    // ===== state =====
    let stmPage = 1;
    let stmSearch = '';
    let stmFilter = null;   // null | 'empty' | 'low' | 'changes'
    let stmStats = {total: 0, ok: 0, low: 0, empty: 0};
    let stmChanges = {};
    let stmConfirmed = new Set();
    let stmSearchTimer = null;

    const el = id => document.getElementById(id);

    function stmLoadPage(page = 1) {
        stmPage = page;
        el('stm-loading').style.display = 'block';
        el('stm-content').innerHTML = '';
        el('stm-empty-state').style.display = 'none';
        el('stm-pagination').style.display = 'none';

        const params = new URLSearchParams({page});
        if (stmSearch) params.set('search', stmSearch);
        if (stmFilter === 'empty') params.set('stock_filter', 'empty');
        if (stmFilter === 'low') params.set('stock_filter', 'low');

        fetch(`${stockTakeAPI}?${params}`, {
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}
        })
            .then(r => r.json())
            .then(data => {
                el('stm-loading').style.display = 'none';
                stmUpdateStats(data.stats);
                stmRenderContent(data.variations);
            })
            .catch(() => {
                el('stm-loading').style.display = 'none';
                el('stm-content').innerHTML =
                    '<div class="stm-loading" style="color:#dc2626;">خطا در بارگذاری</div>';
            });
    }

    function stmUpdateStats(stats) {
        stmStats = stats;
        el('stat-ok').textContent = stats.ok;
        el('stat-low').textContent = stats.low;
        el('stat-empty').textContent = stats.empty;
        el('stat-total').textContent = stats.total;
        stmUpdateProgress();
    }

    function stmRenderContent(paginated) {
        const items = paginated.data || [];
        if (!items.length) {
            el('stm-empty-state').style.display = 'block';
            return;
        }

        const groups = {};
        items.forEach(price => {
            const pid = price.product_id;
            if (!groups[pid]) groups[pid] = {product: price.product, variations: []};
            groups[pid].variations.push(price);
        });

        const frag = document.createDocumentFragment();
        Object.values(groups).forEach(g => frag.appendChild(stmBuildCard(g)));
        el('stm-content').innerHTML = '';
        el('stm-content').appendChild(frag);

        stmRestoreState();
        if (stmFilter === 'changes') stmApplyChangesFilter();
        stmRenderPagination(paginated);
        stmUpdateTotalDiff();
    }

    function stmBuildCard(group) {
        const card = document.createElement('div');
        card.className = 'stm-product-card';
        const p = group.product || {};

        const imgHtml = p.image
            ? `<img src="${FRONT_URL + '/' + p.image}" class="stm-product-thumb" alt="">`
            : `<div class="stm-product-thumb-ph"><i class="feather icon-box"></i></div>`;

        const brandChip = p.brand?.name
            ? `<span class="stm-chip stm-chip-primary"><i class="feather icon-tag" style="font-size:.6rem;"></i>${p.brand.name}</span>` : '';
        const catChip = p.category?.title
            ? `<span class="stm-chip stm-chip-info"><i class="feather icon-folder" style="font-size:.6rem;"></i>${p.category.title}</span>` : '';

        card.innerHTML = `
            <div class="stm-product-head">
                ${imgHtml}
                <div class="stm-product-info">
                    <div class="stm-product-name">${p.title || '—'}</div>
                    <div class="stm-product-meta">
                        <span class="stm-chip stm-chip-neutral"># ${p.id}</span>
                        ${brandChip}${catChip}
                    </div>
                </div>
                <div class="stm-var-count">
                    <i class="feather icon-layers" style="font-size:.7rem;"></i> ${group.variations.length} تنوع
                </div>
            </div>
            <div class="stm-col-header">
                <span>موجودی سیستم</span>
                <span style="text-align:right;">ویژگی‌ها</span>
                <span>موجودی واقعی</span>
                <span>مغایرت</span>
                <span>تخفیف</span>
                <span title="تأیید"><i class="feather icon-check-circle" style="font-size:.82rem;"></i></span>
            </div>
            <div class="stm-variations-list">
                ${group.variations.map(stmBuildRow).join('')}
            </div>
        `;
        return card;
    }

    function stmBuildRow(price) {
        const sys = parseInt(price.stock) || 0;
        const hasDiscount = price.discount > 0
            && price.discount_expire_at
            && new Date(price.discount_expire_at) > new Date();

        let rowClass = 'stm-var-row';
        if (sys === 0) rowClass += ' row-empty';
        else if (sys <= 5) rowClass += ' row-low';

        const attrsHtml = (price.attributes?.length)
            ? price.attributes.map(a => {
                const dot = a.group?.type === 'color'
                    ? `<span class="stm-color-dot" style="background:${a.code || a.value || '#6c757d'};"></span>` : '';
                return `<span class="stm-attr-badge">${dot}${a.name}</span>`;
            }).join('')
            : '<span style="font-size:.72rem;color:#94a3b8;font-style:italic;">پایه</span>';

        const discHtml = hasDiscount
            ? `<span style="font-size:.72rem;font-weight:700;padding:.14rem .4rem;border-radius:5px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;">${price.discount}%</span>`
            : `<span style="color:#cbd5e1;font-size:.72rem;">—</span>`;

        return `
            <div class="${rowClass}"
                 data-price-id="${price.id}"
                 data-system-stock="${sys}"
                 data-product-id="${price.product_id}">
                <div style="text-align:center;">
                    <span class="stm-sys-stock">${sys.toLocaleString('fa-IR')}</span>
                    ${hasDiscount ? `<div style="font-size:.6rem;color:#dc2626;margin-top:.1rem;">تخفیف فعال</div>` : ''}
                </div>
                <div class="stm-var-name-col">
                    <div>${attrsHtml}</div>
                    <div class="stm-var-code-small">کد: ${price.id}</div>
                </div>
                <div style="text-align:center;">
                    <input type="number" class="stm-actual-input actual-stock"
                           data-price-id="${price.id}" data-sys="${sys}"
                           value="${sys}" min="0" step="1">
                </div>
                <div style="text-align:center;">
                    <span class="stm-diff-badge neutral" data-diff-for="${price.id}">0</span>
                </div>
                <div style="text-align:center;">${discHtml}</div>
                <div style="text-align:center;">
                    <input type="checkbox" class="stm-confirm-check"
                           data-price-id="${price.id}" title="تأیید">
                </div>
            </div>
        `;
    }

    function stmRestoreState() {
        document.querySelectorAll('.actual-stock').forEach(inp => {
            const pid = inp.dataset.priceId;
            if (stmChanges[pid] !== undefined) {
                inp.value = stmChanges[pid].actual;
                inp.classList.toggle('changed', stmChanges[pid].actual !== parseInt(inp.dataset.sys));
                stmUpdateDiffBadge(pid, stmChanges[pid].actual - stmChanges[pid].sys);
            }
        });
        document.querySelectorAll('.stm-confirm-check').forEach(chk => {
            if (stmConfirmed.has(chk.dataset.priceId)) {
                chk.checked = true;
                const row = chk.closest('.stm-var-row');
                row?.classList.add('row-confirmed');
                const inp = row?.querySelector('.actual-stock');
                if (inp) inp.disabled = true;
            }
        });
    }

    function stmRenderPagination(meta) {
        if (!meta || meta.last_page <= 1) return;
        el('stm-pagination').style.display = 'flex';
        el('stm-page-info').textContent = `صفحه ${meta.current_page} از ${meta.last_page} — ${meta.total} آیتم`;

        const range = buildPageRange(meta.current_page, meta.last_page);
        const btns = [];
        btns.push(`<button class="stm-page-btn" data-page="${meta.current_page - 1}" ${meta.current_page === 1 ? 'disabled' : ''}><i class="feather icon-chevron-right" style="font-size:.8rem;"></i></button>`);
        range.forEach(p => {
            if (p === '...') btns.push(`<span class="stm-page-btn" style="cursor:default;border:none;">…</span>`);
            else btns.push(`<button class="stm-page-btn ${p === meta.current_page ? 'active' : ''}" data-page="${p}">${p}</button>`);
        });
        btns.push(`<button class="stm-page-btn" data-page="${meta.current_page + 1}" ${meta.current_page === meta.last_page ? 'disabled' : ''}><i class="feather icon-chevron-left" style="font-size:.8rem;"></i></button>`);
        el('stm-page-btns').innerHTML = btns.join('');
    }

    function stmUpdateDiffBadge(priceId, diff) {
        const badge = document.querySelector(`[data-diff-for="${priceId}"]`);
        if (!badge) return;
        badge.textContent = diff > 0 ? '+' + diff : String(diff);
        badge.className = 'stm-diff-badge ' + (diff > 0 ? 'positive' : diff < 0 ? 'negative' : 'neutral');
    }

    function stmUpdateTotalDiff() {
        let total = 0;
        document.querySelectorAll('.actual-stock').forEach(inp => {
            total += (parseInt(inp.value) || 0) - (parseInt(inp.dataset.sys) || 0);
        });
        Object.values(stmChanges).forEach(c => {
            if (!document.querySelector(`.actual-stock[data-price-id="${c.priceId}"]`))
                total += c.actual - c.sys;
        });
        ['total-difference', 'stm-footer-diff'].forEach(id => {
            const e = el(id);
            if (!e) return;
            e.textContent = total > 0 ? '+' + total : String(total);
            e.className = e.className.replace(/\b(positive|negative|neutral)\b/, '')
                + ' ' + (total > 0 ? 'positive' : total < 0 ? 'negative' : 'neutral');
        });
    }

    function stmUpdateProgress() {
        const confirmed = stmConfirmed.size;
        const total = stmStats.total || 1;
        const pct = Math.round(confirmed / total * 100);
        const fill = el('stm-progress-fill');
        if (fill) fill.style.width = pct + '%';
        const cnt = el('stm-progress-count');
        if (cnt) cnt.textContent = `${confirmed} از ${stmStats.total} تأیید شده`;
        const fc = el('stm-confirmed-count');
        if (fc) fc.textContent = confirmed;
    }

    function stmApplyChangesFilter() {
        document.querySelectorAll('.stm-var-row').forEach(row => {
            const pid = row.dataset.priceId;
            const sys = parseInt(row.dataset.systemStock) || 0;
            const val = parseInt(row.querySelector('.actual-stock')?.value) || sys;
            row.style.display = (val !== sys || stmChanges[pid]) ? '' : 'none';
        });
    }

    // ===== events =====

    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('actual-stock')) return;
        const pid = e.target.dataset.priceId;
        const sys = parseInt(e.target.dataset.sys) || 0;
        const val = parseInt(e.target.value) || 0;
        e.target.classList.toggle('changed', val !== sys);
        stmUpdateDiffBadge(pid, val - sys);
        if (val !== sys) stmChanges[pid] = {priceId: pid, sys, actual: val};
        else delete stmChanges[pid];
        stmUpdateTotalDiff();
        if (stmFilter === 'changes') stmApplyChangesFilter();
    });

    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('actual-stock')) return;
        if (!['Enter', 'ArrowDown', 'ArrowUp'].includes(e.key)) return;
        e.preventDefault();
        const inputs = [...document.querySelectorAll('.actual-stock:not([disabled])')];
        const next = inputs[inputs.indexOf(e.target) + (e.key === 'ArrowUp' ? -1 : 1)];
        if (next) {
            next.focus();
            next.select();
        }
    });

    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('stm-confirm-check')) return;
        const pid = e.target.dataset.priceId;
        const row = e.target.closest('.stm-var-row');
        const inp = row?.querySelector('.actual-stock');
        if (e.target.checked) {
            stmConfirmed.add(pid);
            row?.classList.add('row-confirmed');
            if (inp) inp.disabled = true;
        } else {
            stmConfirmed.delete(pid);
            row?.classList.remove('row-confirmed');
            if (inp) inp.disabled = false;
        }
        stmUpdateProgress();
    });

    el('stm-pagination')?.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-page]');
        if (!btn || btn.disabled) return;
        stmLoadPage(parseInt(btn.dataset.page));
        el('stm-scroll-wrap').scrollTop = 0;
    });

    el('stm-search')?.addEventListener('input', function() {
        stmSearch = this.value.trim();
        clearTimeout(stmSearchTimer);
        stmSearchTimer = setTimeout(() => stmLoadPage(1), 400);
    });

    ['stm-filter-empty', 'stm-filter-low', 'stm-filter-changes'].forEach(id => {
        el(id)?.addEventListener('click', function() {
            const filter = this.dataset.filter;
            const classMap = {empty: 'active-empty', low: 'active-low', changes: 'active-changes'};

            if (stmFilter === filter) {
                stmFilter = null;
                this.classList.remove(classMap[filter]);
            } else {
                document.querySelectorAll('.stm-filter-btn').forEach(b =>
                    Object.values(classMap).forEach(c => b.classList.remove(c)));
                stmFilter = filter;
                this.classList.add(classMap[filter]);
            }

            if (filter === 'changes') {
                stmFilter === 'changes' ? stmApplyChangesFilter()
                    : document.querySelectorAll('.stm-var-row').forEach(r => r.style.display = '');
            } else {
                stmLoadPage(1);
            }
        });
    });

    el('submitStockTake')?.addEventListener('click', function() {
        // save current page
        document.querySelectorAll('.actual-stock').forEach(inp => {
            const pid = inp.dataset.priceId;
            const sys = parseInt(inp.dataset.sys) || 0;
            const val = parseInt(inp.value) || 0;
            if (val !== sys) stmChanges[pid] = {priceId: pid, sys, actual: val};
        });

        const stocks = Object.values(stmChanges);
        const totalDiff = stocks.reduce((s, c) => s + (c.actual - c.sys), 0);
        const msg = stocks.length === 0
            ? 'هیچ تغییری ثبت نشده. آیا سرشماری را ثبت کنید؟'
            : `مغایرت کل: ${totalDiff > 0 ? '+' : ''}${totalDiff} عدد\nتعداد آیتم: ${stocks.length}\nآیا از ثبت مطمئن هستید؟`;

        if (!confirm(msg)) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="feather icon-loader" style="animation:spin .8s linear infinite;font-size:.8rem;"></i> در حال ثبت...';

        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('description', el('stm-description')?.value || '');
        stocks.forEach(s => {
            fd.append(`stocks[${s.priceId}][price_id]`, s.priceId);
            fd.append(`stocks[${s.priceId}][actual_stock]`, s.actual);
        });

        fetch(SUBMIT_STM_URL, {method: 'POST', body: fd})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    toastr.success(res.message);
                    $('#stockTakeModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.message || 'خطا در ثبت');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="feather icon-clipboard" style="font-size:.8rem;"></i> ثبت سرشماری';
                }
            })
            .catch(() => {
                toastr.error('خطا در ارتباط با سرور');
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-clipboard" style="font-size:.8rem;"></i> ثبت سرشماری';
            });
    });

    $('#stockTakeModal').on('shown.bs.modal', function() {
        stmChanges = {};
        stmConfirmed = new Set();
        stmPage = 1;
        stmSearch = '';
        stmFilter = null;
        el('stm-search').value = '';
        el('stm-description').value = '';
        document.querySelectorAll('.stm-filter-btn').forEach(b =>
            b.classList.remove('active-empty', 'active-low', 'active-changes'));
        stmLoadPage(1);
    });

})();



// ============= تاریخچه موجودی
    let currentProductId = null;
    let currentHistoryUrl = null;

// نمایش تاریخچه موجودی
    function showStockHistory(element, productId) {
        currentProductId = productId;
        currentHistoryUrl = $(element).data('action');

        // نمایش مودال و بارگذاری داده‌ها
        $('#stockHistoryModal').modal('show');

        loadStockHistoryData();

    }

// بارگذاری داده‌های تاریخچه
    function loadStockHistoryData(movementType = 'all', variationId = 'all', dateFrom = '', dateTo = '') {
        let url = currentHistoryUrl;
        let params = new URLSearchParams({
            movement_type: movementType,
            variation_id: variationId,
            date_from: dateFrom,
            date_to: dateTo
        });

        $.ajax({
            url: url + '?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#stockHistoryModalBody').html(`
                <div class="text-center py-5">
                    <i class="feather icon-loader fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">در حال بارگذاری...</p>
                </div>
            `);
            },
            success: function(response) {
                $('#stockHistoryModalBody').html(response.html);
                $('#export-history-btn').show();

                // تنظیم عنوان مودال
                $('#modal-product-name').text(' - ' + response.product_title);

                // راه‌اندازی مجدد فیلترها
                attachFilterEvents();

                // راه‌اندازی datepicker
                if ($.fn.persianDatepicker) {
                    $('.persian-date-picker').persianDatepicker({
                        format: 'YYYY-MM-DD',
                        observer: true,
                        altField: '.observer-example-alt',
                        initialValue: false  // این خط را اضافه کنید
                    });
                }
            },
            error: function(xhr) {
                $('#stockHistoryModalBody').html(`
                <div class="text-center py-5 text-danger">
                    <i class="feather icon-alert-circle fa-2x"></i>
                    <p class="mt-2">خطا در بارگذاری اطلاعات</p>
                    <small>${xhr.responseJSON?.message || 'لطفاً مجدداً تلاش کنید'}</small>
                </div>
            `);
            }
        });
    }

// اتصال رویدادهای فیلتر
    function attachFilterEvents() {
        // فیلتر نوع حرکت
        $('#movement-type-filter, #variation-filter, #date-from, #date-to').off('change').on('change', function() {
            let movementType = $('#movement-type-filter').val();
            let variationId = $('#variation-filter').val();
            let dateFrom = $('#date-from').val();
            let dateTo = $('#date-to').val();

            let url = currentHistoryUrl;
            let params = new URLSearchParams({
                movement_type: movementType,
                variation_id: variationId,
                date_from: dateFrom,
                date_to: dateTo
            });

            $.ajax({
                url: url + '?' + params.toString(),
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#stock-history-body').html(`
                    <tr><td colspan="7" class="text-center py-5">
                        <i class="feather icon-loader fa-spin fa-2x"></i>
                        <p>در حال بارگذاری...</p>
                    </td></tr>
                `);
                },
                success: function(response) {
                    $('#stock-history-body').html($(response.html).find('#stock-history-body').html());
                    $('#stock-history-pagination').html($(response.html).find('#stock-history-pagination').html());
                    attachPaginationEvents();
                },
                error: function() {
                    $('#stock-history-body').html(`
                    <tr><td colspan="7" class="text-center text-danger py-5">
                        خطا در بارگذاری داده‌ها
                    </td></tr>
                `);
                }
            });
        });

        attachPaginationEvents();
    }

// اتصال رویدادهای pagination
    function attachPaginationEvents() {
        $('#stock-history-pagination .pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#stock-history-body').html(`
                    <tr><td colspan="7" class="text-center py-5">
                        <i class="feather icon-loader fa-spin fa-2x"></i>
                        <p>در حال بارگذاری...</p>
                    </td></tr>
                `);
                },
                success: function(response) {
                    $('#stock-history-body').html($(response.html).find('#stock-history-body').html());
                    $('#stock-history-pagination').html($(response.html).find('#stock-history-pagination').html());
                    attachPaginationEvents();
                }
            });
        });
    }

// خروجی اکسل
    $(document).on('click', '#export-history-btn', function() {
        let movementType = $('#movement-type-filter').val();
        let variationId = $('#variation-filter').val();
        let dateFrom = $('#date-from').val();
        let dateTo = $('#date-to').val();

        let params = new URLSearchParams({
            movement_type: movementType,
            variation_id: variationId,
            date_from: dateFrom,
            date_to: dateTo,
            export: 'excel'
        });

        window.location.href = currentHistoryUrl + '?' + params.toString();
    });

// هنگام بسته شدن مودال، محتوا را پاک کن
    $('#stockHistoryModal').on('hidden.bs.modal', function() {
        $('#stockHistoryModalBody').html(`
        <div class="text-center py-5">
            <i class="feather icon-loader fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">در حال بارگذاری...</p>
        </div>
    `);
        currentProductId = null;
    });
// =============  اتمام تاریخچه موجودی


// ============================================================
// ابزار مشترک
// ============================================================
function buildPageRange(cur, last) {
    if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
    const p = [1];
    if (cur > 3) p.push('...');
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) p.push(i);
    if (cur < last - 2) p.push('...');
    p.push(last);
    return p;
}
