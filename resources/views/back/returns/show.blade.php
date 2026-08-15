@extends('back.layouts.master')

@section('title', 'جزئیات مرجوعی #' . $returnRequest->id)

@section('content')
@php
    $statusInfo = \App\Models\ReturnRequest::statusLabels()[$returnRequest->status] ?? ['label' => $returnRequest->status, 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-circle'];
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
                <span style="background:{{ $statusInfo['bg'] }};color:{{ $statusInfo['color'] }};padding:6px 16px;border-radius:999px;font-weight:700;">
                    <i class="fas {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
                </span>
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
                                        · مبلغ: {{ number_format(($item?->price ?? 0) * ($item?->quantity ?? 0)) }} ت
                                    </small>
                                </div>
                            </div>
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
                            <div class="mb-2"><span class="text-muted">مبلغ برگشتی:</span> <strong class="text-success">{{ number_format($returnRequest->refund_amount) }} ت</strong></div>
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
                            <div class="alert alert-info py-2 mb-2" style="font-size:0.82rem;">
                                مبلغ قابل برگشت: <strong>{{ number_format($returnRequest->refund_amount) }} ت</strong>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
