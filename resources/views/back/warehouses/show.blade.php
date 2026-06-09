@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/warehouses.css') }}">
@endpush

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            {{-- هدایت --}}
            <div class="content-header row d-flex justify-content-between">
                <div class="content-header-left  mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.warehouses.index') }}">انبارها</a></li>
                                    <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right ">
                    <div class="btn-group float-left">
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning btn-sm">
                            <i class="feather icon-edit"></i> ویرایش
                        </a>
                        <a href="{{route('admin.products.create')}}" class="btn btn-success btn-sm">
                            <i class=" fas fa-plus"></i> افزودن محصول جدید
                        </a>
                        <a href="{{ route('admin.warehouses.movements', $warehouse) }}" class="btn btn-info btn-sm">
                            <i class="feather icon-list"></i> حرکات
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                {{-- ========== بخش 1: اطلاعات انبار ========== --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="feather icon-home text-primary"></i> اطلاعات انبار</h4>
                        <div>
                            <span class="badge bg-secondary">کد: {{ $warehouse->code }}</span>
                            @if($warehouse->is_active)
                                <span class="badge bg-success"><i class="feather icon-check"></i> فعال</span>
                            @else
                                <span class="badge bg-danger"><i class="feather icon-x"></i> غیرفعال</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th width="150">نام انبار:</th>
                                        <td>{{ $warehouse->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>نوع انبار:</th>
                                        <td>@if($warehouse->type == 'main')
                                                اصلی
                                            @elseif($warehouse->type == 'seller')
                                                فروشنده
                                            @else
                                                موقت
                                            @endif</td>
                                    </tr>
                                    <tr>
                                        <th>مدیر انبار:</th>
                                        <td>{{ $warehouse->manager_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تلفن:</th>
                                        <td>{{ $warehouse->phone ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th width="150">آدرس:</th>
                                        <td>{{ $warehouse->address ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>توضیحات:</th>
                                        <td>{{ $warehouse->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ایجاد:</th>
                                        <td>{{ jdate($warehouse->created_at)->format('d F Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== بخش 2: آمار و کارت‌ها ========== --}}

                <div class="card">
                    <div class="card-body">
                        {{-- ردیف اول: آمار پایه --}}
                        <div class="row">
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="feather icon-box"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">تعداد محصولات</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['total_products'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="feather icon-package"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">تنوع در این انبار</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['current_count'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="feather icon-database"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">کل موجودی</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['total_stock_current'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gradient-orange"><i
                                            class="feather icon-shopping-cart"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">کل فروش</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['total_sold'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ردیف دوم: آمار وضعیت --}}
                        <div class="row">
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i
                                            class="feather icon-alert-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">موجودی کم</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['low_stock'] ?? 0) }}</span>
                                        <small class="text-muted">کمتر از 5 عدد</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gradient-danger"><i class="feather icon-x-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ناموجود</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['out_of_stock'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i
                                            class="feather icon-alert-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">موجودی بحرانی</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['critical_stock'] ?? 0) }}</span>
                                        <small class="text-muted">کمتر از 2 عدد</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="feather icon-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">تخفیف‌های در حال انقضا</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['expiring_discounts'] ?? 0) }}</span>
                                        <small class="text-muted">3 روز آینده</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="card">
                    <div class="card-body">
                        {{-- ردیف سوم: آمار عملکرد --}}
                        <div class="row">
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gradient-purple"><i
                                            class="feather icon-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">پرفروش‌ترین تنوع</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['best_seller_count'] ?? 0) }}</span>
                                        <small class="text-muted">{{ $stats['best_seller_attributes'] ?? '-' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary"><i class="feather icon-award"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">میانگین فروش/تنوع</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['avg_sold_per_variation'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gradient-teal"><i
                                            class="feather icon-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ارزش کل موجودی</span>
                                        <span class="info-box-number">{{ number_format($stats['total_value'] ?? 0) }} تومان</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ردیف چهارم: آمار مقایسه‌ای --}}
                        <div class="row">
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-dark"><i class="feather icon-grid"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">تنوع در سایر انبارهای شما</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['main_count'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="feather icon-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">تنوع فروشندگان دیگر</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['other_sellers_count'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 stat-card">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i
                                            class="feather icon-bar-chart-2"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">جمع کل تنوع‌ها</span>
                                        <span
                                            class="info-box-number">{{ number_format($stats['total_variations'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ========== بخش 3: نمودارها ========== --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="feather icon-trending-up"></i> 10 محصول پرفروش</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="topProductsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="feather icon-calendar"></i> فروش ماهانه (6 ماه اخیر)
                                </h4>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlySalesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== بخش 4: فیلتر و محصولات ========== --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title"><i class="feather icon-list text-primary"></i> محصولات انبار</h4>
                        <div>
                            <a href="{{route('admin.products.create')}}" class="btn btn-success btn-sm">
                                <i class=" fas fa-plus"></i> افزودن محصول جدید
                            </a>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#exportExcelModal">
                                <i class="feather icon-download"></i> اکسل
                            </button>
                            {{-- <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#exportPdfModal">
                                 <i class="feather icon-file-text"></i> PDF
                             </button>--}}
                            <button type="button" id="refresh-btn" class="btn btn-secondary btn-sm">
                                <i class="feather icon-refresh-cw"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>جستجوی محصول</label>
                                        <input type="text" id="search-product" class="form-control"
                                               placeholder="نام محصول، کد...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>وضعیت موجودی</label>
                                        <select id="stock-filter" class="form-control">
                                            <option value="all">همه محصولات</option>
                                            <option value="in_stock">موجود</option>
                                            <option value="low_stock">موجودی کم (&lt;5)</option>
                                            <option value="critical_stock">بحرانی (&lt;2)</option>
                                            <option value="out_of_stock">ناموجود</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>مرتب‌سازی</label>
                                        <select id="sort-filter" class="form-control">
                                            <option value="title_asc">عنوان (الفبا)</option>
                                            <option value="title_desc">عنوان (معکوس)</option>
                                            <option value="stock_desc">بیشترین موجودی</option>
                                            <option value="stock_asc">کمترین موجودی</option>
                                            <option value="sold_desc">پرفروش‌ترین</option>
                                            <option value="price_desc">گران‌ترین</option>
                                            <option value="price_asc">ارزان‌ترین</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label>&nbsp;</label>
                                        <button id="filter-btn" class="btn btn-primary btn-block">
                                            <i class="feather icon-filter"></i> فیلتر
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="products-container">
                            @include('back.warehouses.partials.products-list', ['products' => $products])
                        </div>
                    </div>
                </div>

                {{-- ========== بخش 5: آخرین حرکات انبار ========== --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title"><i class="feather icon-clock text-primary"></i> آخرین حرکات انبار</h4>
                        <a href="{{ route('admin.warehouses.movements', $warehouse) }}"
                           class="btn btn-sm btn-outline-info">
                            <i class="feather icon-arrow-left"></i> مشاهده همه
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                <tr>
                                    <th>تاریخ و زمان</th>
                                    <th>نوع</th>
                                    <th>محصول</th>
                                    <th>تنوع</th>
                                    <th class="text-center">تعداد</th>
                                    <th class="text-center">موجودی قبل/بعد</th>
                                    <th>توضیحات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recentMovements as $movement)
                                    <tr>
                                        <td>{{ jdate($movement->created_at)->format('d F Y H:i') }}</td>
                                        <td>
                                            <span class="badge {{ $movement->type_badge_class }}">
                                                {{ $movement->type_label }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($movement->product)
                                                <a href="{{ route('admin.products.edit', $movement->product) }}"
                                                   target="_blank">
                                                    {{ $movement->product->title }}
                                                </a>
                                            @else
                                                <span class="text-muted">محصول حذف شده</span>
                                            @endif

                                        </td>
                                        <td>
                                            @if($movement->price)
                                                {{-- تنوع موجود --}}
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($movement->price->attributes as $attr)
                                                        <span class="badge bg-light text-dark">
                @if($attr->group && $attr->group->type == 'color')
                                                                <span class="color-dot"
                                                                      style="background-color: {{ $attr->value ?? '#6c757d' }};"></span>
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
                                                                        <span
                                                                            class="badge bg-secondary bg-opacity-25 text-dark"
                                                                            style="font-size: 10px; padding: 2px 6px;">
                                @if($attr['value'])
                                                                                <span class="color-dot"
                                                                                      style="background-color: {{ $attr['value'] }}; width: 8px; height: 8px;"></span>
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
                                        <td class="text-center fw-bold">{{ number_format($movement->quantity) }}</td>
                                        <td class="text-center">
                                            <small>{{ number_format($movement->before_stock) }}
                                                ← {{ number_format($movement->after_stock) }}</small>
                                        </td>
                                        <td>{{ Str::limit($movement->description ?? '-', 50) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">هیچ حرکتی ثبت نشده است</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="quick-actions">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="feather icon-zap"></i> عملیات سریع
            </button>
            <div class="dropdown-menu dropdown-menu-left">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkStockModal">
                    <i class="feather icon-edit"></i> بروزرسانی گروهی موجودی
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#stockTakeModal">
                    <i class="feather icon-clipboard"></i> سرشماری انبار
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{route('admin.products.create')}}">
                    <i class=" fas fa-plus"></i> افزودن محصول جدید
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" data-toggle="modal" data-target="#exportExcelModal">
                    <i class="feather icon-download"></i> خروجی اکسل
                </a>
                {{--<a class="dropdown-item" href="#" data-toggle="modal" data-target="#exportPdfModal">
                <i class="feather icon-file-text"></i> خروجی PDF
                </a>--}}
            </div>
        </div>
    </div>



    {{-- ===== مودال‌ها ===== --}}
    @include('back.warehouses.partials.modal-bulk-stock')
    @include('back.warehouses.partials.modal-stock-take')
    @include('back.warehouses.partials.modal-export-excel')
    @include('back.warehouses.partials.modal-export-pdf')
    @include('back.warehouses.partials.modal-stock-history')

@endsection
@include('back.partials.plugins', ['plugins' => ['chart', 'persian-datepicker']])
@push('scripts')
    <script>
        var WAREHOUSE_ID = {{ $warehouse->id }};
        var stockTakeAPI = '{{ route("admin.warehouses.stock-take-data", $warehouse) }}';
        var bulkStockDataAPI = '{{ route("admin.warehouses.bulk-stock-data", $warehouse) }}';
        var SUBMIT_STM_URL = '{{ route("admin.warehouses.stock-take", $warehouse) }}';
        var SUBMIT_BSM_URL = '{{ route("admin.warehouses.bulk-stock-update", $warehouse) }}';
        var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var topProducts = @json($chartData['top_products'] ?? []);
        var monthlySales = @json($chartData['monthly_sales'] ?? []);
        var warehousesProducts = '{{ route("admin.warehouses.products", $warehouse) }}';

    </script>
    <script src="{{ asset('back/assets/js/pages/warehouses/show.js') }}"></script>

@endpush
