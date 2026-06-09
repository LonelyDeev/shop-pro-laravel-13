@extends('back.layouts.master')
@push('styles')
    <style>
        .color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }
        .gap-1 {
            gap: 0.25rem !important;
        }
    </style>
@endpush
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">انبارها</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                                    <li class="breadcrumb-item active">تاریخچه حرکات</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-info btn-sm">
                        <i class="feather icon-arrow-right"></i> بازگشت به انبار
                    </a>
                </div>
            </div>

            <div class="content-body">
                {{-- اطلاعات انبار --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تاریخچه حرکات انبار: {{ $warehouse->name }}</h4>
                        <div>
                            <span class="badge bg-secondary">کد: {{ $warehouse->code }}</span>
                            @if($warehouse->type == 'main')
                                <span class="badge bg-primary">اصلی</span>
                            @elseif($warehouse->type == 'seller')
                                <span class="badge bg-info">فروشنده</span>
                            @else
                                <span class="badge bg-warning">موقت</span>
                            @endif
                            @if($warehouse->is_active)
                                <span class="badge bg-success">فعال</span>
                            @else
                                <span class="badge bg-danger">غیرفعال</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="feather icon-package"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">کل آیتم‌ها</span>
                                        <span class="info-box-number">{{ number_format($movements->total()) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="feather icon-arrow-down"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ورود به انبار</span>
                                        <span class="info-box-number">{{ number_format($movements->where('type', 'in')->count()) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="feather icon-arrow-up"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">خروج از انبار</span>
                                        <span class="info-box-number">{{ number_format($movements->where('type', 'out')->count()) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="feather icon-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">رزرو موقت</span>
                                        <span class="info-box-number">{{ number_format($movements->where('type', 'reserve')->count()) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- فیلترها --}}
                <div class="card">
                    <div class="card-header filter-card">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse">
                        <div class="card-body">
                            <form method="GET" id="filter-form" class="row">
                                <div class="col-md-3">
                                    <label>نوع حرکت</label>
                                    <select name="type" id="filter-type" class="form-control">
                                        <option value="all">همه</option>
                                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>ورود به انبار</option>
                                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>خروج از انبار</option>
                                        <option value="reserve" {{ request('type') == 'reserve' ? 'selected' : '' }}>رزرو موقت</option>
                                        <option value="unreserve" {{ request('type') == 'unreserve' ? 'selected' : '' }}>لغو رزرو</option>
                                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>تعدیل دستی</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>محصول</label>
                                    <select name="product_id" id="filter-product" class="form-control">
                                        <option value="all">همه محصولات</option>
                                        @foreach($products ?? [] as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>از تاریخ</label>
                                    <input type="text" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="1400/01/01">
                                </div>
                                <div class="col-md-2">
                                    <label>تا تاریخ</label>
                                    <input type="text" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="1400/12/29">
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary form-control">فیلتر</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- جدول حرکات --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست حرکات انبار</h4>
                        <button class="btn btn-sm btn-outline-success" onclick="exportMovements()">
                            <i class="feather icon-download"></i> خروجی Excel
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>تاریخ و زمان</th>
                                        <th>نوع حرکت</th>
                                        <th>محصول</th>
                                        <th>تنوع</th>
                                        <th>تعداد</th>
                                        <th class="text-center">موجودی قبل/بعد</th>
                                        <th>مرجع</th>
                                        <th>توضیحات</th>
                                        <th>اپراتور</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($movements as $movement)
                                        <tr>
                                            <td>
                                                {{ jdate($movement->created_at)->format('d F Y H:i:s') }}
                                                <br>
                                                <small class="text-muted">{{ jdate($movement->created_at)->ago() }}</small>
                                            </td>
                                            <td>
                                            <span class="badge {{ $movement->type_badge_class }}">
                                                {{ $movement->type_label }}
                                            </span>
                                            </td>
                                            <td>
                                                @if($movement->price && $movement->price->product)
                                                    <a href="{{ route('admin.products.edit', $movement->price->product) }}" target="_blank">
                                                        {{ $movement->price->product->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">محصول حذف شده</span>
                                                @endif
                                                @if($movement->price)
                                                    <br>
                                                    <small class="text-muted">کد قیمت: {{ $movement->price_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($movement->price)
                                                    {{-- تنوع موجود --}}
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($movement->price->attributes as $attr)
                                                            <span class="badge bg-light text-dark">
                @if($attr->group && $attr->group->type == 'color')
                                                                    <span class="color-dot" style="background-color: {{ $attr->value ?? '#6c757d' }};"></span>
                                                                @endif
                                                                {{ $attr->name }}
            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{-- تنوع حذف شده --}}
                                                    <div>
        <span class="text-danger small">
            <i class="feather icon-trash-2"></i> تنوع حذف شده
        </span>
                                                        @if($movement->attributes)
                                                            @php
                                                                $deletedAttributes = json_decode($movement->attributes, true);
                                                            @endphp
                                                            @if(is_array($deletedAttributes) && count($deletedAttributes) > 0)
                                                                <div class="d-flex  gap-1 mt-1">
                                                                    @foreach($deletedAttributes as $groupName => $attributes)
                                                                        @foreach($attributes as $attr)
                                                                            <span class="badge bg-secondary bg-opacity-25 text-dark" style="font-size: 10px; padding: 2px 6px;">
                                @if($attr['value'])
                                                                                    <span class="color-dot" style="background-color: {{ $attr['value'] }}; width: 8px; height: 8px;"></span>
                                                                                @endif
                                                                                {{ $attr['name'] }}
                            </span>
                                                                        @endforeach
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif

                                            </td>
                                            <td>
                                            <span class="fw-bold {{ $movement->type == 'in' ? 'text-success' : ($movement->type == 'out' ? 'text-danger' : 'text-warning') }}">
                                                {{ $movement->type == 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                                            </span>
                                            </td>
                                            <td class="text-center">
                                                <small>{{ number_format($movement->before_stock) }}
                                                    ← {{ number_format($movement->after_stock) }}</small>
                                            </td>
                                            <td>
                                                @if($movement->order_id)
                                                    <a href="{{ route('admin.orders.show', $movement->order_id) }}" target="_blank">
                                                        سفارش #{{ $movement->order_id }}
                                                    </a>
                                                @elseif($movement->reference)
                                                    {{ $movement->reference }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $movement->description ?? '-' }}</td>
                                            <td>
                                            <span class="badge bg-secondary">
                                                {{ $movement->operator_name }}
                                            </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="feather icon-clock fa-2x d-block mb-2 text-muted"></i>
                                                <span class="text-muted">هیچ حرکتی در این انبار ثبت نشده است</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $movements->appends(request()->all())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')

@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // ========== تاریخ‌های شمسی ==========
            $('#from_date').pDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: $('#from_date').val()
            });

            $('#to_date').pDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: $('#to_date').val()
            });

            // ========== فیلتر خودکار ==========
            $('#filter-type, #filter-product').on('change', function() {
                $('#filter-form').submit();
            });

            // ========== خروجی Excel ==========
            window.exportMovements = function() {
                var queryString = window.location.search;
                window.location.href = '{{ route("admin.warehouses.movements.export", $warehouse) }}' + queryString;
            };
        });
    </script>
@endpush

@push('styles')
    <style>
        .info-box {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-box-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-left: 15px;
        }

        .info-box-icon i {
            font-size: 24px;
            color: white;
        }

        .info-box-content {
            flex: 1;
        }

        .info-box-text {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-box-number {
            font-size: 20px;
            font-weight: bold;
            color: #1c1c25;
        }

        .badge {
            padding: 5px 10px;
            font-size: 11px;
        }
    </style>
@endpush
