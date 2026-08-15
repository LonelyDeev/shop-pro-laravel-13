@extends('front::user.layouts.master')

@section('user-content')
<div class="headline-profile">
    <a href="{{ route('front.orders.show', $order) }}" class="text-dark">
        <i class="fas fa-arrow-right"></i>
    </a>
    <span>درخواست مرجوعی محصول</span>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-bold">ثبت درخواست مرجوعی</h5>
    </div>
    <div class="card-body">

        {{-- اطلاعات محصول --}}
        <div class="d-flex gap-3 py-3 border-bottom mb-3 align-items-center">
            @if($orderItem->product)
            <img src="{{ $orderItem->product->image ? asset($orderItem->product->image) : '/empty.svg' }}"
                 class="rounded-3" style="width: 70px; height: 70px; object-fit: cover;">
            @endif
            <div>
                <h6 class="fw-bold mb-1">{{ $orderItem->title }}</h6>
                <div class="small text-muted">
                    سفارش #{{ $order->id }} · تعداد: {{ $orderItem->quantity }} ·
                    مبلغ: {{ number_format($orderItem->price * $orderItem->quantity) }} ت
                </div>
            </div>
        </div>

        <form action="{{ route('front.returns.store', ['order' => $order, 'orderItem' => $orderItem]) }}"
              method="POST" enctype="multipart/form-data">
            @csrf

            {{-- دلیل مرجوعی --}}
            <div class="mb-3">
                <label class="form-label fw-bold">دلیل مرجوعی <span class="text-danger">*</span></label>
                <select name="return_reason_id" class="form-select" required>
                    <option value="">انتخاب دلیل...</option>
                    @foreach($reasons as $reason)
                    <option value="{{ $reason->id }}">{{ $reason->title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- توضیحات --}}
            <div class="mb-3">
                <label class="form-label fw-bold">توضیحات <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" required
                          placeholder="مشکل محصول را به‌طور کامل توضیح دهید..."></textarea>
            </div>

            {{-- گالری تصاویر --}}
            <div class="mb-3">
                <label class="form-label fw-bold">تصاویر محصول (حداکثر ۵ تصویر)</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">تصاویر مشکلات محصول را آپلود کنید (jpg, png, gif - حداکثر ۵MB هر تصویر)</small>
            </div>

            {{-- هشدار --}}
            <div class="alert alert-warning">
                <small>
                    <i class="fas fa-info-circle"></i>
                    با ثبت این درخواست، وضعیت محصول به «در حال بررسی مرجوعی» تغییر می‌کند.
                    پس از تایید نهایی ادمین، مبلغ محصول ({{ number_format($orderItem->price * $orderItem->quantity) }} ت) به کیف پول شما برگشت داده می‌شود.
                    هزینه ارسال قابل بازگشت نیست.
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> ثبت درخواست مرجوعی
            </button>
            <a href="{{ route('front.orders.show', $order) }}" class="btn btn-outline-secondary">انصراف</a>
        </form>
    </div>
</div>
@endsection
