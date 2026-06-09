{{-- ===== مودال تغییر گروهی موجودی انبار ===== --}}
<div class="modal fade" id="bulkStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            {{-- هدر --}}
            <div class="modal-header">
                <div class="bsm-modal-title">
                    <div class="bsm-title-icon">
                        <i class=" fas fa-pen-to-square" style="font-size:.9rem;"></i>
                    </div>
                    <div>
                        <div>بروزرسانی گروهی موجودی</div>
                        <div class="bsm-title-meta">{{ $warehouse->name }}</div>
                    </div>
                </div>
                <button type="button" class="bsm-close" data-dismiss="modal" aria-label="Close">
                    <i class="feather icon-x"></i>
                </button>
            </div>

            {{-- بادی --}}
            <div class="modal-body">

                {{-- نوار ابزار: جستجو + انتخاب + badge --}}
                <div class="bsm-toolbar">
                    <input type="text" class="bsm-search" id="bsm-search" placeholder="جستجوی محصول یا کد تنوع...">
                    <div class="bsm-sep"></div>
                    <label class="bsm-select-all-wrap">
                        <input type="checkbox" id="bsm-select-all"> انتخاب همه صفحه
                    </label>
                    <div class="bsm-sep"></div>
                    <span class="bsm-count-pill" id="selected-count-badge">
                        <i class="feather icon-check-square" style="font-size:.76rem;"></i>
                        <span class="cnt" id="selected-count">0</span> تنوع انتخاب شده
                    </span>
                </div>

                {{-- محتوا --}}
                <div class="bsm-scroll" id="bsm-scroll-wrap">
                    <div class="bsm-loading" id="bsm-loading">
                        <i class="feather icon-loader spin"></i>
                        در حال بارگذاری...
                    </div>
                    <div id="bsm-content"></div>
                    <div class="bsm-empty-state" id="bsm-empty-state">
                        <i class="feather icon-search" style="font-size:1.8rem;display:block;margin-bottom:.5rem;opacity:.35;"></i>
                        نتیجه‌ای یافت نشد
                    </div>
                    <div class="bsm-pagination" id="bsm-pagination">
                        <span class="bsm-page-info" id="bsm-page-info"></span>
                        <div class="bsm-page-btns" id="bsm-page-btns"></div>
                    </div>
                </div>

                {{-- پنل عملیات گروهی --}}
                <div class="bsm-ops-panel">
                    <div class="bsm-ops-title">
                        <i class="feather icon-sliders" style="font-size:.82rem;"></i>
                        عملیات گروهی
                    </div>
                    <div class="bsm-ops-grid">
                        <div class="bsm-form-group">
                            <label for="operation-type">نوع عملیات</label>
                            <select id="operation-type" class="bsm-form-control">
                                <option value="set">✏️ تنظیم مستقیم</option>
                                <option value="add">➕ افزایش عدد</option>
                                <option value="subtract">➖ کاهش عدد</option>
                                <option value="percentage_add">📈 افزایش درصدی</option>
                                <option value="percentage_subtract">📉 کاهش درصدی</option>
                            </select>
                        </div>
                        <div class="bsm-form-group">
                            <label for="bulk-value">مقدار</label>
                            <input type="number" id="bulk-value" class="bsm-form-control" placeholder="عدد...">
                        </div>
                        <div class="bsm-form-group">
                            <label for="apply-scope">اعمال روی</label>
                            <select id="apply-scope" class="bsm-form-control">
                                <option value="selected">✅ تنوع‌های انتخاب شده</option>
                                <option value="product">📦 محصولات انتخاب شده</option>
                                <option value="all">🌐 همه (این صفحه)</option>
                            </select>
                        </div>
                        <div class="bsm-form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="apply-bulk-update" class="bsm-btn-apply">
                                <i class="feather icon-zap" style="font-size:.82rem;"></i> اعمال
                            </button>
                        </div>
                    </div>
                    <div class="bsm-desc-row">
                        <div class="bsm-form-group">
                            <label for="bsm-description">توضیحات (اختیاری)</label>
                            <textarea id="bsm-description" rows="2" class="bsm-form-control"
                                      placeholder="دلیل بروزرسانی موجودی..."></textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- فوتر --}}
            <div class="modal-footer">
                <div class="bsm-footer-info">
                    <span class="bsm-count-pill" id="selected-count-badge-footer">
                        <i class="feather icon-check-square" style="font-size:.76rem;"></i>
                        <span id="selected-count-footer">0</span> تنوع انتخاب شده
                    </span>
                    <span class="bsm-footer-hint">
                        <i class="feather icon-info" style="font-size:.72rem;"></i>
                        تغییرات همه صفحات ثبت می‌شود
                    </span>
                </div>
                <button type="button" class="bsm-btn-cancel" data-dismiss="modal">
                    <i class="feather icon-x" style="font-size:.8rem;"></i> انصراف
                </button>
                <button type="button" class="bsm-btn-save" id="submit-bulk-update"
                        data-action="{{ route('admin.warehouses.bulk-stock-update', $warehouse) }}">
                    <i class="feather icon-save" style="font-size:.8rem;"></i> ذخیره تغییرات
                </button>
            </div>

        </div>
    </div>
</div>
