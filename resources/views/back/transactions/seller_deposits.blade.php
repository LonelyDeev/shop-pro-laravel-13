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
                                    <li class="breadcrumb-item">پرداخت
                                    </li>
                                    <li class="breadcrumb-item active"> لیست کمیسیون فروشندگان
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
                        <h4 class="card-title"> لیست کمیسیون فروشندگان</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body table-responsive">

                            @if($histories->count())
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>فروشنده مربوطه</th>
                                            <th>دسته بندی مربوطه</th>
                                            <th>مبلغ (تومان)</th>
                                            <th>درصد</th>
                                            <th>نوع تراکنش</th>
                                            <th>شماره سفارش	</th>
                                            <th>تاریخ</th>
                                            <th class="text-center">وضعیت</th>
                                            <th class="text-center">توضیحات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($histories as $history)
                                            @php
                                                $is_deposit = $history->type == 'deposit';
                                            @endphp

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                    <td>
                                                        <span class="d-inline-block">{{ @$history->seller->fullname }}</span>
                                                        @if($history->seller)
                                                            <a href="{{ route('admin.sellers.show', ['seller' => $history->seller]) }}" target="_blank">
                                                                <i class="feather icon-external-link"></i>
                                                            </a>
                                                        @endif

                                                    </td>

                                                <td>
                                                    <span class="d-inline-block">{{ @$history->category->title }}</span>
                                                </td>
                                                <td class="text-success">{{ number_format($history->amount) }}</td>
                                                <td class="text-success">%{{$history->percent }}</td>
                                                <td>

                                                        واریز شده
                                                        <div class="badge badge-success ml-1">
                                                            <i class="feather icon-arrow-up"></i>
                                                        </div>

                                                </td>
                                                <td class="text-center">
                                                    @if($history->order and $history->seller)
                                                        <a href="{{route('admin.sellers.orders.show',['seller'=>$history->seller,'order'=>$history->order])}}">سفارش {{$history->order_id}}</a>
                                                    @endif

                                                </td>
                                                <td>{{ jdate($history->created_at) }}</td>
                                                <td class="text-center">
                                                    @if($history->status == 'success')
                                                        <div class="badge badge-success">موفق</div>
                                                    @else
                                                        <div class="badge badge-danger">ناموفق</div>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    {!! $history->description !!}
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
    <script src="{{ asset('back/assets/js/pages/wallet-histories/index.js') }}"></script>
@endpush
