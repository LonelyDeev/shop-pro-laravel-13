<div class='productItems'>
    @if($products->count())
        @foreach($products as $item)

            <div class="productItem">
                <a href="{{route('admin.products.edit', $item['product_slug'])}}" target="_blank" class="pic">
                    <img class='w-100' src="{{ $item['product_image'] }}" alt="{{ $item['product_title'] }}">
                </a>
                <h3>{{ $item['product_title'] }}</h3>
                <h5>
                    تعداد سفارش :
                    <span>
               {{ number_format($item['total_orders']) }}
            </span>
                </h5>
                <h5>
                    تعداد سفارش موفق:
                    <span>
                {{ $item['successful_orders'] }}
            </span>
                </h5>
                <h5>
                    تعداد سفارش ناموفق:
                    <span>{{ $item['failed_orders'] }}</span>
                </h5>
                <h5>
                    تعداد سفارش امروز :
                    <span>{{ $item['today_orders'] }}</span>
                </h5>
                <h5>
                    تعداد مرجوعی :
                    <span>0</span>
                </h5>
                <h5>
                    تعداد موجود :
                    <span>
               {{ number_format($item['available_stock']) }}
                </span>
                </h5>
                <h5>
                    مبلغ کل سفارش :
                    <span>
               {{ number_format($item['total_order_amount']) }} تومان
                </span>
                </h5>
                      <h5>
                          سود فروش :
                          <span>
                              <span class="badge badge-success">{{ number_format($item['total_profit']) }} تومان </span>
                          </span>

                      </h5>
                <a href='{{route('admin.orders.index',  [
    'fullname'=>'',
    'username'=>'',
    'id'=>'',
    'status'=>'all',
    'shipping_status'=>'all',
    'product_name'=>'',
    'product_id'=>$item['product_id'],
    'from_date'=>'',
    'to_date'=>'',
    ]) }}' target='_blank' class="btn btn-primary waves-effect waves-light show">مشاهده سفارشات</a>
            </div>
        @endforeach
    @else
            <div class="card-content">
                    <div class="card-text">
                        <p>چیزی برای نمایش وجود ندارد!</p>
                    </div>
            </div>
    @endif
</div>

<div class="row flex-column">
    {{ $products->withPath(route('admin.statistics.orders.products'))->links() }}
</div>

