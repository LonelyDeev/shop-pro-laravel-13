
@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/pages/order.css') }}">
    @if(function_exists('module_is_active') && module_is_active('InstallmentPayment'))
        <link rel="stylesheet" href="{{ module_asset('InstallmentPayment', 'css/installment.css') }}">
    @endif
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

        // ========== بررسی اقساطی ==========
        $installmentPlan = null;
        if (function_exists('module_is_active') && module_is_active('InstallmentPayment')) {
            $installmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $orderItem->order_id)->first();
        }
        $isInstallment = (bool) $installmentPlan;
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
                        @if($isInstallment)
                            <span class="status-badge sb-info" style="background:#dbeafe;color:#1e40af;font-size:13px;margin-right:8px;">
                                💰 سفارش اقساطی
                            </span>
                        @endif
                        {{-- Badge مرجوعی --}}
                        @if($orderItem->refunded || $orderItem->return_status !== 'none')
                            @php
                                $returnStatusLabel = [
                                    'pending'              => ['label' => 'مرجوعی در حال بررسی', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
                                    'approved'              => ['label' => 'مرجوعی تایید شد', 'color' => '#3b82f6', 'bg' => '#dbeafe'],
                                    'shipped_by_customer'  => ['label' => 'محصول ارسال شد', 'color' => '#8b5cf6', 'bg' => '#ede9fe'],
                                    'received'              => ['label' => 'محصول دریافت شد', 'color' => '#06b6d4', 'bg' => '#cffafe'],
                                    'reshipped'             => ['label' => 'ارسال مجدد', 'color' => '#6366f1', 'bg' => '#e0e7ff'],
                                    'completed'             => ['label' => 'مرجوعی تکمیل شد', 'color' => '#10b981', 'bg' => '#d1fae5'],
                                    'rejected'              => ['label' => 'مرجوعی رد شد', 'color' => '#ef4444', 'bg' => '#fee2e2'],
                                    'cancelled'             => ['label' => 'مرجوعی لغو شد', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                                    'failed'                => ['label' => 'مرجوعی ناموفق', 'color' => '#dc2626', 'bg' => '#fef2f2'],
                                ][$orderItem->return_status] ?? ['label' => 'مرجوع شده', 'color' => '#f59e0b', 'bg' => '#fffbeb'];
                            @endphp
                            <span class="status-badge" style="background:{{ $returnStatusLabel['bg'] }};color:{{ $returnStatusLabel['color'] }};font-size:13px;margin-right:8px;">
                                <i class="fas fa-undo-alt"></i> {{ $returnStatusLabel['label'] }}
                            </span>
                        @endif
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

            {{-- ======== بنر هشدار اقساطی ======== --}}
            @if($isInstallment)
                <div class="installment-order-banner installment-order-banner-{{ $installmentPlan->status }}" style="margin-bottom:16px;">
                    <div class="installment-order-banner-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="installment-order-icon mr-1">
                                <i class="fas fa-money-check-alt"></i>
                            </div>
                            <div>
                                <h5 style="margin:0;font-weight:700;">طرح اقساطی</h5>
                                <div style="font-size:12px;">
                                    @php
                                        $iStatusLabels = [
                                            'pending_down_payment' => 'در انتظار پیش‌پرداخت',
                                            'active'    => 'فعال',
                                            'completed' => 'تکمیل شده',
                                            'defaulted' => 'معوق',
                                            'cancelled' => 'لغو شده',
                                        ];
                                    @endphp
                                    <span class="status-badge sb-light">{{ $iStatusLabels[$installmentPlan->status] ?? $installmentPlan->status }}</span>
                                    <span style="opacity:0.9;margin-right:8px;">شناسه: #{{ $installmentPlan->id }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            @can('installment.plans')
                                <a href="{{ route('admin.installment.plans.show', $installmentPlan) }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-external-link-alt"></i> مدیریت طرح
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="installment-order-banner-body">
                        <div class="row" style="row-gap:10px;">
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat">
                                    <div class="installment-order-stat-label">مبلغ کل سفارش</div>
                                    <div class="installment-order-stat-value">{{ number_format($installmentPlan->total_amount) }} ت</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat @if($installmentPlan->isDownPaymentPaid()) installment-order-stat-success @else installment-order-stat-warning @endif">
                                    <div class="installment-order-stat-label">
                                        پیش‌پرداخت
                                        @if($installmentPlan->isDownPaymentPaid())
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-clock text-warning"></i>
                                        @endif
                                    </div>
                                    <div class="installment-order-stat-value">{{ number_format($installmentPlan->down_payment) }} ت</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat">
                                    <div class="installment-order-stat-label">مبلغ هر قسط</div>
                                    <div class="installment-order-stat-value">{{ number_format($installmentPlan->installment_amount) }} ت</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat">
                                    <div class="installment-order-stat-label">پرداخت‌شده</div>
                                    <div class="installment-order-stat-value">{{ $installmentPlan->paid_installments }} / {{ $installmentPlan->total_installments }}</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat">
                                    <div class="installment-order-stat-label">باقی‌مانده</div>
                                    <div class="installment-order-stat-value" style="color:var(--warning);">{{ number_format($installmentPlan->remainingAmount()) }} ت</div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="installment-order-stat installment-order-stat-primary">
                                    <div class="installment-order-stat-label">مبلغ نهایی</div>
                                    <div class="installment-order-stat-value" style="color:var(--primary);">{{ number_format($installmentPlan->total_payable) }} ت</div>
                                </div>
                            </div>
                        </div>

                        {{-- نوار پیشرفت --}}
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small style="color:var(--gray-500);">پیشرفت طرح</small>
                                <small style="color:var(--gray-500);">{{ $installmentPlan->progressPercent() }}٪</small>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar bg-success" style="width:{{ $installmentPlan->progressPercent() }}%;"></div>
                            </div>
                        </div>

                        {{-- هشدارها --}}
                        @if(!$installmentPlan->isDownPaymentPaid())
                            <div class="oi-alert warning" style="margin-top:12px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>پیش‌پرداخت انجام نشده!</strong>
                                کاربر باید پیش‌پرداخت ({{ number_format($installmentPlan->down_payment) }} ت) را پرداخت کند تا طرح فعال شود.
                                @if($orderItem->order->status === 'cancelled')
                                    <br><span style="color:var(--danger);">سفارش لغو شده است - طرح نیز لغو خواهد شد.</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif


            {{-- ======== بنر وضعیت مرجوعی ======== --}}
            @include('back.returns.partials.order_item_return_info', ['orderItem' => $orderItem])


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
                                        @if($isInstallment)
                                            <span class="status-badge sb-info" style="background:#dbeafe;color:#1e40af;">
                                                💰 اقساطی
                                            </span>
                                        @elseif($orderItem->order->status == 'paid')
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
                            @if($isInstallment)
                                <span class="lbl">مبلغ پیش‌پرداخت</span>
                                <span class="stat-price">
                                    {{ number_format($installmentPlan->down_payment) }} تومان
                                    <br>
                                    @if($installmentPlan->isDownPaymentPaid())
                                        <small style="color:var(--success);font-size:11px;">✅ پیش‌پرداخت پرداخت شده</small>
                                    @else
                                        <small style="color:var(--warning);font-size:11px;">⏳ در انتظار پرداخت پیش‌پرداخت</small>
                                    @endif
                                    <br>
                                    <small style="color:var(--gray-400);font-size:11px;">
                                        از مجموع {{ number_format($installmentPlan->total_payable) }} ت (شامل {{ number_format($installmentPlan->total_interest) }} ت بهره)
                                    </small>
                                </span>
                            @else
                                <span class="lbl">مبلغ پرداخت شده</span>
                                <span class="stat-price">{{ number_format(array_sum($totalAmount)) }} تومان</span>
                            @endif
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


            @if(function_exists('module_is_active') && module_is_active('CreditPay'))
                    @include('credit-pay::back.orders.credit_info', ['order' => $orderItem->order])
            @endif

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
                            @php
                                $finalPayable = $totalAmount + ($orderItem->shipping_cost ?? 0);
                                // در حالت اقساطی، مبلغ قابل پرداخت = پیش‌پرداخت
                                $displayPayable = $isInstallment ? $installmentPlan->down_payment : $finalPayable;
                            @endphp
                            <tr class="tfoot-row-total">
                                <td colspan="6" class="text-left" style="font-size:15px;">
                                    @if($isInstallment)
                                        مبلغ پیش‌پرداخت (اقساطی):
                                    @else
                                        مبلغ قابل پرداخت نهایی:
                                    @endif
                                </td>
                                <td colspan="2" class="text-center tfoot-price">
                                    {{ number_format($displayPayable) }} تومان
                                    @if($isInstallment)
                                        <br>
                                        <small style="font-size:11px;color:var(--gray-400);">
                                            از مجموع {{ number_format($installmentPlan->total_payable) }} ت
                                        </small>
                                    @endif
                                </td>
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
                        @if($isInstallment)
                            {{-- حالت اقساطی: فقط پیش‌پرداخت برگشت داده می‌شه --}}
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <i class="fa fa-info-circle" style="margin-top:2px;"></i>
                                <span>
                                    این سفارش به‌صورت <strong>اقساطی</strong> ثبت شده است.
                                    @if($installmentPlan->isDownPaymentPaid())
                                        در صورت لغو، مبلغ <strong id="order-amount">{{ number_format($installmentPlan->down_payment) }}</strong> تومان (پیش‌پرداخت) به کیف پول کاربر برگشت داده می‌شود.
                                    @else
                                        سفارش هنوز پیش‌پرداخت نشده است. با لغو سفارش، طرح اقساطی نیز لغو خواهد شد.
                                    @endif
                                </span>
                            </div>
                            @if($installmentPlan->isDownPaymentPaid())
                                <div class="oi-alert info" style="margin-top:4px;">
                                    <i class="fas fa-money-check-alt"></i>
                                    <div style="font-size:12px;">
                                        <strong>نکته مهم:</strong> با لغو سفارش، طرح اقساطی لغو می‌شود و اقساط پرداخت‌نشده نیز باطل می‌شوند.
                                        @if($installmentPlan->paid_installments > 0)
                                            <br>تعداد {{ $installmentPlan->paid_installments }} قسط قبلاً پرداخت شده است که باید جداگانه بررسی شود.
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            {{-- حالت عادی --}}
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

    {{-- ======== استایل‌های بنر اقساطی ======== --}}
    <style>
        .installment-order-banner {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .installment-order-banner-pending_down_payment { border-color: #f59e0b; }
        .installment-order-banner-active { border-color: #0ea5e9; }
        .installment-order-banner-completed { border-color: #10b981; }
        .installment-order-banner-defaulted { border-color: #ef4444; }
        .installment-order-banner-cancelled { border-color: #9ca3af; opacity: 0.85; }

        .installment-order-banner-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .installment-order-banner-pending_down_payment .installment-order-banner-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .installment-order-banner-completed .installment-order-banner-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .installment-order-banner-defaulted .installment-order-banner-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .installment-order-banner-cancelled .installment-order-banner-header {
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        }

        .installment-order-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .installment-order-banner-body {
            padding: 16px 20px;
            background: #f8fafc;
        }

        .installment-order-stat {
            background: #fff;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        .installment-order-stat-success {
            background: #ecfdf5;
            border-color: #6ee7b7;
        }
        .installment-order-stat-warning {
            background: #fffbeb;
            border-color: #fcd34d;
        }
        .installment-order-stat-primary {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .installment-order-stat-label {
            color: #64748b;
            font-size: 11px;
            margin-bottom: 4px;
        }
        .installment-order-stat-value {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }

        .status-badge.sb-light {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }
        .status-badge.sb-info {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>

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
