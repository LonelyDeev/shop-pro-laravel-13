@extends('back.layouts.master')

@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    @if($wallet->seller_id)
                                    <li class="breadcrumb-item">مدیریت فروشندگان</li>
                                    <li class="breadcrumb-item">
                                            <a href="{{ route('admin.sellers.show', ['seller' => $wallet->seller]) }}">{{ $wallet->sellerInfo->fullname }}</a>
                                    </li>
                                    @else
                                        <li class="breadcrumb-item">مدیریت کاربران
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.users.show', ['user' => $wallet->user]) }}">{{ $wallet->user->fullname }}</a>
                                        </li>
                                    @endif
                                    <li class="breadcrumb-item active">تاریخچه کیف پول
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title" title="{{ convert_number($wallet->balance) . ' تومان' }}">تاریخچه کیف پول (موجودی: {{ number_format($wallet->balance) }})</h4>
                        <div>
                            <a href="{{ route('admin.wallets.create', ['wallet' => $wallet]) }}" class="btn personal-info-btn waves-effect waves-light"><i class="feather icon-plus"></i> ایجاد تراکنش</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body table-responsive">

                            @if($histories->count())
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>مبلغ (تومان)</th>
                                            <th>نوع تراکنش</th>
                                            <th>تاریخ</th>
                                            <th class="text-center">وضعیت</th>
                                            <th class="text-center">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($histories as $history)
                                            @php
                                                $is_deposit = $history->type == 'deposit';
                                            @endphp

                                            <tr id="data-{{$history->id}}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="{{ $is_deposit ? 'text-success' : 'text-danger' }}">{{ number_format($history->amount) }}</td>
                                                <td>
                                                    @if($history->withdraw)
                                                        برداشت وجه
                                                        <div class="badge badge-danger ml-1">
                                                            <i class="feather icon-arrow-left"></i>
                                                        </div>
                                                    @else
                                                        @if ($is_deposit)
                                                            افزایش اعتبار
                                                            <div class="badge badge-success ml-1">
                                                                <i class="feather icon-arrow-up"></i>
                                                            </div>
                                                        @else
                                                            کاهش اعتبار
                                                            <div class="badge badge-danger ml-1">
                                                                <i class="feather icon-arrow-down"></i>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ jdate($history->created_at) }}</td>
                                                <td class="text-center">
                                                    @if($history->status == 'success')
                                                        <div class="badge badge-success">موفق</div>
                                                    <div class="status-pay">
                                                        @if($history->withdraw)
                                                            @if($history->status_pay=="waiting")
                                                                <div class="badge badge-warning">در انتظار پرداخت</div>
                                                            @elseif($history->status_pay=="pay")
                                                                <div class="badge badge-success">پرداخت شده</div>
                                                            @elseif($history->status_pay=="unpay")
                                                                <div class="badge badge-danger">پرداخت نشده</div>
                                                            @elseif($history->status_pay=="unpay-refund")
                                                                <div class="badge badge-danger">پرداخت نشده و مبلغ عودت داده شده</div>
                                                            @endif

                                                        @endif
                                                    </div>

                                                    @else
                                                        <div class="badge badge-danger">ناموفق</div>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <button data-action="{{ route('admin.wallets.history', ['history' => $history]) }}" class="btn btn-info waves-effect waves-light show-history">مشاهده</button>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            @else
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            @endif

                            {{ $histories->links() }}
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <!-- show Modal -->
    <div class="modal fade text-left" id="show-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel21">جزئیات تراکنش</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="history-detail" class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/wallets/show.js') }}"></script>
@endpush
