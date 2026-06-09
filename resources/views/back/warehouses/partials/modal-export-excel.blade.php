{{-- مودال خروجی اکسل --}}
<div class="modal fade" id="exportExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="feather icon-file-text text-success"></i> خروجی اکسل</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="export-form" action="{{ route('admin.warehouses.export', $warehouse) }}" method="GET">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>وضعیت موجودی</label>
                            <select name="stock_status" class="form-control">
                                <option value="all">تمام محصولات</option>
                                <option value="in_stock">محصولات موجود</option>
                                <option value="out_of_stock">محصولات ناموجود</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>دسته‌بندی</label>
                            <select name="category_id" class="form-control">
                                <option value="">همه دسته‌بندی‌ها</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>برند</label>
                            <select name="brand_id" class="form-control">
                                <option value="">همه برندها</option>
                                @foreach($brands ?? [] as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="format" value="excel">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success" id="export-submit">دریافت خروجی</button>
            </div>
        </div>
    </div>
</div>
