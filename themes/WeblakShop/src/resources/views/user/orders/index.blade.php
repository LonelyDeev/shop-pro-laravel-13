@extends('front::user.layouts.master')

@section('user-content')
    <div class="headline-profile page-profile-order">
        <span>همه سفارش ها</span>
    </div>
    <div class="profile-stats page-profile-order">
        <div class="table-orders">
            <table class="table">
                <thead class="thead-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">شماره سفارش</th>
                    <th scope="col">تاریخ ثبت سفارش</th>
                    <th scope="col">تخفیف</th>
                    <th scope="col">مبلغ قابل پرداخت</th>
                    <th scope="col">عملیات پرداخت</th>
                    <th scope="col">جزئیات</th>
                </tr>
                </thead>
                <tbody>
                @if($orders->count())
                    @foreach ($orders as $order)

                        <tr>
                            <td>{{ $loop->iteration}}</td>
                            <td class="text-info">{{ $order->id }}</td>
                            <td>{{ jdate($order->created_at)->format('%d %B %Y') }}</td>
                            @if($order->totalDiscount()!=0)
                            <td>{{ number_format($order->totalDiscount()) }} تومان</td>
                            @else
                                <td>بدون تخفیف</td>
                                @endif
                            <td>{{ number_format($order->price) }} تومان</td>
                            <td>
                                @if($order->status == 'paid')
                                    <span class="text-success">پرداخت شده</span>
                                @elseif($order->status == 'unpaid')
                                    <span class="text-danger">پرداخت نشده</span>
                                @else
                                    <span class="text-danger">لغو شده</span>
                                @endif
                            </td>
                            <td class="details-link">
                                <a href="{{ route('front.orders.show', ['order' => $order]) }}">
                                    <i class="mdi mdi-chevron-left"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7">چیزی برای نمایش وجود ندارد!</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="page-profile headline-profile-favorites">
        <div class="page-navigation">
            <div class="page-navigation-title">سفارش‌های من</div>
        </div>
        <div class="profile-orders">
            @if(count($orders))
                @foreach ($orders as $order)
                    <div class="collapse">
                        <div class="profile-orders-item">
                            <div class="profile-orders-header">
                                <a href="{{ route('front.orders.show', ['order' => $order]) }}" class="profile-orders-header-details">
                                    <div class="profile-orders-header-summary">
                                        <div class="profile-orders-header-row">
                                            <span class="profile-orders-header-id">{{ $order->id }}</span>
                                            <span class="profile-orders-header-state">

                                        @if($order->status == 'paid')
                                                    <span class="text-success">پرداخت شده</span>
                                                @elseif($order->status == 'unpaid')
                                                    <span class="text-danger">پرداخت نشده</span>
                                                @else
                                                    <span class="text-danger">لغو شده</span>
                                                @endif
                                    </span>
                                        </div>
                                    </div>
                                </a>
                                <hr class="ui-separator">
                                <div class="profile-orders-header-data">
                                    <div class="profile-info-row">
                                        <div class="profile-info-label">تاریخ ثبت سفارش</div>
                                        <div class="profile-info-value">{{ jdate($order->created_at)->format('%d %B %Y') }}</div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-label">تخفیف</div>
                                        <div class="profile-info-value">
                                            @if($order->totalDiscount()!=0)
                                                {{ number_format($order->totalDiscount()) }} تومان
                                            @else
                                                بدون تخفیف
                                            @endif
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-label">مبلغ کل</div>
                                        <div class="profile-info-value">{{ number_format($order->price) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="collapse">
                    <div class="profile-orders-item text-center">
                        چیزی برای نمایش وجود ندارد!
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="pager pager-back-none">
        {{$orders->links("pagination::bootstrap-4")}}
    </div>

@endsection
