
@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/pages/order.css') }}">
    <style>

    </style>
@endpush

@section('content')

    @php
        $totalAmount = [($orderItem->shipping_cost ?? 0)];
        $totalDiscount = 0;
        $totalDiscountAmount = 0;
        $finalPayable = [($orderItem->shipping_cost ?? 0)];
        $products = $orderItem->products();
    @endphp

    @foreach($products as $index => $item)
        @php
            $itemPrice = $item->real_price * $item->quantity;
            $itemDiscountPercent = ($item->discount ?? 0);
            $itemDiscountAmount = ($itemPrice * $itemDiscountPercent / 100);
            $itemFinal = $itemPrice - $itemDiscountAmount;
            $totalDiscount += $itemDiscountAmount;
            $totalAmount[] += $itemFinal;
        @endphp
    @endforeach

    @php
        $disableStatus = false;
        if (isset($orderCanceled) && $orderCanceled->orderCanceled) { $disableStatus = true; }
        if ($orderItem->order->status != 'paid') { $disableStatus = true; }
        if ($orderItem->refunded) { $disableStatus = true; }
        if ($orderItem->shipping_status == 'canceled') { $disableStatus = true; }

             // وضعیت‌های ارسال برای نوار پیشرفت
        $statusSteps = ['w-pending', 'pending', 'processing', 'waiting', 'sent', 'post-sent', 'delivered'];
        $currentStepIndex = array_search($orderItem->shipping_status, $statusSteps);
    @endphp

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            {{-- Header Row --}}
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <div class="oi-breadcrumb">
                        <span>پنل مدیریت</span>
                        <span class="sep">›</span>
                        <span>مدیریت سفارشات</span>
                        <span class="sep">›</span>
                        <span>مرسوله‌ها</span>
                        <span class="sep">›</span>
                        <span class="active">مرسوله #{{ $orderItem->id }}</span>
                    </div>
                    <h4 style="font-size:22px; font-weight:800; color:var(--gray-800); margin: 6px 0 0;">
                        جزئیات مرسوله
                        <span style="color:var(--primary);">#{{ $orderItem->id }}</span>
                    </h4>
                </div>
                <div class="actions-row">
                    <a href="{{ route('admin.orders.print', ['order' => $orderItem->order()->first(),'seller_id'=>$orderItem->seller_id]) }}"
                       target="_blank" class="btn-oi-outline">
                        <i class="feather icon-printer"></i> چاپ
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="btn-oi-outline info">
                        <i class="feather icon-arrow-right"></i> بازگشت
                    </a>
                </div>
            </div>

            {{-- Status Bar --}}
            <div class="status-bar section-gap">

                <div class="row">
                    <div style="width:100%;">
                        <div class="sb-title">
                            <span style="width:28px;height:28px;background:var(--primary-light);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;">⚙️</span>
                            تغییر وضعیت مرسوله
                        </div>
                    </div>
                    <div class="form-group" style="min-width:220px;">
                        <label>وضعیت ارسال</label>
                        <select class="form-control" id="shipping-status-change" {{ $disableStatus ? 'disabled' : '' }}>
                            @foreach([
                                'w-pending'  => 'در انتظار بررسی',
                                'pending' => 'در حال پردازش',
                                'waiting'    => 'منتظر ارسال',
                                'sent'       => 'ارسال شد',
                                'post-sent'  => 'تحویل به پست',
                                'delivered'  => 'تحویل داده شد',
                                'canceled'   => 'لغو شد',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ $orderItem->shipping_status == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-1" style="margin-top: 28px">
                        <button class="btn-oi-primary" id="update-status"
                                {{ $disableStatus ? 'disabled' : '' }}
                                data-action="{{ route('admin.orders.order-item.update-status', $orderItem) }}">
                            <i class="feather icon-save"></i> ذخیره
                        </button>
                    </div>


                    @if($orderItem->order && $orderItem->order->status != 'paid')
                        <div class="oi-alert warning" style="width:100%;">
                            <i class="feather icon-alert-circle"></i>
                            سفارش اصلی هنوز پرداخت نشده است. تغییر وضعیت ارسال فقط پس از پرداخت امکان‌پذیر است.
                        </div>
                    @endif
                    @if(isset($orderCanceled) && $orderCanceled->orderCanceled)
                        <div class="oi-alert danger" style="width:100%;">
                            <i class="feather icon-info"></i>
                            سفارش قبلاً لغو شده و وجه آن برگشت داده شده است. امکان تغییر وضعیت وجود ندارد.
                        </div>
                    @endif

                </div>


                {{-- نوار پیشرفت وضعیت --}}
                @if($orderItem->shipping_status != 'canceled' && $currentStepIndex !== false)
                    <div class="row">
                        <div class="status-timeline mt-3">
                            @foreach($statusSteps as $index => $step)
                                <div class="status-step {{ $index < $currentStepIndex ? 'active' : ($index == $currentStepIndex ? 'current' : '') }}"></div>
                            @endforeach
                        </div>
                    </div>

                @endif

            </div>

            {{-- Stat Cards Row --}}
            <div class="row section-gap" style="row-gap:16px;">

                {{-- Invoice Card --}}
                <div class="col-md-4">
                    <div class="stat-card sc-success" style="height:100%;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:13px;font-weight:700;color:#065f46;">📄 فاکتور مرسوله</span>
                        </div>
                        <div class="sc-divider"></div>
                        <div class="row" style="row-gap:10px;">
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">شماره مرسوله</span>
                                    <span class="val">#{{ $orderItem->id }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">تاریخ ثبت</span>
                                    <span class="val" style="font-size:12px;">{{ jdate($orderItem->created_at)->format('Y/m/d - H:i') }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">وضعیت پرداخت</span>
                                    <span class="val">
                                        <span class="status-badge {{ $orderItem->order->status == 'paid' ? 'sb-success' : 'sb-warning' }}">
                                            {{ $orderItem->order->status == 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">نحوه پرداخت</span>
                                    <span class="val">
                                        @if($orderItem->order->status == 'paid')
                                            {{ $orderItem->order->gateway == 'wallet' ? 'کیف پول' : $orderItem->order->gateway }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="sc-divider"></div>
                        <div class="info-row">
                            <span class="lbl">مبلغ پرداخت شده</span>
                            <span class="stat-price">{{ number_format(array_sum($totalAmount)) }} تومان</span>
                        </div>
                    </div>
                </div>

                {{-- Seller Card --}}
                <div class="col-md-4">
                    <div class="stat-card sc-info" style="height:100%;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:13px;font-weight:700;color:#0c4a6e;">🏪 اطلاعات فروشنده</span>
                            @if($orderItem->seller_id)
                                <a href="{{ route('admin.sellers.show', $orderItem->seller) }}"
                                   style="font-size:12px;color:var(--info);display:flex;align-items:center;gap:4px;">
                                    <i class="feather icon-external-link" style="font-size:13px;"></i> مشاهده
                                </a>
                            @endif
                        </div>
                        <div class="sc-divider"></div>
                        <div class="info-row mb-2">
                            <span class="lbl">نام فروشنده</span>
                            <span class="val">
                                {{ $sellerName }}
                                @if($orderItem->seller && $orderItem->seller->mobile)
                                    <small style="display:block;color:var(--gray-400);font-weight:500;margin-top:2px;">{{ $orderItem->seller->mobile }}</small>
                                @endif
                            </span>
                        </div>
                        <div class="row" style="row-gap:10px;">
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">کمیسیون</span>
                                    <span class="val">
                                        @if($orderItem->order->getAmountSellerCommission() and $orderItem->seller_id)
                                            {{ number_format($orderItem->order->getAmountSellerCommission()['priceForSite']) }} تومان
                                            <small style="color:var(--gray-400);">({{ $orderItem->order->getAmountSellerCommission()['commission'] }}%)</small>
                                        @else —
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-row">
                                    <span class="lbl">سهم فروشنده</span>
                                    <span class="val" style="color:var(--info);">
                                        @if($orderItem->order->getAmountSellerCommission() and $orderItem->seller_id)
                                            {{ number_format($orderItem->order->priceSeller() - $orderItem->order->getAmountSellerCommission()['priceForSite']) }} تومان
                                        @else —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Status Card --}}
                @php
                    $scClass = match($orderItem->shipping_status) {
                        'sent', 'delivered' => 'sc-success',
                        'canceled' => 'sc-danger',
                        default => 'sc-warning'
                    };
                    $sbClass = match($orderItem->shipping_status) {
                        'sent' => 'sb-success',
                        'delivered' => 'sb-info',
                        'canceled' => 'sb-danger',
                        default => 'sb-warning'
                    };
                    $statusLabels = [
                        'w-pending'  => 'در انتظار بررسی',
                        'pending'    => 'در حال بررسی',
                        'processing' => 'در حال پردازش',
                        'waiting'    => 'منتظر ارسال',
                        'sent'       => 'ارسال شد',
                        'post-sent'  => 'تحویل به پست',
                        'delivered'  => 'تحویل داده شد',
                        'canceled'   => 'لغو شد',
                    ];
                @endphp
                <div class="col-md-4">
                    <div class="stat-card {{ $scClass }}" style="height:100%;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:13px;font-weight:700;">⚡ وضعیت مرسوله</span>
                        </div>
                        <div class="sc-divider"></div>
                        <div class="info-row mb-3">
                            <span class="lbl">وضعیت ارسال</span>
                            <span class="val">
                                <span class="status-badge {{ $sbClass }}">
                                    {{ $statusLabels[$orderItem->shipping_status] ?? $orderItem->shipping_status }}
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="lbl">کد رهگیری</span>
                            <div class="tracking-group mt-1">
                                <input type="text" id="tracking-code"
                                       value="{{ $orderItem->tracking_code }}"
                                       placeholder="کد رهگیری..."
                                    {{ $disableStatus ? 'disabled' : '' }}>
                                <button class="btn-track" id="update-tracking"
                                        {{ $disableStatus ? 'disabled' : '' }}
                                        data-action="{{ route('admin.orders.order-item.update-tracking', $orderItem) }}">
                                    <i class="feather icon-save"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recipient + Address --}}
            <div class="row section-gap" style="row-gap:16px;">
                <div class="col-md-6">
                    <div class="oi-card">
                        <div class="oi-card-header">
                            <h5 class="oi-card-title">
                                <span class="oi-card-icon" style="background:#f5f3ff;">👤</span>
                                اطلاعات گیرنده
                            </h5>
                            <a href="{{ route('admin.users.show', $orderItem->order->user) }}"
                               style="font-size:12px;color:var(--primary);display:flex;align-items:center;gap:4px;">
                                <i class="feather icon-external-link" style="font-size:13px;"></i> مشاهده
                            </a>
                        </div>
                        <div class="oi-card-body">
                            <div class="row" style="row-gap:14px;">
                                <div class="col-6"><div class="info-row"><span class="lbl">نام و نام خانوادگی</span><span class="val">{{ $orderItem->order->name ?? '—' }}</span></div></div>
                                <div class="col-6"><div class="info-row"><span class="lbl">نام کاربری</span><span class="val">{{ $orderItem->order->user->username ?? '—' }}</span></div></div>
                                <div class="col-6"><div class="info-row"><span class="lbl">موبایل</span><span class="val" style="direction:ltr;display:block;">{{ $orderItem->order->mobile ?? '—' }}</span></div></div>
                                <div class="col-6"><div class="info-row"><span class="lbl">ایمیل</span><span class="val" style="direction:ltr;display:block;font-size:12px;">{{ $orderItem->order->user->email ?? '—' }}</span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="oi-card">
                        <div class="oi-card-header">
                            <h5 class="oi-card-title">
                                <span class="oi-card-icon" style="background:#eff6ff;">📍</span>
                                آدرس ارسال
                            </h5>
                            @if($orderItem->order->location)
                                <button type="button" class="map-btn show-location-btn" data-toggle="modal" data-target="#locationMapModal">
                                    <i class="feather icon-map-pin" style="font-size:13px;"></i> نقشه
                                </button>
                            @endif
                        </div>
                        <div class="oi-card-body">
                            @if($orderItem->order && $orderItem->order->hasPhysicalProduct())
                                <div class="row" style="row-gap:14px;">
                                    <div class="col-6">
                                        <div class="info-row">
                                            <span class="lbl">استان / شهر</span>
                                            <span class="val">{{ $orderItem->order->province->name ?? '' }} — {{ $orderItem->order->city->name ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-row">
                                            <span class="lbl">کد پستی</span>
                                            <span class="val" style="direction:ltr;display:block;">{{ $orderItem->order->postal_code ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-row">
                                            <span class="lbl">آدرس کامل</span>
                                            <span class="val" style="line-height:1.6;">{{ $orderItem->order->address ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-row">
                                            <span class="lbl">شیوه تحویل</span>
                                            <span class="val">{{ $orderItem->carrier ? $orderItem->carrier->title : 'پیک' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        @php
                                            $deliveryDate = null;
                                            if($orderItem->delivery_date) {
                                                try { $deliveryDate = jdate($orderItem->delivery_date)->format('Y/m/d'); }
                                                catch(Exception $e) { $deliveryDate = $orderItem->delivery_date; }
                                            }
                                        @endphp
                                        <div class="info-row">
                                            <span class="lbl">تاریخ تحویل</span>
                                            <span class="val">{{ $deliveryDate ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p style="font-size:13px;color:var(--gray-400);margin:0;">این سفارش محصول فیزیکی ندارد.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="oi-card section-gap">
                <div class="oi-card-header">
                    <h5 class="oi-card-title">
                        <span class="oi-card-icon" style="background:#fff7ed;">📦</span>
                        جزئیات محصولات
                    </h5>
                </div>
                <div class="oi-card-body" style="padding:0;">
                    <div class="table-responsive">
                        @php
                            $totalAmount = 0;
                            $totalDiscount = 0;
                            $products = $orderItem->products();
                        @endphp
                        <table class="product-table">
                            <thead>
                            <tr>
                                <th class="text-center" width="48">#</th>
                                <th width="72">تصویر</th>
                                <th>عنوان محصول</th>
                                <th width="90" class="text-center">کد</th>
                                <th width="70" class="text-center">تعداد</th>
                                <th width="120" class="text-center">قیمت واحد</th>
                                <th width="100" class="text-center">تخفیف</th>
                                <th width="140" class="text-center">قیمت نهایی</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $index => $item)
                                @php
                                    $itemPrice = $item->real_price * $item->quantity;
                                    $itemDiscountPercent = ($item->discount ?? 0);
                                    $itemDiscountAmount = ($itemPrice * $itemDiscountPercent / 100);
                                    $itemFinal = $itemPrice - $itemDiscountAmount;
                                    $totalDiscount += $itemDiscountAmount;
                                    $totalAmount += $itemFinal;
                                @endphp
                                <tr>
                                    <td class="text-center" style="color:var(--gray-400);font-weight:600;">{{ $loop->iteration }}</td>
                                    <td>
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset($item->product->image) }}" class="product-img" alt="">
                                        @else
                                            <img src="{{ asset('empty.svg') }}" class="product-img" alt="">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('front.products.show', $item->product) }}" class="product-title-link">
                                            {{ $item->title }}
                                        </a>
                                        @if($item->product && $item->product->title_fa)
                                            <div class="product-fa-title">{{ $item->product->title_fa }}</div>
                                        @endif

                                        @if($item->get_price && $orderItem->attributes)
                                            @php
                                                $attributes = json_decode($orderItem->attributes, true);
                                                $colorAttributes = [];
                                                $otherAttributes = [];
                                                foreach ($attributes as $groupName => $groupAttributes) {
                                                    if ($groupName == 'رنگ') { $colorAttributes = [$groupName => $groupAttributes]; }
                                                    else { $otherAttributes[$groupName] = $groupAttributes; }
                                                }
                                                $sortedAttributes = array_merge($colorAttributes, $otherAttributes);
                                            @endphp
                                            <div style="margin-top:6px;">
                                                @foreach($sortedAttributes as $groupName => $groupAttributes)
                                                    @foreach($groupAttributes as $attribute)
                                                        <span class="attr-chip">
                                                            @if($groupName == 'رنگ')
                                                                <span class="attr-color-dot" style="background-color:{{ $attribute['value'] }};"></span>
                                                            @elseif($groupName == 'گارانتی')
                                                                <i class="fas fa-shield-alt" style="font-size:10px;"></i>
                                                            @elseif($groupName == 'سایز')
                                                                <i class="fas fa-ruler" style="font-size:10px;"></i>
                                                            @endif
                                                            {{ $groupName }}: {{ $attribute['name'] }}
                                                        </span>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center" style="color:var(--gray-400);font-size:12px;">{{ $item->product_id ?? '—' }}</td>
                                    <td class="text-center">
                                        <span style="background:var(--gray-100);padding:3px 10px;border-radius:12px;font-weight:700;font-size:13px;">{{ number_format($item->quantity) }}</span>
                                    </td>
                                    <td class="text-center" style="font-weight:600;">{{ number_format($item->real_price) }}<small style="color:var(--gray-400);font-size:11px;"> ت</small></td>
                                    <td class="text-center">
                                        @if($item->discount)
                                            <span style="background:var(--danger-light);color:var(--danger);border-radius:12px;padding:3px 9px;font-size:12px;font-weight:700;">{{ $item->discount }}%</span>
                                        @else
                                            <span style="color:var(--gray-400);">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span style="color:var(--success);font-weight:800;font-size:14px;">{{ number_format($itemFinal) }}</span>
                                        <small style="color:var(--gray-400);font-size:11px;"> تومان</small>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="6" class="text-left" style="color:var(--gray-600);font-weight:600;">جمع تخفیف‌ها:</td>
                                <td class="text-center tfoot-discount">{{ number_format($totalDiscount) }} تومان</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-left" style="color:var(--gray-600);font-weight:600;">جمع کل محصولات:</td>
                                <td colspan="2" class="text-center" style="font-weight:800;font-size:15px;">{{ number_format($totalAmount) }} تومان</td>
                            </tr>
                            @if(($orderItem->shipping_cost ?? 0) > 0)
                                <tr>
                                    <td colspan="6" class="text-left" style="color:var(--gray-600);">هزینه ارسال:</td>
                                    <td colspan="2" class="text-center">{{ number_format($orderItem->shipping_cost) }} تومان</td>
                                </tr>
                            @endif
                            @php $finalPayable = $totalAmount + ($orderItem->shipping_cost ?? 0); @endphp
                            <tr class="tfoot-row-total">
                                <td colspan="6" class="text-left" style="font-size:15px;">مبلغ قابل پرداخت نهایی:</td>
                                <td colspan="2" class="text-center tfoot-price">{{ number_format($finalPayable) }} تومان</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($orderItem->order->description)
                <div class="oi-card section-gap">
                    <div class="oi-card-header">
                        <h5 class="oi-card-title">
                            <span class="oi-card-icon" style="background:#f0fdf4;">📝</span>
                            توضیحات سفارش
                        </h5>
                    </div>
                    <div class="oi-card-body">
                        <div class="desc-block">{{ $orderItem->order->description }}</div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade oi-modal" id="cancelOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">⚠️ تأیید لغو سفارش</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="oi-alert warning" id="refund-info" style="flex-direction:column;gap:8px;">
                        <div style="display:flex;align-items:flex-start;gap:8px;">
                            <i class="fa fa-info-circle" style="margin-top:2px;"></i>
                            <span>در صورت تایید، مجموعا <strong id="order-amount">{{ number_format($finalPayable) }}</strong> تومان به کیف پول کاربر برگشت داده می‌شود.</span>
                        </div>

                        @if(isset($orderItem) && $orderItem->seller_id)
                            <div class="oi-alert warning" style="margin-top:4px;">
                                <i class="fa fa-exchange-alt"></i>
                                <div>
                                    <strong>گردش مالی:</strong>
                                    <ul style="margin:6px 0 0;padding-right:16px;font-size:12px;">
                                        <li>کسر از کیف پول فروشنده: <strong>{{ number_format($orderItem->order->priceSeller()-$orderItem->order->getAmountSellerCommission()['priceForSite']) }} تومان</strong></li>
                                        <li>افزودن به کیف پول کاربر: <strong>{{ number_format($finalPayable) }} تومان</strong></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="oi-alert danger" style="margin-top:4px;">
                                <i class="fa fa-exclamation-triangle"></i>
                                <div style="font-size:12px;">
                                    لغو سفارش فقط در صورتی انجام می‌شود که موجودی کیف پول فروشنده حداقل
                                    <strong>{{ number_format($orderItem->order->priceSeller()-$orderItem->order->getAmountSellerCommission()['priceForSite']) }} تومان</strong> باشد.
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="form-group mt-3">
                        <label style="font-size:12px;font-weight:600;color:var(--gray-600);">دلیل لغو سفارش</label>
                        <textarea class="form-control" id="cancel_reason" rows="3" placeholder="دلیل لغو سفارش را وارد کنید..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-danger" id="confirm-cancel">تأیید و لغو سفارش</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Map Modal --}}
    <div class="modal fade oi-modal" id="locationMapModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="feather icon-map-pin" style="color:var(--danger);"></i> موقعیت مکانی</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div id="map" style="height:420px;width:100%;border-radius:0 0 var(--radius) var(--radius);"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['map']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/orders/order-item.js') }}"></script>
    <script>
        @php
            $lat = null; $lng = null;
            if ($orderItem->order->location) {
                $location = explode(',', $orderItem->order->location);
                $lat = $location[0]; $lng = $location[1];
            }
        @endphp
        var info_map_type    = "{{ option('info_map_type', 'google') }}";
        var info_latitude    = "{{ $lat }}";
        var info_Longitude   = "{{ $lng }}";
        var info_site_title  = "{{ option('info_site_title', 'او پی شاپ') }}";
        var mapIrApiKey      = '{{ option('map_api') }}';
    </script>
@endpush
