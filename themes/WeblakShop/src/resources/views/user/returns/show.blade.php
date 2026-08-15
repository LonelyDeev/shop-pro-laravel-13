@extends('front::user.layouts.master')

@section('user-content')
@php
    $statusInfo = \App\Models\ReturnRequest::statusLabels()[$returnRequest->status] ?? ['label' => $returnRequest->status, 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-circle'];
@endphp

<div class="headline-profile">
    <a href="{{ route('front.returns.index') }}" class="text-dark">
        <i class="fas fa-arrow-right"></i>
    </a>
    <span>جزئیات مرجوعی #{{ $returnRequest->id }}</span>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">درخواست مرجوعی #{{ $returnRequest->id }}</h5>
        <span style="background:{{ $statusInfo['bg'] }};color:{{ $statusInfo['color'] }};padding:5px 14px;border-radius:999px;font-size:0.82rem;font-weight:700;">
            <i class="fas {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
        </span>
    </div>
    <div class="card-body">

        {{-- اطلاعات محصول --}}
        <div class="d-flex gap-3 py-3 border-bottom mb-3 align-items-center">
            @if($returnRequest->orderItem?->product?->image)
            <img src="{{ asset($returnRequest->orderItem->product->image) }}"
                 class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;">
            @endif
            <div>
                <h6 class="fw-bold mb-1">{{ $returnRequest->orderItem?->title ?? '—' }}</h6>
                <div class="small text-muted">
                    سفارش #{{ $returnRequest->order_id }} · تعداد: {{ $returnRequest->orderItem?->quantity ?? 0 }}
                </div>
            </div>
        </div>

        {{-- اطلاعات مرجوعی --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="text-muted small">دلیل مرجوعی</div>
                <div class="fw-bold">{{ $returnRequest->reason?->title ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">مبلغ قابل برگشت</div>
                <div class="fw-bold text-success">{{ number_format($returnRequest->refund_amount) }} ت</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">تاریخ درخواست</div>
                <div class="fw-bold">{{ jdate($returnRequest->created_at)->format('Y/m/d H:i') }}</div>
            </div>
        </div>

        {{-- توضیحات کاربر --}}
        @if($returnRequest->description)
        <div class="mb-3">
            <div class="text-muted small">توضیحات شما</div>
            <div class="bg-light rounded p-3">{{ $returnRequest->description }}</div>
        </div>
        @endif

        {{-- تصاویر --}}
        @if($returnRequest->images->count())
        <div class="mb-3">
            <div class="text-muted small mb-2">تصاویر ارسالی</div>
            <div class="d-flex gap-2 flex-wrap">
                @foreach($returnRequest->images as $image)
                <a href="{{ Storage::url($image->path) }}" target="_blank">
                    <img src="{{ Storage::url($image->path) }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- یادداشت ادمین --}}
        @if($returnRequest->admin_notes)
        <div class="mb-3">
            <div class="text-muted small">یادداشت مدیریت</div>
            <div class="bg-light rounded p-3">{{ $returnRequest->admin_notes }}</div>
        </div>
        @endif

        {{-- دلیل رد --}}
        @if($returnRequest->rejection_reason)
        <div class="alert alert-danger">
            <strong>دلیل رد:</strong> {{ $returnRequest->rejection_reason }}
        </div>
        @endif

        {{-- دکمه لغو --}}
        @if($returnRequest->canBeCancelled())
        <form action="{{ route('front.returns.cancel', $returnRequest) }}" method="POST"
              onsubmit="return confirm('آیا از لغو درخواست مرجوعی مطمئن هستید؟')">
            @csrf
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-times"></i> لغو درخواست
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
