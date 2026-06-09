
{{-- ===== مودال سرشماری انبار ===== --}}
<div class="modal fade" id="stockTakeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            {{-- هدر --}}
            <div class="modal-header">
                <div class="stm-modal-title">
                    <div class="stm-title-icon">
                        <i class=" fas fa-clipboard" style="font-size:.95rem;"></i>
                    </div>
                    <div>
                        <div>سرشماری انبار</div>
                        <div class="stm-title-meta">{{ $warehouse->name }}</div>
                    </div>
                </div>
                <button type="button" class="stm-close" data-dismiss="modal" aria-label="Close">
                    <i class="feather icon-x"></i>
                </button>
            </div>

            {{-- بادی --}}
            <div class="modal-body">

                <div class="stm-alert">
                    <div class="stm-alert-icon">
                        <i class="feather icon-alert-triangle" style="font-size:.78rem;"></i>
                    </div>
                    <div>
                        <strong>توجه:</strong> موجودی واقعی هر تنوع را وارد کنید. مغایرت‌ها در تاریخچه انبار ثبت می‌شوند.
                        بعد از تأیید هر ردیف (✓) آن کم‌رنگ می‌شود.
                    </div>
                </div>

                {{-- آمار --}}
                <div class="stm-top-bar" id="stm-stats-bar">
                    <div class="stm-stat-pill"><span class="stm-stat-dot ok"></span> موجود: <strong id="stat-ok">—</strong></div>
                    <div class="stm-stat-pill"><span class="stm-stat-dot low"></span> کم‌موجود: <strong id="stat-low">—</strong></div>
                    <div class="stm-stat-pill"><span class="stm-stat-dot empty"></span> اتمام: <strong id="stat-empty">—</strong></div>
                    <div class="stm-stat-pill"><i class="feather icon-layers" style="font-size:.7rem;color:var(--stm-muted);"></i> کل: <strong id="stat-total">—</strong></div>
                </div>

                {{-- پیشرفت --}}
                <div class="stm-progress-wrap">
                    <span class="stm-progress-label">پیشرفت:</span>
                    <div class="stm-progress-track"><div class="stm-progress-fill" id="stm-progress-fill"></div></div>
                    <span class="stm-progress-count" id="stm-progress-count">0 تأیید شده</span>
                </div>

                {{-- جستجو و فیلتر --}}
                <div class="stm-toolbar">
                    <input type="text" class="stm-search" id="stm-search" placeholder="جستجوی محصول یا کد تنوع...">
                    <div class="stm-filter-sep"></div>
                    <button type="button" class="stm-filter-btn" id="stm-filter-empty" data-filter="empty">
                        <i class="feather icon-x-circle" style="font-size:.76rem;"></i> صفر
                    </button>
                    <button type="button" class="stm-filter-btn" id="stm-filter-low" data-filter="low">
                        <i class="feather icon-alert-triangle" style="font-size:.76rem;"></i> کم‌موجود
                    </button>
                    <div class="stm-filter-sep"></div>
                    <button type="button" class="stm-filter-btn" id="stm-filter-changes" data-filter="changes">
                        <i class="feather icon-eye" style="font-size:.76rem;"></i> فقط تغییرات
                    </button>
                </div>

                {{-- محتوا --}}
                <div class="stm-scroll" id="stm-scroll-wrap">
                    <div class="stm-loading" id="stm-loading">
                        <i class="feather icon-loader"></i>
                        در حال بارگذاری...
                    </div>
                    <div id="stm-content"></div>
                    <div class="stm-empty-state" id="stm-empty-state">
                        <i class="feather icon-search" style="font-size:1.8rem;display:block;margin-bottom:.5rem;opacity:.35;"></i>
                        نتیجه‌ای یافت نشد
                    </div>
                    <div class="stm-pagination" id="stm-pagination" style="display:none;">
                        <span class="stm-page-info" id="stm-page-info"></span>
                        <div class="stm-page-btns" id="stm-page-btns"></div>
                    </div>
                </div>

                {{-- جمع مغایرت + توضیحات --}}
                <div class="stm-summary-bar">
                    <span class="stm-total-label"><i class="feather icon-activity" style="font-size:.76rem;margin-left:.25rem;"></i> مغایرت کل (این صفحه):</span>
                    <span id="total-difference" class="stm-total-diff neutral">0</span>
                    <span class="stm-total-label" style="margin-right:auto;font-weight:500;color:var(--stm-muted);font-size:.73rem;">
                        <i class="feather icon-info" style="font-size:.72rem;"></i>
                        مغایرت کل همه صفحات بعد از ثبت محاسبه می‌شود
                    </span>
                </div>

                <div class="stm-desc-wrap">
                    <div class="stm-desc-label"><i class="feather icon-file-text" style="font-size:.7rem;margin-left:.25rem;"></i> توضیحات (اختیاری)</div>
                    <textarea id="stm-description" class="stm-desc-input" rows="2" placeholder="دلیل یا توضیحات سرشماری..."></textarea>
                </div>

            </div>

            {{-- فوتر --}}
            <div class="modal-footer">
                <div class="stm-footer-info">
                    <span>مغایرت کل: <span id="stm-footer-diff" class="stm-total-diff neutral" style="font-size:.74rem;padding:.1rem .42rem;">0</span></span>
                    <span>تأیید شده: <strong id="stm-confirmed-count">0</strong></span>
                </div>
                <button type="button" class="stm-btn-cancel" data-dismiss="modal">
                    <i class="feather icon-x" style="font-size:.8rem;"></i> انصراف
                </button>
                <button type="button" class="stm-btn-submit" id="submitStockTake" data-action="{{route('admin.warehouses.stock-take',$warehouse)}}">
                    <i class="feather icon-clipboard" style="font-size:.8rem;"></i> ثبت سرشماری
                </button>
            </div>

        </div>
    </div>
</div>
