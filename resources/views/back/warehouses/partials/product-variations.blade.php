@extends('back.layouts.master')

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
        .info-box-icon i { font-size: 24px; color: white; }
        .info-box-content { flex: 1; }
        .info-box-text { font-size: 12px; color: #6c757d; margin-bottom: 5px; }
        .info-box-number { font-size: 20px; font-weight: bold; color: #1c1c25; }
        .badge { padding: 5px 10px; font-size: 11px; }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14, #dc3545); }
        .progress-sold { height: 4px; background-color: #e9ecef; border-radius: 2px; overflow: hidden; margin-top: 5px; }
        .progress-sold-bar { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); border-radius: 2px; }
        .table tbody tr:hover { background-color: rgba(0,123,255,0.05); }
        .best-seller-row { background-color: rgba(255,193,7,0.1) !important; border-right: 3px solid #ffc107; }
        .modal .stm-close {
            color: rgb(255, 255, 255);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1;
            position: relative;
            font-size: 1.1rem;
            line-height: 1;
            margin-right: auto;
            margin-left: 0px;
            background: rgba(255, 255, 255, 0.15);
            border-width: medium;
            border-style: none;
            border-color: currentcolor;
            border-image: initial;
            border-radius: 8px;
            transition: background 0.15s;
            padding: 0px;
        }
      .modal .form-group{
          display: flex;
          flex-direction: column;
      }
        .select2-container,.select2-results{
            direction: rtl;
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
                                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">مدیریت انبارها</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse) }}">{{$warehouse->name}}</a></li>
                                    <li class="breadcrumb-item active">تنوع ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                {{-- هدر محصول --}}
                <div class="row mb-4">
                    <div class="col-md-2 text-center">
                        <img src="{{ $product->image ? asset($product->image) : asset('empty.svg') }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px;">
                    </div>
                    <div class="col-md-10">
                        <h4 class="mb-1">{{ $product->title }}</h4>
                        <div class="flex-wrap gap-3">
                            <span class="badge bg-secondary">کد محصول: {{ $product->id }}</span>
                            @if($product->category)<span class="badge bg-info">دسته: {{ $product->category->title }}</span>@endif
                            @if($product->brand)<span class="badge bg-primary">برند: {{ $product->brand->name }}</span>@endif
                        </div>
                    </div>
                </div>

                {{-- آمار کلی --}}

                <div class="card" id="variation-stats">
                    @include('back.warehouses.partials.product-variation-stats')
                </div>
                {{-- جمع‌بندی --}}
                @php
                    $currentCount = $currentWarehouseVariations->count() ?? 0;
                    $otherSellerCount = $otherSellerVariations->count() ?? 0;
                    $mainCount = $mainWarehouseVariations->count() ?? 0;
                    $otherSellersCount = $otherSellersVariations->count() ?? 0;
                    $totalVariations = $currentCount + $otherSellerCount + $mainCount + $otherSellersCount;
                    $currentWarehouseCount = 1;
                    $otherSellerWarehouseCount = $otherSellerWarehouses->count() ?? 0;
                    $mainWarehouseCount = $mainWarehouseVariations->groupBy('warehouse_id')->count() ?? 0;
                    $otherSellersWarehouseCount = $otherSellersVariations->groupBy('warehouse_id')->count() ?? 0;
                    $totalWarehouses = $currentWarehouseCount + $otherSellerWarehouseCount + $mainWarehouseCount + $otherSellersWarehouseCount;
                @endphp

                <div class="alert alert-info">
                    <i class="feather icon-info"></i>
                    <strong>جمع‌بندی:</strong>
                    این محصول در مجموع دارای <strong>{{ number_format($totalVariations) }}</strong> تنوع
                    در <strong>{{ number_format($totalWarehouses) }}</strong> انبار مختلف است.
                    @if($totalWarehouses > 1)
                        <small class="d-block mt-1 text-muted">
                            (انبار فعلی: {{ $currentCount }} تنوع، سایر انبارهای شما: {{ $otherSellerCount }} تنوع،
                            فروشگاه اصلی: {{ $mainCount }} تنوع، فروشندگان دیگر: {{ $otherSellersCount }} تنوع)
                        </small>
                    @endif
                </div>

                {{-- تنوع‌های انبار فعلی (با ستون فروش) --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary pb-2 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="feather icon-home"></i> تنوع‌های موجود در انبار {{ $warehouse->name }}
                            <span class="badge bg-light text-dark ms-2">{{ $currentWarehouseVariations->count() }} تنوع</span>
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addVariationModal">
                            <i class="feather icon-plus"></i> افزودن تنوع جدید
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="variationsTable">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ویژگی‌ها</th>
                                    <th class="text-center">قیمت</th>
                                    <th class="text-center">قیمت با تخفیف</th>
                                    <th class="text-center">موجودی</th>
                                    <th class="text-center">تعداد فروش</th>
                                    <th class="text-center">وضعیت</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($currentWarehouseVariations as $index => $price)
                                    @include('back.warehouses.partials.product-variation-row',['price'=>$price])
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="8" class="text-center">هیچ تنوعی در این انبار وجود ندارد</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light">
                                <tr>
                                    <th colspan="4" class="text-end">جمع کل:</th>
                                    <th class="text-center">{{ number_format($currentWarehouseVariations->sum('stock')) }}</th>
                                    <th class="text-center">{{ number_format($currentWarehouseVariations->sum('sold_count')) }}</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- پرفروش‌ترین تنوع‌ها (۵ تای اول) --}}
                @php $topSellers = $currentWarehouseVariations->where('sold_count', '>', 0)->take(5); @endphp
                @if($topSellers->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-gradient-orange text-white p-1">
                            <h5 class="mb-0"><i class="feather icon-trending-up"></i> پرفروش‌ترین تنوع‌های این انبار</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($topSellers as $price)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                            <div>
                                                <strong>@foreach($price->attributes as $attr){{ $attr->name }} @endforeach</strong>
                                                <br><small class="text-muted">فروش: {{ number_format($price->sold_count) }} عدد</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">{{ number_format($price->price) }} تومان</span>
                                                <div class="progress-sold mt-1" style="width: 150px;">
                                                    <div class="progress-sold-bar" style="width: {{ ($price->sold_count / $topSellers->first()->sold_count) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- سایر جداول (سایر انبارهای شما، فروشگاه اصلی، فروشندگان دیگر) --}}
                @if($otherSellerVariations->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="feather icon-grid"></i> همچنین دارای {{ $otherSellerVariations->count() }} تنوع در سایر انبارهای شما</h5></div>
                        <div class="card-body"><div class="table-responsive">@include('back.warehouses.partials._variations_table', ['variations' => $otherSellerVariations])</div></div>
                    </div>
                @endif

                @if(isset($mainWarehouseVariations) && $mainWarehouseVariations->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="feather icon-home"></i> همچنین دارای {{ $mainWarehouseVariations->count() }} تنوع در فروشگاه اصلی</h5></div>
                        <div class="card-body"><div class="table-responsive">@include('back.warehouses.partials._variations_table', ['variations' => $mainWarehouseVariations])</div></div>
                    </div>
                @endif

                @if($otherSellersVariations->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-white"><h5 class="mb-0"><i class="feather icon-users"></i> همچنین دارای {{ $otherSellersVariations->count() }} تنوع در انبار سایر فروشندگان</h5></div>
                        <div class="card-body"><div class="table-responsive">@include('back.warehouses.partials._variations_table', ['variations' => $otherSellersVariations])</div></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal ویرایش تنوع --}}
    <div class="modal fade" id="editVariationModal" tabindex="-1" aria-labelledby="editVariationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editVariationModalLabel">
                        <i class="feather icon-edit-2"></i> ویرایش تنوع
                    </h5>
                    <button type="button" class="stm-close" data-dismiss="modal" aria-label="Close">
                        <i class="feather icon-x"></i>
                    </button>
                </div>
                <form id="editVariationForm" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="_price_id" id="edit_price_id">
                    <div class="modal-body" id="editVariationModalBody">
                        {{-- اسکلت لودینگ --}}
                        <div class="text-center py-4" id="editLoadingSpinner">
                            <div class="spinner-border text-warning" role="status"></div>
                            <p class="mt-2">در حال بارگذاری...</p>
                        </div>
                        {{-- محتوای فرم اینجا لود میشه --}}
                        <div id="editFormContent" style="display:none;">



                            {{-- Alert خطا --}}
                            <div class="alert alert-danger d-none" id="editErrorAlert"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                            <i class="feather icon-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal افزودن تنوع جدید --}}
    <div class="modal fade" id="addVariationModal" tabindex="-1" aria-labelledby="addVariationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addVariationModalLabel">
                        <i class="feather icon-plus-circle"></i> افزودن تنوع جدید
                    </h5>
                    <button type="button" class="stm-close" data-dismiss="modal" aria-label="Close">
                        <i class="feather icon-x"></i>
                    </button>
                </div>
                <form id="addVariationForm" method="POST" action="{{ route('admin.warehouses.product-variations.store',  ['warehouse'=>$warehouse,'product'=>$product]) }}">
                    @csrf
                    <div class="modal-body">
                        @include('back.warehouses.partials.prices-template')

                        <div class="alert alert-danger d-none" id="addErrorAlert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-success" id="addSubmitBtn">
                            <i class="feather icon-save"></i> افزودن تنوع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@include('back.partials.plugins', [
    'plugins' => [
        'jquery.validate',
        'jquery-ui',
        'persian-datepicker',
    ],
])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/warehouses/product-variations.js') }}"></script>
    <script>


    </script>
@endpush
