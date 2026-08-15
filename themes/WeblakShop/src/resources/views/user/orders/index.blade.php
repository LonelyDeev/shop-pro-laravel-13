@extends('front::user.layouts.master')

@section('user-content')

    <style>
        /* ===== Page Styles ===== */
        .orders-page {
            padding: 0;
        }

        /* ===== Header ===== */
        .orders-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .orders-header h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .orders-header h4 i {
            color: var(--primary, #4f46e5);
        }

        /* ===== Filter Tabs ===== */
        .orders-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .orders-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            background: #f1f5f9;
            color: #64748b;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .orders-tab:hover {
            background: #e2e8f0;
            color: #475569;
        }
        .orders-tab.active {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        .orders-tab i {
            font-size: 0.9rem;
        }
        .orders-tab .tab-count {
            background: rgba(255, 255, 255, 0.25);
            padding: 1px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
        }
        .orders-tab:not(.active) .tab-count {
            background: #e2e8f0;
        }

        /* ===== Order Card ===== */
        .order-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #f1f5f9;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        /* ===== Card Header ===== */
        .order-card-header {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f8fafc;
            background: #fcfcfd;
        }
        .order-card-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .order-card-header-left a {
            color: #94a3b8;
            font-size: 1.1rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .order-card-header-left a:hover {
            color: #475569;
        }
        .order-card-header-left h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
        }
        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .order-status-badge.paid {
            background: #d1fae5;
            color: #065f46;
        }
        .order-status-badge.unpaid {
            background: #fee2e2;
            color: #991b1b;
        }
        .order-status-badge.cancelled {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ===== Card Body ===== */
        .order-card-body {
            padding: 16px 20px;
        }

        /* ===== Order Info Row ===== */
        .order-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            font-size: 0.82rem;
            color: #64748b;
        }
        .order-info-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .order-info-item i {
            color: #94a3b8;
            font-size: 0.78rem;
        }
        .order-info-item strong {
            color: #1e293b;
            font-weight: 600;
        }
        .order-info-divider {
            width: 4px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 50%;
        }

        /* ===== Product Thumbnails ===== */
        .order-products {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .order-product-thumb {
            position: relative;
            flex-shrink: 0;
            width: 72px;
            height: 72px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #f1f5f9;
        }
        .order-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .order-product-thumb-more {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* ===== Card Footer ===== */
        .order-card-footer {
            padding: 12px 20px;
            border-top: 1px solid #f8fafc;
            background: #fcfcfd;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .order-total {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }
        .order-total .label {
            color: #64748b;
            font-size: 0.78rem;
        }
        .order-total .value {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.95rem;
        }
        .order-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .order-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s;
            border: none;
            cursor: pointer;
        }
        .order-action-btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
        }
        .order-action-btn-primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .order-action-btn-outline {
            background: transparent;
            color: #6366f1;
            border: 1.5px solid #c7d2fe;
        }
        .order-action-btn-outline:hover {
            background: #eef2ff;
            color: #4338ca;
        }

        /* ===== Payment Type Badge ===== */
        .payment-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .payment-type-badge.installment {
            background: #dbeafe;
            color: #1e40af;
        }
        .payment-type-badge.credit {
            background: #ede9fe;
            color: #5b21b6;
        }
        .payment-type-badge.wallet {
            background: #fef3c7;
            color: #92400e;
        }
        .payment-type-badge.gateway {
            background: #f0fdf4;
            color: #065f46;
        }

        /* ===== Empty State ===== */
        .orders-empty {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .orders-empty i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 12px;
        }
        .orders-empty h6 {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .orders-empty p {
            font-size: 0.85rem;
            margin-bottom: 16px;
        }

        /* ===== Return Badge in Card ===== */
        .return-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #fff7ed;
            color: #c2410c;
        }
    </style>

    <div class="orders-page">

        {{-- ===== Header ===== --}}
        <div class="orders-header">
            <h4>
                <i class="fas fa-shopping-bag"></i>
                سفارش‌های من
            </h4>
        </div>

        {{-- ===== Filter Tabs ===== --}}
        <div class="orders-tabs">
            <a href="?status=all" class="orders-tab {{ (request('status', 'all') === 'all') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                همه
                @php $allCount = auth()->user()->orders()->count(); @endphp
                @if($allCount > 0)
                    <span class="tab-count">{{ $allCount }}</span>
                @endif
            </a>
            <a href="?status=paid" class="orders-tab {{ (request('status') === 'paid') ? 'active' : '' }}">
                <i class="fas fa-check-circle"></i>
                تکمیل شده
                @php $paidCount = auth()->user()->orders()->where('status', 'paid')->count(); @endphp
                @if($paidCount > 0)
                    <span class="tab-count">{{ $paidCount }}</span>
                @endif
            </a>
            <a href="?status=unpaid" class="orders-tab {{ (request('status') === 'unpaid') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                در انتظار پرداخت
                @php $unpaidCount = auth()->user()->orders()->where('status', 'unpaid')->count(); @endphp
                @if($unpaidCount > 0)
                    <span class="tab-count">{{ $unpaidCount }}</span>
                @endif
            </a>
            <a href="?status=cancelled" class="orders-tab {{ (request('status') === 'cancelled') ? 'active' : '' }}">
                <i class="fas fa-times-circle"></i>
                لغو شده
                @php $cancelledCount = auth()->user()->orders()->whereIn('status', ['cancelled', 'failed'])->count(); @endphp
                @if($cancelledCount > 0)
                    <span class="tab-count">{{ $cancelledCount }}</span>
                @endif
            </a>
            @php
                $returnedCount = 0;
                if (class_exists(\App\Models\ReturnRequest::class)) {
                    $returnedCount = \App\Models\ReturnRequest::where('user_id', auth()->id())
                        ->whereNotIn('status', ['cancelled'])
                        ->distinct('order_id')
                        ->count('order_id');
                }
            @endphp
            @if(class_exists(\App\Models\ReturnRequest::class))
                <a href="?status=returned" class="orders-tab {{ (request('status') === 'returned') ? 'active' : '' }}">
                    <i class="fas fa-undo-alt"></i>
                    مرجوع شده
                    @if($returnedCount > 0)
                        <span class="tab-count">{{ $returnedCount }}</span>
                    @endif
                </a>
            @endif
        </div>

        @php
            $query = auth()->user()->orders()->latest();
            if (request('status') === 'paid') {
                $query->where('status', 'paid');
            } elseif (request('status') === 'unpaid') {
                $query->where('status', 'unpaid');
            } elseif (request('status') === 'cancelled') {
                $query->whereIn('status', ['cancelled', 'failed']);
            } elseif (request('status') === 'returned' && class_exists(\App\Models\ReturnRequest::class)) {
                $returnedOrderIds = \App\Models\ReturnRequest::where('user_id', auth()->id())
                    ->whereNotIn('status', ['cancelled'])
                    ->pluck('order_id')
                    ->unique()
                    ->toArray();
                $query->whereIn('id', $returnedOrderIds);
            }
            $orders = $query->paginate(10);
        @endphp

        {{-- ===== Order Cards ===== --}}
        @if($orders->count())
            @foreach ($orders as $order)

                @php
                    // بررسی نوع پرداخت
                    $isInstallment = false;
                    $isCredit = false;
                    if (function_exists('module_is_active') && module_is_active('InstallmentPayment')) {
                        $isInstallment = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $order->id)->exists();
                    }
                    if (function_exists('module_is_active') && module_is_active('CreditPay')) {
                        $isCredit = \Modules\CreditPay\Models\CreditOrder::where('order_id', $order->id)->exists();
                    }

                    // بررسی مرجوعی
                    $hasReturn = false;
                    if (class_exists(\App\Models\ReturnRequest::class)) {
                        $hasReturn = \App\Models\ReturnRequest::where('order_id', $order->id)
                            ->whereNotIn('status', ['cancelled'])
                            ->exists();
                    }

                    // محصولات سفارش
                    $orderProducts = $order->items->take(4);
                    $totalProducts = $order->items->count();
                    $remainingProducts = max(0, $totalProducts - 4);
                @endphp

                <div class="order-card">
                    {{-- Header --}}
                    <div class="order-card-header">
                        <div class="order-card-header-left">
                            <a href="{{ route('front.orders.show', ['order' => $order]) }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <h6>سفارش #{{ $order->id }}</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($hasReturn)
                                <span class="return-indicator">
                            <i class="fas fa-undo-alt"></i> مرجوعی
                        </span>
                            @endif
                            @if($order->status == 'paid')
                                <span class="order-status-badge paid">
                                <i class="fas fa-check-circle"></i> تحویل شده
                            </span>
                            @elseif($order->status == 'unpaid')
                                <span class="order-status-badge unpaid">
                                <i class="fas fa-clock"></i> پرداخت نشده
                            </span>
                            @else
                                <span class="order-status-badge cancelled">
                                <i class="fas fa-times-circle"></i> لغو شده
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="order-card-body">
                        {{-- Info Row --}}
                        <div class="order-info-row">
                            <div class="order-info-item">
                                <i class="far fa-calendar"></i>
                                <span>{{ jdate($order->created_at)->format('%d %B %Y') }}</span>
                            </div>
                            <div class="order-info-divider"></div>
                            <div class="order-info-item">
                                <i class="fas fa-box"></i>
                                <span>{{ $totalProducts }} کالا</span>
                            </div>
                            <div class="order-info-divider"></div>
                            @if($isInstallment)
                                <span class="payment-type-badge installment">
                            <i class="fas fa-money-check-alt"></i> اقساطی
                        </span>
                            @elseif($isCredit)
                                <span class="payment-type-badge credit">
                            <i class="fas fa-credit-card"></i> اعتباری
                        </span>
                            @elseif ($order->walletHistory)
                                <span class="payment-type-badge wallet">
                            <i class="fas fa-wallet"></i> کیف پول
                        </span>
                            @else
                                <span class="payment-type-badge gateway">
                            <i class="fas fa-credit-card"></i> اینترنتی
                        </span>
                            @endif
                        </div>

                        {{-- Product Thumbnails --}}
                        <div class="order-products">
                            @foreach($orderProducts as $item)
                                <div class="order-product-thumb">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset($item->product->image) }}" alt="{{ $item->title }}">
                                    @else
                                        <div class="order-product-thumb-more">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            @if($remainingProducts > 0)
                                <div class="order-product-thumb order-product-thumb-more">
                                    +{{ $remainingProducts }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="order-card-footer">
                        <div class="order-total">
                            <span class="label">مبلغ قابل پرداخت:</span>
                            <span class="value">{{ number_format($order->price) }} تومان</span>
                            @if($order->totalDiscount() != 0)
                                <span style="color:#10b981;font-size:0.75rem;">
                            (تخفیف: {{ number_format($order->totalDiscount()) }} ت)
                        </span>
                            @endif
                        </div>
                        <div class="order-actions">
                            @if($order->status == 'unpaid')
                                <a href="{{ route('front.orders.pay', ['order' => $order]) }}"
                                   class="order-action-btn order-action-btn-primary">
                                    <i class="fas fa-credit-card"></i> پرداخت
                                </a>
                            @endif
                            <a href="{{ route('front.orders.show', ['order' => $order]) }}"
                               class="order-action-btn order-action-btn-outline">
                                <i class="fas fa-eye"></i> جزئیات
                            </a>
                        </div>
                    </div>
                </div>

            @endforeach

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links("pagination::bootstrap-4") }}
            </div>

        @else
            {{-- Empty State --}}
            <div class="orders-empty">
                <i class="fas fa-shopping-bag"></i>
                <h6>هیچ سفارشی یافت نشد</h6>
                <p>شما هنوز سفارشی ثبت نکرده‌اید.</p>
                <a href="{{ route('front.products.index') }}" class="order-action-btn order-action-btn-primary">
                    <i class="fas fa-store"></i> شروع خرید
                </a>
            </div>
        @endif

    </div>

@endsection
