@php
    $filters = $request->filters;
@endphp

<table>
    <thead>
        <tr>
            @isset($filters['row'])
                <th>ردیف</th>
            @endisset

            @isset($filters['id'])
                <th>آیدی</th>
            @endisset

            @isset($filters['user_id'])
                <th>آیدی کاربر</th>
            @endisset

            @isset($filters['name'])
                <th>نام و نام خانوادگی</th>
            @endisset

            @isset($filters['mobile'])
                <th>شماره همراه</th>
            @endisset

            @isset($filters['province'])
                <th>استان</th>
            @endisset

            @isset($filters['city'])
                <th>شهر</th>
            @endisset

            @isset($filters['postal_code'])
                <th>کد پستی</th>
            @endisset

            @isset($filters['address'])
                <th>آدرس کامل</th>
            @endisset

            @isset($filters['carrier'])
                <th>شیوه تحویل</th>
            @endisset

            @isset($filters['created_at'])
                <th>تاریخ ثبت</th>
            @endisset

            @isset($filters['shipping_cost'])
                <th>هزینه ارسال</th>
            @endisset

            @isset($filters['total_discount'])
                <th>تخفیف</th>
            @endisset

            @isset($filters['price'])
                <th>جمع قیمت</th>
            @endisset

            @isset($filters['description'])
                <th>توضیحات سفارش</th>
            @endisset

        </tr>
    </thead>


    <tbody>
        @foreach($orders as $order)
            @php
                $price=[];
            $orderId=[];
            foreach ($order->items()->get() as $order_item){
                if ($order_item->seller_id==$request->seller->id){
                    $price[]=$order_item->real_price * $order_item->quantity;
                    $orderId[]=$order_item->order_id;
                }
            }
            $orderId=array_unique($orderId);
            $TotalPrice=array_sum($price);

            $price=$TotalPrice + $order->shippingCostSeller();

            $priceSeller=$price-$order->totalDiscountSeller($request->seller->id) ?: 0;
             @endphp
                <tr>
                    @isset($filters['row'])
                        <td>{{ $loop->iteration }}</td>
                    @endisset

                    @isset($filters['id'])
                        <td>{{ $order->id }}</td>
                    @endisset

                    @isset($filters['user_id'])
                        <td>{{ $order->user_id }}</td>
                    @endisset

                    @isset($filters['name'])
                        <td>{{ $order->name }}</td>
                    @endisset

                    @isset($filters['mobile'])
                        <td>{{ $order->mobile }}</td>
                    @endisset

                    @isset($filters['province'])
                        <td>{{ $order->province->name ?? '' }}</td>
                    @endisset

                    @isset($filters['city'])
                        <td>{{ $order->city->name ?? '' }}</td>
                    @endisset

                    @isset($filters['postal_code'])
                        <td>{{ $order->postal_code }}</td>
                    @endisset

                    @isset($filters['address'])
                        <td>{{ $order->address }}</td>
                    @endisset

                    @isset($filters['carrier'])
                        <td>{{ $order->carrier->title ?? '' }}</td>
                    @endisset

                    @isset($filters['created_at'])
                        <td>{{ $order->created_at }}</td>
                    @endisset

                    @isset($filters['shipping_cost'])
                        <td>{{ $order->shippingCostSeller() }}</td>
                    @endisset

                    @isset($filters['total_discount'])
                        <td>{{ $order->totalDiscountSeller($request->seller->id) }}</td>
                    @endisset

                    @isset($filters['price'])
                        <td>{{ $priceSeller  }}</td>
                    @endisset

                    @isset($filters['description'])
                        <td>{{ $order->description }}</td>
                    @endisset

                </tr>

        @endforeach

    </tbody>
</table>
