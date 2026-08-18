@extends('back.layouts.master')

@section('title', 'جزئیات مرجوعی #' . $returnRequest->id)

@section('content')
@php
    $statusInfo = \App\Models\ReturnRequest::statusLabels()[$returnRequest->status] ?? ['label' => $returnRequest->status, 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-circle'];
    $ptInfo = \App\Models\ReturnRequest::paymentTypeLabels()[$returnRequest->payment_type] ?? ['label' => $returnRequest->payment_type, 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-circle'];
    $item = $returnRequest->orderItem;
@endphp

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <a href="{{ route('admin.returns.index') }}" class="btn btn-link p-0 mb-1 text-muted">
                        <i class="fas fa-arrow-right"></i> بازگشت
                    </a>
                    <h4 class="mb-0">جزئیات مرجوعی #{{ $returnRequest->id }}</h4>
                </div>
                <div class="d-flex gap-2">
                    <span style="background:{{ $ptInfo['bg'] }};color:{{ $ptInfo['color'] }};padding:6px 16px;border-radius:999px;font-weight:700;font-size:0.82rem;">
                        <i class="fas {{ $ptInfo['icon'] }}"></i> {{ $ptInfo['label'] }}
                    </span>
                    <span style="background:{{ $statusInfo['bg'] }};color:{{ $statusInfo['color'] }};padding:6px 16px;border-radius:999px;font-weight:700;">
                        <i class="fas {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            <div class="row">
                {{-- ستون چپ: اطلاعات --}}
                <div class="col-md-8">
                    {{-- اطلاعات محصول --}}
                    <div class="card mb-3">
                        <div class="card-header"><strong>محصول مرجوعی</strong></div>
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center">
                                @if($item?->product?->image)
                                <img src="{{ asset($item->product->image) }}" style="width:70px;height:70px;border-radius:10px;object-fit:cover;">
                                @endif
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $item?->title ?? '—' }}</h6>
                                    <small class="text-muted">
                                        سفارش: <a href="{{ route('admin.orders.show', $returnRequest->order_id) }}" target="_blank">#{{ $returnRequest->order_id }}</a>
                                        · تعداد: {{ $item?->quantity ?? 0 }}
                                        · مبلغ محصول: {{ number_format(($item?->price ?? 0) * ($item?->quantity ?? 0)) }} ت
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- اطلاعات مالی مرجوعی --}}
                    <div class="card mb-3" style="border:2px solid #a7f3d0;">
                        <div class="card-header" style="background:#d1fae5;color:#065f46;">
                            <strong><i class="fas fa-wallet"></i> اطلاعات مالی مرجوعی</strong>
                        </div>
                        <div class="card-body" style="background:#f0fdf4;">
                            <div class="row g-2" style="font-size:0.88rem;">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">مبلغ محصول (با تخفیف)</small>
                                    <strong>{{ number_format($returnRequest->total_item_amount) }} ت</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">نوع پرداخت سفارش</small>
                                    <strong style="color:{{ $ptInfo['color'] }};">
                                        <i class="fas {{ $ptInfo['icon'] }}"></i> {{ $ptInfo['label'] }}
                                    </strong>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted d-block">بازگشت به کیف پول</small>
                                    <strong class="text-success">
                                        @if($returnRequest->wallet_refund_amount > 0)
                                            {{ number_format($returnRequest->wallet_refund_amount) }} ت
                                            @if($returnRequest->paid_to_wallet)
                                                <span class="badge bg-success ms-1">واریز شد</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted d-block">بازگشت به اعتبار</small>
                                    <strong class="text-primary">
                                        @if($returnRequest->credit_restore_amount > 0)
                                            {{ number_format($returnRequest->credit_restore_amount) }} ت
                                            @if($returnRequest->credit_restored)
                                                <span class="badge bg-primary ms-1">برگشت داده شد</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted d-block">هزینه ارسال</small>
                                    <strong class="text-muted">قابل بازگشت نیست</strong>
                                </div>
                                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed #a7f3d0;">
                                    <small class="text-muted d-block">مبلغ کل قابل برگشت</small>
                                    <strong style="font-size:1.1rem;color:#10b981;">{{ number_format($returnRequest->refund_amount) }} ت</strong>
                                </div>
                            </div>

                            @if($returnRequest->payment_type !== 'cash')
                            <div class="alert mt-3 mb-0" style="background:{{ $ptInfo['bg'] }};border:1px solid {{ $ptInfo['color'] }}33;color:{{ $ptInfo['color'] }};font-size:0.82rem;">
                                <i class="fas {{ $ptInfo['icon'] }}"></i>
                                <strong>نکته درباره سفارش {{ $ptInfo['label'] }}:</strong>
                                <ul class="mb-0 mt-1" style="padding-right:16px;">
                                    @if($returnRequest->payment_type === 'credit')
                                        <li>مبلغ پرداخت‌شده کاربر (قسط اول + اقساط پرداخت‌شده) × سهم این آیتم به کیف پول کاربر واریز شد.</li>
                                        <li>اعتبار استفاده‌شده برای این آیتم به حساب اعتباری کاربر برگردانده شد.</li>
                                    @elseif($returnRequest->payment_type === 'installment')
                                        <li>مبلغ پرداخت‌شده کاربر (پیش‌پرداخت + اقساط پرداخت‌شده) × سهم این آیتم به کیف پول کاربر واریز شد.</li>
                                    @endif
                                    <li>هزینه ارسال قابل بازگشت نیست.</li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- توضیحات کاربر --}}
                    @if($returnRequest->description)
                    <div class="card mb-3">
                        <div class="card-header"><strong>توضیحات کاربر</strong></div>
                        <div class="card-body">
                            <p class="mb-0">{{ $returnRequest->description }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- تصاویر --}}
                    @if($returnRequest->images->count())
                    <div class="card mb-3">
                        <div class="card-header"><strong>تصاویر ارسالی ({{ $returnRequest->images->count() }})</strong></div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach($returnRequest->images as $image)
                                <a href="{{ \Storage::url($image->path) }}" target="_blank">
                                    <img src="{{ \Storage::url($image->path) }}" style="width:100px;height:100px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0;">
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- نتیجه بررسی --}}
                    @if($returnRequest->inspection_result)
                    <div class="card mb-3">
                        <div class="card-header"><strong>نتیجه بررسی محصول</strong></div>
                        <div class="card-body">
                            <p class="mb-0">{{ $returnRequest->inspection_result }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- دلیل رد --}}
                    @if($returnRequest->rejection_reason)
                    <div class="card mb-3 border-danger">
                        <div class="card-header bg-danger text-white"><strong>دلیل رد</strong></div>
                        <div class="card-body">
                            <p class="mb-0">{{ $returnRequest->rejection_reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ستون راست: عملیات --}}
                <div class="col-md-4">
                    {{-- اطلاعات کلی --}}
                    <div class="card mb-3">
                        <div class="card-header"><strong>اطلاعات</strong></div>
                        <div class="card-body" style="font-size:0.85rem;">
                            <div class="mb-2"><span class="text-muted">کاربر:</span> {{ $returnRequest->user?->first_name }} {{ $returnRequest->user?->last_name }}</div>
                            <div class="mb-2"><span class="text-muted">موبایل:</span> {{ $returnRequest->user?->mobile }}</div>
                            <div class="mb-2"><span class="text-muted">دلیل:</span> {{ $returnRequest->reason?->title ?? '—' }}</div>
                            <div class="mb-2"><span class="text-muted">مبلغ کل برگشتی:</span> <strong class="text-success">{{ number_format($returnRequest->refund_amount) }} ت</strong></div>
                            <div class="mb-2"><span class="text-muted">تاریخ:</span> {{ jdate($returnRequest->created_at)->format('Y/m/d H:i') }}</div>
                            @if($returnRequest->admin_notes)
                            <div class="mb-2"><span class="text-muted">یادداشت ادمین:</span><br>{{ $returnRequest->admin_notes }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- عملیات --}}
                    @if($returnRequest->isPending())
                    {{-- تایید اولیه --}}
                    <div class="card mb-3">
                        <div class="card-header bg-warning"><strong class="text-white">بررسی اولیه</strong></div>
                        <div class="card-body">
                            <form action="{{ route('admin.returns.approve', $returnRequest) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="یادداشت (اختیاری)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm w-100">
                                    <i class="fas fa-check"></i> تایید اولیه
                                </button>
                            </form>
                            <hr>
                            <form action="{{ route('admin.returns.reject', $returnRequest) }}" method="POST" onsubmit="return confirm('رد شود؟')">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" placeholder="دلیل رد" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times"></i> رد درخواست
                                </button>
                            </form>
                        </div>
                    </div>

                    @elseif($returnRequest->isApproved())
                    {{-- محصول دریافت شد --}}
                    <div class="card mb-3">
                        <div class="card-header bg-primary"><strong class="text-white">دریافت محصول</strong></div>
                        <div class="card-body">
                            <form action="{{ route('admin.returns.received', $returnRequest) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="یادداشت (اختیاری)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-box"></i> محصول دریافت شد
                                </button>
                            </form>
                        </div>
                    </div>

                    @elseif($returnRequest->isReceived())
                    {{-- تایید نهایی + بازگشت وجه --}}
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success"><strong class="text-white">تایید نهایی</strong></div>
                        <div class="card-body">
                            {{-- نمایش تفکیک مبالغ --}}
                            <div class="alert alert-info py-2 mb-2" style="font-size:0.82rem;">
                                @if($returnRequest->wallet_refund_amount > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>بازگشت به کیف پول:</span>
                                        <strong class="text-success">{{ number_format($returnRequest->wallet_refund_amount) }} ت</strong>
                                    </div>
                                @endif
                                @if($returnRequest->credit_restore_amount > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>بازگشت به اعتبار:</span>
                                        <strong class="text-primary">{{ number_format($returnRequest->credit_restore_amount) }} ت</strong>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between mt-1 pt-1" style="border-top:1px dashed #c7d2fe;">
                                    <span>کل بازگشتی:</span>
                                    <strong>{{ number_format($returnRequest->refund_amount) }} ت</strong>
                                </div>
                            </div>

                            <form action="{{ route('admin.returns.complete', $returnRequest) }}" method="POST" onsubmit="return confirm('وجه به کیف پول کاربر برگشت داده شود؟')">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="یادداشت (اختیاری)"></textarea>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="refund_to_wallet" value="1" class="form-check-input" checked id="refund_wallet">
                                    <label for="refund_wallet" class="form-check-label small">بازگشت وجه به کیف پول کاربر</label>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-check-double"></i> تایید نهایی و بازگشت وجه
                                </button>
                            </form>
                            <hr>
                            <form action="{{ route('admin.returns.reship', $returnRequest) }}" method="POST" onsubmit="return confirm('محصول دوباره ارسال شود؟')">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="inspection_result" class="form-control form-control-sm" rows="2" placeholder="یادداشت بررسی (اختیاری)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-info btn-sm w-100">
                                    <i class="fas fa-truck-fast"></i> محصول مشکلی نداشت - ارسال مجدد
                                </button>
                            </form>
                            <hr>
                            <form action="{{ route('admin.returns.reject', $returnRequest) }}" method="POST" onsubmit="return confirm('رد شود؟')">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" placeholder="دلیل رد" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times"></i> رد درخواست
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    @if($returnRequest->isCompleted())
                    <div class="card border-success">
                        <div class="card-header bg-success">
                            <strong class="text-white"><i class="fas fa-check-double"></i> تکمیل شده</strong>
                        </div>
                        <div class="card-body" style="font-size:0.85rem;">
                            @if($returnRequest->paid_to_wallet)
                            <div class="mb-2">
                                <span class="text-muted">واریز به کیف پول:</span>
                                <strong class="text-success">{{ number_format($returnRequest->wallet_refund_amount) }} ت</strong>
                                <span class="badge bg-success ms-1">✓</span>
                            </div>
                            @endif
                            @if($returnRequest->credit_restored)
                            <div class="mb-2">
                                <span class="text-muted">برگشت به اعتبار:</span>
                                <strong class="text-primary">{{ number_format($returnRequest->credit_restore_amount) }} ت</strong>
                                <span class="badge bg-primary ms-1">✓</span>
                            </div>
                            @endif
                            <div class="mt-2">
                                <small class="text-muted">تاریخ تکمیل:</small><br>
                                {{ jdate($returnRequest->completed_at)->format('Y/m/d H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
