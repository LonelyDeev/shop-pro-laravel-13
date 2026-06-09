{{-- اطلاعات محصول --}}
<div class="product-info-card mb-4 p-3 bg-light rounded-3">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            @if($product->image)
                <img src="{{ asset($product->image) }}"
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
            @else
                <div class="bg-secondary rounded-3 d-flex align-items-center justify-content-center"
                     style="width: 60px; height: 60px;">
                    <i class="feather icon-box text-white fs-2"></i>
                </div>
            @endif
        </div>
        <div class="flex-grow-1 ml-1">
            <h6 class="mb-1 fw-bold">{{ $product->title }}</h6>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark">
                    <i class="feather icon-hash"></i> کد: {{ $product->id }}
                </span>
                @if($product->brand)
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        <i class="feather icon-tag"></i> {{ $product->brand->name }}
                    </span>
                @endif
                @if($product->category)
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="feather icon-folder"></i> {{ $product->category->title }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- آمار --}}
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 stat-card">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="feather icon-box"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">تعداد تنوع</span>
                        <span class="info-box-number">{{ number_format($variationsCount) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-card">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="feather icon-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل موجودی</span>
                        <span class="info-box-number">{{ number_format($totalStock) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 stat-card">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="feather icon-alert-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">رزرو شده</span>
                        <span class="info-box-number">{{ number_format($totalReserved) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-card">
                <div class="info-box">
                    <span class="info-box-icon bg-gradient-orange"><i class="feather icon-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل فروش</span>
                        <span class="info-box-number">{{ number_format($totalSold) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- فیلترها --}}
<div class="filter-bar bg-light p-3 rounded-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-muted">نوع حرکت</label>
            <select id="movement-type-filter" class="bsm-form-control">
                <option value="all">همه</option>
                <option value="in">ورود</option>
                <option value="out">خروج</option>
                <option value="reserve">رزرو</option>
                <option value="unreserve">لغو رزرو</option>
                <option value="adjustment">تنظیم دستی</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">تنوع</label>
            <select id="variation-filter" class="bsm-form-control">
                <option value="all">همه تنوع‌ها</option>
                @foreach($prices as $price)
                    <option value="{{ $price->id }}">
                        @foreach($price->attributes as $attr)
                            {{ $attr->name }}
                        @endforeach
                        (موجودی: {{ number_format($price->stock) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">از تاریخ</label>
            <input type="text" id="date-from" class="bsm-form-control persian-date-picker" placeholder="از تاریخ">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">تا تاریخ</label>
            <input type="text" id="date-to" class="bsm-form-control persian-date-picker" placeholder="تا تاریخ">
        </div>
    </div>
</div>

{{-- جدول تاریخچه --}}
<div class="table-responsive">
    <table class="table table-hover table-sm" id="stock-history-table">
        <thead class="bg-light">
        <tr>
            <th>تاریخ و زمان</th>
            <th>نوع</th>
            <th>تنوع</th>
            <th class="text-center">تعداد</th>
            <th class="text-center">موجودی قبل/بعد</th>
            <th>توضیحات</th>
            <th>اپراتور</th>
        </tr>
        </thead>
        <tbody id="stock-history-body">
        @include('back.warehouses.partials.stock-history-rows', ['movements' => $stockHistory])
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-3" id="stock-history-pagination">
    {{ $stockHistory->links() }}
</div>
