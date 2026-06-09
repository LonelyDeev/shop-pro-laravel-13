{{-- مودال PDF --}}
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="feather icon-file-text text-danger"></i> خروجی PDF</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="pdf-form" action="{{ route('admin.warehouses.export', $warehouse) }}" method="GET">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>بازه زمانی حرکات</label>
                            <select name="movement_period" class="form-control">
                                <option value="all">همه حرکات</option>
                                <option value="today">امروز</option>
                                <option value="week">هفته جاری</option>
                                <option value="month">ماه جاری</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="format" value="pdf">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-danger" id="pdf-submit">دریافت PDF</button>
            </div>
        </div>
    </div>
</div>
