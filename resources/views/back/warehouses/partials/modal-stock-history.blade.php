{{-- مودال تاریخچه موجودی (خالی - با AJAX پر می‌شود) --}}
<div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-gradient-info text-white border-0 rounded-top-3">
                <h5 class="modal-title fw-bold">
                    <i class="feather icon-clock me-2"></i> تاریخچه موجودی
                    <span id="modal-product-name" class="ms-2 small"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="stockHistoryModalBody">
                <div class="text-center py-5">
                    <i class="feather icon-loader fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">در حال بارگذاری...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">
                    <i class="feather icon-x me-1"></i> بستن
                </button>
                <button type="button" class="btn btn-success px-4" id="export-history-btn" style="display: none;">
                    <i class="feather icon-download me-1"></i> خروجی اکسل
                </button>
            </div>
        </div>
    </div>
</div>
