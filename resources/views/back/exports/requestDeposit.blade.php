@php
    $filters = $request->filters;
@endphp

<table>
    <thead>
        <tr>
            @isset($filters['id'])
                <th>آیدی</th>
            @endisset

            @isset($filters['source'])
                <th>نوع کاربر</th>
            @endisset

            @isset($filters['name'])
                <th>نام و نام خانوادگی</th>
            @endisset

            @isset($filters['mobile'])
                <th>شماره موبایل</th>
            @endisset

            @isset($filters['national_identity_number'])
                <th>کدملی</th>
            @endisset

            @isset($filters['amount'])
                <th>مبلغ</th>
            @endisset

            @isset($filters['card_number'])
                <th>شماره کارت</th>
            @endisset

            @isset($filters['shaba_number'])
                <th>شماره شبا</th>
            @endisset

            @isset($filters['created_at'])
                <th>تاریخ ثبت </th>
            @endisset

        </tr>
    </thead>
    <tbody>
        @foreach($histories as $history)
            <tr>
                @isset($filters['id'])
                    <td>{{ $history->id }}</td>
                @endisset

                @isset($filters['source'])
                    <td>@if($history->source=="seller")
                            فروشنده
                        @elseif($history->source=="user")
                            کاربر سایت
                        @endif</td>
                @endisset

                @isset($filters['name'])
                    @if($history->source=="seller")
                            <td>{{ $history->wallet->seller->full_name }}</td>
                        @else
                            <td>{{ $history->wallet->user->full_name }}</td>
                    @endif

                @endisset

                @isset($filters['mobile'])
                        @if($history->source=="seller")
                            <td>{{ $history->wallet->seller->mobile }}</td>
                        @else
                            <td>{{ $history->wallet->user->mobile }}</td>
                        @endif
                @endisset

                @isset($filters['national_identity_number'])
                        @if($history->source=="seller")
                            <td>{{ $history->wallet->seller->national_identity_number }}</td>
                        @else
                            <td>{{ $history->wallet->user->national_code }}</td>
                        @endif
                @endisset

                @isset($filters['amount'])
                    <td>{{  $history->amount }}</td>
                @endisset


                @isset($filters['card_number'])
                        @if($history->source=="user")
                            <td>{{ $history->wallet->user->card_number }}</td>
                        @else
                            <td></td>
                        @endif
                @endisset


                @isset($filters['shaba_number'])
                        @if($history->source=="seller")
                            <td>{{ $history->wallet->seller->shaba_number }}</td>
                        @else
                            <td></td>
                        @endif
                @endisset


                @isset($filters['created_at'])
                    <td>{{ jdate($history->created_at) }}</td>
                @endisset
            </tr>
        @endforeach
    </tbody>
</table>
