@extends('front::user.layouts.master')

@section('user-content')

    @php
        // گروه‌بندی آیتم‌های سفارش بر اساس فروشنده
        $sellerGroups = [];
        $totalPrice = 0;
        $totalShippingCost = 0;
        $totalDiscount = 0;

        foreach ($order->items as $item) {
            $sellerId = $item->seller_id;
            $groupId = $sellerId ? 'seller_' . $sellerId : 'store';

            if (!isset($sellerGroups[$groupId])) {
                $seller = $sellerId ? \App\Models\Seller::find($sellerId) : null;
                $sellerGroups[$groupId] = [
                    'seller_id' => $sellerId,
                    'seller_name' => $seller ? ($seller->seller_info->business_name ?? $seller->name) : 'فروشگاه',
                    'seller_logo' => $seller && $seller->seller_info ? asset($seller->seller_info->logo) : null,
                    'items' => [],
                    'subtotal' => 0,
                    'shipping_cost' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'shipping_status' => $item->shipping_status,
                    'tracking_code' => $item->tracking_code ?? null,
                    'carrier_name' => $item->carrier ? $item->carrier->title : null,
                    'delivery_date' => $item->delivery_date ?? null,
                    'cancel_reason' => $item->cancel_reason ?? null,
                    'refunded' => $item->refunded ?? false
                ];
            }

            $itemTotalPrice = $item->real_price * $item->quantity;
            $itemDiscount = ($item->real_price - $item->price) * $item->quantity;

            $sellerGroups[$groupId]['items'][] = $item;
            $sellerGroups[$groupId]['subtotal'] += $itemTotalPrice;
            $sellerGroups[$groupId]['discount'] += $itemDiscount;
            $sellerGroups[$groupId]['total'] += $itemTotalPrice;
            $sellerGroups[$groupId]['shipping_cost'] = $item->shipping_cost ?? 0;

            $totalPrice += $itemTotalPrice;
            $totalDiscount += $itemDiscount;
            $totalShippingCost += $item->shipping_cost ?? 0;
        }

        $finalPayable = $totalPrice - $totalDiscount + $totalShippingCost;
        $hasPhysicalProduct = $order->hasPhysicalProduct();
        $NoDownload = [];
        foreach ($order->items as $item) {
            if ($item->product && $item->product->isDownload() && $item->get_price && $item->get_price->isDownloadable()) {
                // قابل دانلود است
            } else {
                $NoDownload[] = $item->id;
            }
        }
    @endphp

    <section class="page-contents page-contents-order">
        <div class="container-fluid px-0">

            {{-- پیام‌ها --}}
            @if(session('message') == 'ok')
                <div class="alert alert-success rounded-3 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle fs-4 me-2"></i>
                        <div>
                            سفارش شما با شماره سفارش <strong>{{ $order->id }}</strong> با موفقیت در سیستم ثبت شد.
                        </div>
                    </div>
                </div>
            @elseif(session('transaction-error'))
                <div class="alert alert-warning rounded-3 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="mdi mdi-alert-circle fs-4 me-2"></i>
                        <div>
                            <strong>سفارش با شماره {{ $order->id }} ثبت شد اما پرداخت ناموفق بود.</strong>
                            <p class="mb-0 mt-1 small">برای جلوگیری از لغو سیستمی سفارش، تا ۱ ساعت پس از ثبت سفارش پرداخت را انجام دهید.</p>
                            <p class="mb-0 small text-muted">چنانچه طی این فرایند مبلغی از حساب شما کسر شده است، طی ۷۲ ساعت آینده به حساب شما باز خواهد گشت.</p>
                            @if($order->status == 'unpaid')
                                <a href="{{ route('front.orders.pay', ['order' => $order]) . '?gateway=' . ($order->gatewayRelation ? $order->gatewayRelation->key : 'wallet') }}"
                                   class="btn btn-primary btn-sm mt-2">
                                    پرداخت سفارش
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger rounded-3 mb-4">
                    <i class="mdi mdi-close-circle me-2"></i>
                    <strong>{{ session('error') }}</strong>
                </div>
            @endif

            {{-- کارت اصلی سفارش --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('front.orders.index') }}" class="text-dark me-3">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <h5 class="mb-0 fw-bold">جزئیات سفارش #{{ $order->id }}</h5>
                    </div>
                    <a href="{{ route('front.orders.print', $order->id) }}" class="text-secondary" target="_blank">
                        <i class="fas fa-print me-1"></i> مشاهده فاکتور
                    </a>
                </div>

                <div class="card-body">
                    {{-- اطلاعات کلی سفارش --}}
                    <div class="row g-3 pb-3 mb-3 border-bottom">
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">کد پیگیری سفارش</div>
                            <div class="fw-bold">{{ $order->id }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">تاریخ ثبت سفارش</div>
                            <div class="fw-bold">{{ jdate($order->created_at)->format('%d %B %Y') }}</div>
                        </div>
                        @if(count($NoDownload))
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">تحویل گیرنده</div>
                                <div class="fw-bold">{{ $order->name }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">شماره موبایل</div>
                                <div class="fw-bold">{{ $order->mobile }}</div>
                            </div>
                        @endif
                    </div>

                    @if(count($NoDownload))
                        <div class="mb-3 text-right">
                            <div class="text-muted small">آدرس تحویل</div>
                            <div class="">{{ $order->address }}</div>
                        </div>
                    @endif

                    {{-- جمع‌بندی مالی --}}
                    <div class="bg-light rounded-3 p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">مبلغ کل کالاها</div>
                                <div class="fw-bold">{{ number_format($totalPrice) }} تومان</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">تخفیف</div>
                                @if($totalDiscount)
                                    <div class="fw-bold text-success">{{ number_format($totalDiscount) }} تومان</div>
                                @else
                                    -
                                @endif

                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">هزینه ارسال</div>
                                <div class="fw-bold">
                                    @if($totalShippingCost > 0)
                                        {{ number_format($totalShippingCost) }} تومان
                                    @elseif($order->carrier && $order->carrier->carrige_forward)
                                        پس کرایه
                                    @else
                                        رایگان
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted small">نوع پرداخت</div>
                                <div class="fw-bold">
                                    @if ($order->walletHistory)
                                        کیف پول
                                    @else
                                        پرداخت اینترنتی
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 pt-2 border-top">
                            <div class="col-md-6 col-6">
                                <div class="text-muted small">وضعیت پرداخت</div>
                                <div>
                                    @if($order->status == 'paid')
                                        <span class="badge bg-success">پرداخت شده</span>
                                    @elseif($order->status == 'unpaid')
                                        <span class="badge bg-danger">پرداخت نشده</span>
                                    @else
                                        <span class="badge bg-secondary">لغو شده</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="text-muted small">مبلغ قابل پرداخت</div>
                                <div class="fw-bold fs-5 text-primary">{{ number_format($finalPayable) }} تومان</div>
                            </div>
                        </div>

                        @if($order->status == 'unpaid')
                            <div class="mt-3">
                                <form action="{{ route('front.orders.pay', ['order' => $order]) }}" method="GET" class="row g-2 align-items-end">
                                    <div class="col-md-4 col-7">
                                        <select class="form-select form-select-sm" name="gateway" required>
                                            <option value="">انتخاب درگاه پرداخت</option>
                                            @if ($wallet->balance >= $order->price)
                                                <option value="wallet">پرداخت با کیف پول</option>
                                            @elseif ($wallet->balance)
                                                <option value="wallet">شارژ و پرداخت با کیف پول</option>
                                            @endif
                                            @foreach ($gateways as $gateway)
                                                <option value="{{ $gateway->key }}">{{ $gateway->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-5">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">پرداخت سفارش</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- ========== مرسوله‌ها ========== --}}
                    @foreach($sellerGroups as $groupId => $group)

                        @php
                            $statusSteps = [
                                'w-pending' => ['label' => 'در انتظار بررسی', 'step' => 1, 'color' => '#ffc107', 'icon' => 'fa-clock'],
                                'pending' => ['label' => 'در حال بررسی', 'step' => 2, 'color' => '#fd7e14', 'icon' => 'fa-magnifying-glass'],
                                'processing' => ['label' => 'در حال پردازش', 'step' => 3, 'color' => '#0d6efd', 'icon' => 'fa-gear'],
                                'waiting' => ['label' => 'منتظر ارسال', 'step' => 4, 'color' => '#0dcaf0', 'icon' => 'fa-hourglass-half'],
                                'sent' => ['label' => 'ارسال شد', 'step' => 5, 'color' => '#6f42c1', 'icon' => 'fa-truck-fast'],
                                'post-sent' => ['label' => 'تحویل به پست', 'step' => 6, 'color' => '#20c997', 'icon' => 'fa-box'],
                                'delivered' => ['label' => 'تحویل داده شد', 'step' => 7, 'color' => '#198754', 'icon' => 'fa-check-circle'],
                                'canceled' => ['label' => 'لغو شد', 'step' => 0, 'color' => '#dc3545', 'icon' => 'fa-ban']
                            ];

                            $currentStatus = $group['shipping_status'];
                            $currentStep = $statusSteps[$currentStatus]['step'] ?? 1;
                            $maxStep = 7;
                            $progressPercent = ($currentStep / $maxStep) * 100;
                            $progressColor = $statusSteps[$currentStatus]['color'] ?? '#ffc107';
                            $isCanceled = ($currentStatus == 'canceled');
                        @endphp


                        <div class="card border rounded-3 mb-4 overflow-hidden">
                            {{-- هدر مرسوله --}}
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    @if($group['seller_logo'])
                                        <img src="{{ $group['seller_logo'] }}" width="32" height="32" class="rounded-circle object-fit-cover">
                                    @else
                                        <i class="fas fa-store fs-4 text-secondary"></i>
                                    @endif
                                    <div>
                                        <span class="fw-bold">{{ $group['seller_name'] }}</span>
                                        <span class="badge bg-secondary ms-2">مرسوله {{ $loop->iteration }}</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge" style="background-color: {{ $progressColor }};">{{ $statusSteps[$currentStatus]['label'] }}</span>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                {{-- اطلاعات ارسال --}}
                                <div class="row g-3 small mb-3 pb-2 border-bottom">
                                    <div class="col-md-3 col-6">
                                        <div class="text-muted">هزینه ارسال</div>
                                        <div class="fw-bold">
                                            @if($group['shipping_cost'] > 0)
                                                {{ number_format($group['shipping_cost']) }} تومان
                                            @else
                                                رایگان
                                            @endif
                                        </div>
                                    </div>
                                    @if($group['carrier_name'])
                                        <div class="col-md-3 col-6">
                                            <div class="text-muted">روش ارسال</div>
                                            <div>{{ $group['carrier_name'] }}</div>
                                        </div>
                                    @endif
                                    @if($group['delivery_date'])
                                        @php
                                            $deliveryDate = null;
                                            if($group['delivery_date']) {
                                                try {
                                                    // اگر تاریخ میلادی است
                                                    $deliveryDate = jdate($group['delivery_date'])->format('%d %B %Y');
                                                } catch(Exception $e) {
                                                    // اگر تاریخ شمسی است یا فرمت نامعتبر
                                                    $deliveryDate = $group['delivery_date'];
                                                }
                                            }
                                        @endphp
                                        <div class="col-md-3 col-6">
                                            <div class="text-muted">تاریخ تحویل</div>
                                            <div>{{ $deliveryDate }}</div>
                                        </div>
                                    @endif
                                    @if($group['tracking_code'])
                                        <div class="col-md-3 col-6">
                                            <div class="text-muted">کد رهگیری</div>
                                            <div class="fw-bold">{{ $group['tracking_code'] }}</div>
                                        </div>
                                    @endif
                                </div>

                                @if($group['cancel_reason'])
                                    <div class="alert alert-warning py-2 small mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>دلیل لغو:</strong> {{ $group['cancel_reason'] }}
                                    </div>
                                @endif

                                @if($group['refunded'])
                                    <div class="alert alert-info py-2 small mb-3">
                                        <i class="fas fa-check-circle me-1"></i>
                                        وجه این سفارش به کیف پول شما برگشت داده شده است.
                                    </div>
                                @endif

                                {{-- جمع مرسوله --}}
                                <div class="row g-3 small mb-3 pb-2 border-bottom">
                                    <div class="col-4">
                                        <div class="text-muted">جمع قیمت کالاها</div>
                                        <div>{{ number_format($group['subtotal']) }} تومان</div>
                                    </div>
                                    @if($group['discount'] > 0)
                                        <div class="col-4">
                                            <div class="text-muted">تخفیف</div>
                                            <div class="text-success">-{{ number_format($group['discount']) }} تومان</div>
                                        </div>
                                    @endif
                                    <div class="col-4">
                                        <div class="text-muted">جمع نهایی مرسوله</div>
                                        <div class="fw-bold text-primary">{{ number_format($group['total'] + $group['shipping_cost']-$group['discount']) }} تومان</div>
                                    </div>
                                </div>

                                {{-- لیست محصولات --}}
                                @foreach($group['items'] as $item)
                                    <div class="d-flex gap-3 py-2 border-bottom align-items-start">
                                        <div class="flex-shrink-0">
                                            @if($item->product)
                                                <a href="{{route('front.products.show',$item->product)}}">
                                                    <img src="{{ $item->product->image ? asset($item->product->image) : '/empty.svg' }}"
                                                         class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;">
                                                </a>

                                            @else
                                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 text-md-right"><a class=" text-black" href="{{route('front.products.show',$item->product)}}">{{ $item->title }}</a></h6>
                                            <div class="d-flex flex-column mt-3">
                                            @if($item->attributes)
                                                @php
                                                    $attributes = json_decode($item->attributes, true);

                                                    // استخراج رنگ از آرایه
                                                    $colorAttributes = [];
                                                    $otherAttributes = [];

                                                    foreach ($attributes as $groupName => $groupAttributes) {
                                                        if ($groupName == 'رنگ') {
                                                            $colorAttributes = [$groupName => $groupAttributes];
                                                        } else {
                                                            $otherAttributes[$groupName] = $groupAttributes;
                                                        }
                                                    }

                                                    // ترکیب نهایی: رنگ اول، سپس بقیه
                                                    $sortedAttributes = array_merge($colorAttributes, $otherAttributes);
                                                @endphp

                                            <div>
                                                @foreach($sortedAttributes as $groupName => $groupAttributes)
                                                    @foreach($groupAttributes as $attribute)
                                                        @if($groupName == 'رنگ')
                                                            <span class="order-product-attribute d-flex align-items-center gap-1">
                    <span class="order-product-color d-print-none"
                          style="display: inline-block; width: 16px; height: 16px; border-radius: 25%; background-color: {{ $attribute['value'] }};"></span>
                    <span>{{ $groupName }}: {{ $attribute['name'] }}</span>
                </span>
                                                        @elseif($groupName == 'گارانتی')
                                                            <span class="order-product-attribute">
                    <i class="fas fa-shield-alt me-1"></i>
                    {{ $groupName }}: {{ $attribute['name'] }}
                </span>
                                                        @elseif($groupName == 'سایز')
                                                            <span class="order-product-attribute">
                    <i class="fas fa-ruler me-1"></i>
                    {{ $groupName }}: {{ $attribute['name'] }}
                </span>
                                                        @else
                                                            <span class="order-product-attribute">
                    {{ $groupName }}: {{ $attribute['name'] }}
                </span>
                                                        @endif
                                                    @endforeach
                                                @endforeach

                                            </div>
                                            @endif
                                            <div class="d-flex flex-wrap gap-3 small">
                                                <div>
                                                    <span class="text-muted">تعداد:</span>
                                                    <span class="fw-medium">{{ $item->quantity }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">قیمت واحد:</span>
                                                    <span class="fw-medium">{{ number_format($item->real_price) }} تومان</span>
                                                </div>
                                                @if($item->discount > 0)
                                                    <div>
                                                        <span class="text-muted">تخفیف:</span>
                                                        <span class="fw-medium text-success">{{ $item->discount }}%</span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="text-muted">قیمت نهایی:</span>
                                                    <span class="fw-bold">{{ number_format($item->price * $item->quantity) }} تومان</span>
                                                </div>
                                            </div>

                                            @if ($item->product && $item->product->isDownload() && $item->get_price && $item->get_price->isDownloadable())
                                                <div class="mt-2">
                                                    <a href="{{ $item->get_price->downloadLink() }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download me-1"></i> دانلود محصول
                                                    </a>
                                                </div>
                                            @endif

                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            <div class="container-fluid">
                                <div class="order-status-container mb-3">

                                @if($isCanceled)
                                    {{-- وضعیت لغو شده --}}
                                    <div class="card bg-danger bg-opacity-10 border-0 p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-ban text-danger fs-5"></i>
                                            <span class="text-white fw-bold">سفارش لغو شده است</span>
                                           {{-- <span class="text-muted small ms-auto">{{ $statusSteps[$currentStatus]['label'] }}</span>--}}
                                        </div>
                                        @if($group['cancel_reason'])
                                            <div class="mt-1 small text-muted">
                                                <i class="fas fa-info-circle me-1"></i> دلیل: {{ $group['cancel_reason'] }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- نمایش وضعیت عادی --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                        <span class="small text-muted">وضعیت سفارش</span>
                                      {{--  <span class="badge" style="background-color: {{ $progressColor }};">{{ $statusSteps[$currentStatus]['label'] }}</span>--}}
                                    </div>

                                    {{-- نوار پیشرفت --}}
                                    <div class="progress mb-3" style="height: 8px; background-color: #e2e8f0; border-radius: 10px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $progressPercent }}%; background-color: {{ $progressColor }}; border-radius: 10px; transition: width 0.5s ease;">
                                        </div>
                                    </div>

                                    {{-- مراحل با نام کامل --}}
                                    <div class="row g-0 text-center">
                                        @foreach(['w-pending', 'pending', 'processing', 'waiting', 'sent', 'post-sent', 'delivered'] as $index => $stepKey)
                                            @php
                                                $stepNumber = $statusSteps[$stepKey]['step'];
                                                $isCompleted = $stepNumber < $currentStep;
                                                $isActive = $stepNumber == $currentStep;
                                                $stepLabel = $statusSteps[$stepKey]['label'];
                                                $stepIcon = $statusSteps[$stepKey]['icon'];
                                            @endphp
                                            <div class="col" style="flex: 1;">
                                                <div class="position-relative">
                                                    {{-- دایره وضعیت --}}
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1"
                                                         style="width: 24px; height: 24px;
                                    background-color: {{ $isCompleted ? $progressColor : ($isActive ? $progressColor : '#f1f5f9') }};
                                    border: 2px solid {{ $isCompleted || $isActive ? $progressColor : '#cbd5e1' }};">
                                                        @if($isCompleted)
                                                            <i class="fas fa-check text-white" style="font-size: 10px;"></i>
                                                        @elseif($isActive)
                                                            <i class="fas {{ $stepIcon }} text-white" style="font-size: 10px;"></i>
                                                        @else
                                                            <i class="fas {{ $stepIcon }} text-muted" style="font-size: 10px;"></i>
                                                        @endif
                                                    </div>

                                                    {{-- متن مرحله --}}
                                                    <div class="small {{ $isActive ? 'fw-bold' : 'text-muted' }} d-none d-sm-block"
                                                         style="color: {{ $isActive ? $progressColor : 'inherit' }};">
                                                        {{ $stepLabel }}
                                                    </div>

                                                    {{-- متن کوتاه برای موبایل --}}
                                                    <div class="small {{ $isActive ? 'fw-bold' : 'text-muted' }} d-sm-none"
                                                         style="color: {{ $isActive ? $progressColor : 'inherit' }};">
                                                        @switch($stepKey)
                                                            @case('w-pending') انتظار @break
                                                            @case('pending') بررسی @break
                                                            @case('processing') پردازش @break
                                                            @case('waiting') آماده @break
                                                            @case('sent') ارسال @break
                                                            @case('post-sent') پست @break
                                                            @case('delivered') تحویل @break
                                                        @endswitch
                                                    </div>

                                                    {{-- نشانگر مرحله فعال --}}
                                                    @if($isActive)
                                                        <div class="small text-success mt-1">
                                                            <i class="fas fa-spinner fa-pulse"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- درصد پیشرفت --}}
                                    <div class="text-center mt-2">
                                        <small class="text-muted">پیشرفت سفارش: {{ round($progressPercent) }}%</small>
                                    </div>
                                @endif
                            </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- تاریخچه تراکنش‌ها --}}
                    @if ($order->transactions->count())
                        <div class="card border rounded-3 overflow-hidden">
                            <div class="card-header bg-light py-2" data-bs-toggle="collapse" data-bs-target="#transactions" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chevron-down small"></i>
                                    <span class="fw-bold">تاریخچه تراکنش‌ها</span>
                                    <span class="badge bg-secondary">{{ $order->transactions->count() }}</span>
                                </div>
                            </div>
                            <div id="transactions" class="collapse">
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach($order->transactions()->latest()->get() as $transaction)
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($transaction->status)
                                                            <i class="fas fa-check-circle text-success fs-5"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-danger fs-5"></i>
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold">
                                                                @if($transaction->status)
                                                                    پرداخت موفق
                                                                @else
                                                                    پرداخت ناموفق
                                                                @endif
                                                            </div>
                                                            <div class="small text-muted">{{ jdate($transaction->created_at)->format('%d %B %Y H:i:s') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-3 small">
                                                        <div>
                                                            <span class="text-muted">درگاه:</span>
                                                            <span>{{ \App\Models\Gateway::find($transaction->gateway_id)->name ?? '-' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">شماره پیگیری:</span>
                                                            <span>{{ $transaction->id }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">مبلغ:</span>
                                                            <span class="fw-bold">{{ number_format($transaction->amount) }} تومان</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <style>
        .badge{
            color: #fff;
        }
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 1rem; }
        .object-fit-cover {
            object-fit: cover;
        }
        .card-header {
            cursor: pointer;
        }
        .card-header i.fa-chevron-down {
            transition: transform 0.2s ease;
        }
        .card-header[aria-expanded="true"] i.fa-chevron-down {
            transform: rotate(180deg);
        }
        .order-status-container .progress-bar {
            position: relative;
            overflow: hidden;
        }

        .order-status-container .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .order-status-container .fa-spinner {
            animation: spin 1s linear infinite;
        }
        .order-product-attribute{
            padding: 2px 10px;
            background: #f5f6f7;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 7px;
            float: right;
            margin-left: 5px;
            font-size: 12px;
        }
        .width-max-content{
            width: max-content;
        }
    </style>

@endsection
