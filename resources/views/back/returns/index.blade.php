@extends('back.layouts.master')

@section('title', 'مدیریت مرجوعی‌ها')

@section('content')
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="breadcrumb-wrapper">
                    <ol class="breadcrumb no-border">
                        <li class="breadcrumb-item">مدیریت</li>
                        <li class="breadcrumb-item active">مرجوعی‌ها</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="content-body">
            <h4 class="mb-3"><i class="fas fa-undo-alt text-warning"></i> مدیریت مرجوعی‌ها</h4>

            {{-- آمار --}}
            <div class="row g-2 mb-3">
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border">
                        <div class="fs-5 fw-bold">{{ $stats['total'] }}</div>
                        <small class="text-muted">کل</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border" style="border-color:#fde68a !important;background:#fffbeb;">
                        <div class="fs-5 fw-bold text-warning">{{ $stats['pending'] }}</div>
                        <small class="text-muted">در حال بررسی</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border" style="border-color:#bfdbfe !important;background:#eff6ff;">
                        <div class="fs-5 fw-bold text-primary">{{ $stats['approved'] }}</div>
                        <small class="text-muted">تایید اولیه</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border" style="border-color:#ddd6fe !important;background:#f5f3ff;">
                        <div class="fs-5 fw-bold text-purple">{{ $stats['received'] }}</div>
                        <small class="text-muted">دریافت شد</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border" style="border-color:#a7f3d0 !important;background:#ecfdf5;">
                        <div class="fs-5 fw-bold text-success">{{ $stats['completed'] }}</div>
                        <small class="text-muted">تکمیل شده</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center p-2 border" style="border-color:#fecaca !important;background:#fef2f2;">
                        <div class="fs-5 fw-bold text-danger">{{ $stats['rejected'] }}</div>
                        <small class="text-muted">رد شده</small>
                    </div>
                </div>
            </div>

            {{-- فیلترها --}}
            <div class="card mb-3">
                <div class="card-body p-2">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="جستجو: شماره سفارش، نام، موبایل">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">همه وضعیت‌ها</option>
                                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>در حال بررسی</option>
                                <option value="approved" {{ request('status')=='approved'?'selected':'' }}>تایید اولیه</option>
                                <option value="received" {{ request('status')=='received'?'selected':'' }}>دریافت شد</option>
                                <option value="completed" {{ request('status')=='completed'?'selected':'' }}>تکمیل شده</option>
                                <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>رد شده</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">فیلتر</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- جدول --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>سفارش</th>
                                    <th>کاربر</th>
                                    <th>محصول</th>
                                    <th>دلیل</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت</th>
                                    <th>تاریخ</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returns as $return)
                                @php
                                    $statusInfo = \App\Models\ReturnRequest::statusLabels()[$return->status] ?? ['label' => $return->status, 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-circle'];
                                @endphp
                                <tr>
                                    <td>{{ $return->id }}</td>
                                    <td><a href="{{ route('admin.orders.show', $return->order_id) }}" target="_blank">#{{ $return->order_id }}</a></td>
                                    <td>
                                        {{ $return->user?->first_name ?? '' }} {{ $return->user?->last_name ?? '' }}
                                        <br><small class="text-muted">{{ $return->user?->mobile ?? '' }}</small>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($return->orderItem?->title ?? '—', 25) }}</td>
                                    <td>{{ $return->reason?->title ?? '—' }}</td>
                                    <td>{{ number_format($return->refund_amount) }} ت</td>
                                    <td>
                                        <span style="background:{{ $statusInfo['bg'] }};color:{{ $statusInfo['color'] }};padding:3px 8px;border-radius:6px;font-size:0.72rem;font-weight:600;">
                                            <i class="fas {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ jdate($return->created_at)->format('Y/m/d') }}</td>
                                    <td>
                                        <a href="{{ route('admin.returns.show', $return) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                @if($returns->isEmpty())
                                <tr><td colspan="9" class="text-center text-muted py-4">هیچ درخواست مرجوعی یافت نشد</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($returns->hasPages())
                <div class="card-footer">{{ $returns->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
