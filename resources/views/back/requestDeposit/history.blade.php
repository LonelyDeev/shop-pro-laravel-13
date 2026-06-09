<div class="table-responsive">
    <table class="table">
        <tbody>
        <tr>
            <th scope="row" style="min-width: 200px;">نام</th>
            <td>
                @if($history->source=="user")
                    @if($history->wallet->user)
                    <a class="float-right" href="{{ route('admin.users.show', ['user' => $history->wallet->user]) }}" target="_blank"><i class="feather icon-external-link"></i></a>
                    {{$history->wallet->user->full_name}}
                    @else
                        <div class="badge badge-danger ml-1">حذف شده</div>
                    @endif
                @else
                    @if($history->wallet->seller)
                    {{$history->wallet->seller->full_name}}
                    <a class="float-right" href="{{ route('admin.sellers.show', ['seller' => $history->wallet->seller]) }}" target="_blank"><i class="feather icon-external-link"></i></a>
                    @else
                        <div class="badge badge-danger ml-1">حذف شده</div>
                    @endif
                @endif
                    @if($history->source=="seller")
                        (فروشنده)
                    @elseif($history->source=="user")
                        (کاربر سایت)
                    @endif
            </td>

        </tr>
            <tr>
                <th scope="row" style="min-width: 200px;">آیدی تراکنش</th>
                <td>{{ $history->id }}</td>
            </tr>

            <tr>
                <th scope="row">مبلغ</th>
                <td>{{ number_format($history->amount) }} تومان</td>
            </tr>
            <tr>
                <th scope="row">نوع تراکنش</th>
                <td>
                    @if($history->withdraw)
                        برداشت وجه
                        <div class="badge badge-danger ml-1">
                            <i class="feather icon-arrow-left"></i>
                        </div>
                    @else
                        @if ($history->type == 'deposit')
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
            </tr>

            <tr>
                <th scope="row">تاریخ تراکنش</th>
                <td>{{ jdate($history->created_at) }}</td>
            </tr>
            <tr>
                <th scope="row">وضعیت پرداخت</th>
                <td class="row m-0">
                        @if($history->withdraw)
                            @if($history->status_pay!="unpay-refund")
                            <div class="col-md-8">
                                <select class="form-control status_pay" name="status_pay" data-action="{{route('admin.wallets.status_pay',$history)}}">
                                    <option {{ $history->status_pay == 'waiting' ? 'selected' : '' }} value="waiting">در انتظار پرداخت</option>
                                    <option {{ $history->status_pay == 'pay' ? 'selected' : '' }} value="pay">پرداخت شده</option>
                                    <option {{ $history->status_pay == 'unpay' ? 'selected' : '' }} value="unpay">پرداخت نشده</option>
                                    <option {{ $history->status_pay == 'unpay-refund' ? 'selected' : '' }} value="unpay-refund">پرداخت نشده و بازگشت وجه</option>
                                </select>
                            </div>
                            @endif

                            @endif

                </td>
            </tr>
        <tr id="trackingIdTr" class="{{$history->status_pay=="pay" ? '' : 'd-none'}}">
            <td colspan="2">
                <label>شماره پیگیری(تراکنش)</label>
                <input type="text" class="form-control text-right float-left" style="max-width: 86%" name="trackingId" value="{{$history->trackingId}}">
                <button  type="button" class="btn btn-outline-primary waves-effect waves-light float-right trackingIdBtn" data-action="{{route('admin.wallets.status_pay',$history)}}">ثبت</button>
            </td>
        </tr>
            <tr>
                <th scope="row">توضیحات</th>
                <td>{!! $history->description !!}</td>
            </tr>
        @if($history->wallet->user)
        <tr>
            <th scope="row">شماره کارت</th>
            <td> {{$history->wallet->user->card_number}}</td>
        </tr>
        @elseif($history->wallet->seller)
            <tr>
                <th scope="row">شماره شبا</th>
                <td> {{get_seller_info($history->wallet->seller->id)->shaba_number}}</td>
            </tr>
        @endif
            @if ($history->transaction)
                <tr>
                    <th scope="row">شماره تراکنش</th>
                    <td>{{ $history->transaction->transId }}</td>
                </tr>
                <tr>
                    <th scope="row">شماره پیگیری</th>
                    <td>{{ $history->transaction->traceNumber }}</td>
                </tr>
            @endif

            @if ($history->order)
                <tr>
                    <th scope="row">شماره سفارش</th>
                    <td>
                        {{ $history->order->id }}<a class="float-right" href="{{ route('admin.orders.show', ['order' => $history->order]) }}" target="_blank"><i class="feather icon-external-link"></i></a>
                    </td>
                </tr>
            @endif

        </tbody>
    </table>
</div>
