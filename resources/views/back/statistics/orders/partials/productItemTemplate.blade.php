<style>
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 0.5rem 0;
    }

    .product-stat-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
    }

    .product-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 24px rgba(0,0,0,0.12);
    }

    .product-card-img-wrap {
        position: relative;
        height: 180px;
        overflow: hidden;
        background: #f5f5f5;
    }

    .product-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .product-stat-card:hover .product-card-img-wrap img {
        transform: scale(1.04);
    }

    .product-card-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(40, 199, 111, 0.92);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .product-card-body {
        padding: 1rem 1.2rem 0.8rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 0.9rem;
        line-height: 1.5;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 0.7rem;
    }

    .stat-rows {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem 0.8rem;
        margin-bottom: 0.9rem;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        background: #f8f9fb;
        border-radius: 8px;
        padding: 0.5rem 0.7rem;
    }

    .stat-item.wide {
        grid-column: span 2;
    }

    .stat-label {
        font-size: 0.7rem;
        color: #888;
        margin-bottom: 2px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #333;
    }

    .stat-value.success { color: #28c76f; }
    .stat-value.danger  { color: #ea5455; }
    .stat-value.info    { color: #00cfe8; }
    .stat-value.warning { color: #ff9f43; }
    .stat-value.purple  { color: #7367f0; }

    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .stock-indicator.in-stock  { color: #28c76f; }
    .stock-indicator.low-stock { color: #ff9f43; }
    .stock-indicator.no-stock  { color: #ea5455; }

    .profit-row {
        background: linear-gradient(135deg, #28c76f15, #28c76f08);
        border: 1px solid #28c76f30;
        border-radius: 8px;
        padding: 0.55rem 0.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .profit-row .profit-label {
        font-size: 0.75rem;
        color: #555;
        font-weight: 600;
    }

    .profit-row .profit-value {
        font-size: 0.95rem;
        color: #28c76f;
        font-weight: 800;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.6rem;
        border-top: 1px solid #f0f0f0;
    }

    .card-actions a {
        flex: 1;
        text-align: center;
        padding: 0.45rem 0.5rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: opacity 0.15s;
    }

    .card-actions a:hover { opacity: 0.82; }

    .btn-view-orders {
        background: #7367f0;
        color: #fff !important;
    }

    .btn-view-product {
        background: #f1f0ff;
        color: #7367f0 !important;
        border: 1px solid #c9c5f7;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #aaa;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    /* Summary bar */
    .products-summary-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.2rem;
        flex-wrap: wrap;
    }

    .summary-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f4f4f8;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.8rem;
        color: #555;
        font-weight: 600;
    }

    .summary-pill span {
        font-weight: 800;
        color: #333;
    }
</style>

@php
    $totalProducts   = $products->total();
    $totalOrders     = $products->sum('total_orders');
    $totalProfit     = $products->sum('total_profit');
    $totalSuccessful = $products->sum('successful_orders');
@endphp

@if($products->count())

    {{-- Summary bar --}}
    <div class="products-summary-bar">
        <div class="summary-pill">🛍️ نتایج این صفحه: <span>{{ $products->count() }} از {{ number_format($totalProducts) }} محصول</span></div>
        <div class="summary-pill">📦 کل سفارشات (این صفحه): <span>{{ number_format($totalOrders) }}</span></div>
        <div class="summary-pill">✅ سفارش موفق: <span>{{ number_format($totalSuccessful) }}</span></div>
        <div class="summary-pill">💰 سود کل: <span>{{ number_format($totalProfit) }} تومان</span></div>
    </div>

    <div class="products-grid">
        @foreach($products as $item)
            @php
                $stock = (int) $item['available_stock'];
                if ($stock <= 0) {
                    $stockClass = 'no-stock';
                    $stockIcon  = '🔴';
                    $stockLabel = 'ناموجود';
                } elseif ($stock <= 5) {
                    $stockClass = 'low-stock';
                    $stockIcon  = '🟡';
                    $stockLabel = 'موجودی کم';
                } else {
                    $stockClass = 'in-stock';
                    $stockIcon  = '🟢';
                    $stockLabel = 'موجود';
                }

                $successRate = $item['total_orders'] > 0
                    ? round(($item['successful_orders'] / $item['total_orders']) * 100)
                    : 0;
            @endphp

            <div class="product-stat-card">

                <a href="{{ route('admin.products.edit', $item['product_slug']) }}" target="_blank" class="product-card-img-wrap">
                    <img src="{{ asset($item['product_image']) }}" alt="{{ $item['product_title'] }}"
                         onerror="this.src='{{ asset('back/assets/images/pages/no-image.jpg') }}'">
                    @if($item['today_orders'] > 0)
                        <span class="product-card-badge">🔥 {{ $item['today_orders'] }} فروش امروز</span>
                    @endif
                </a>

                <div class="product-card-body">
                    <div class="product-card-title">{{ $item['product_title'] }}</div>

                    <div class="stat-rows">
                        <div class="stat-item">
                            <span class="stat-label">کل سفارشات</span>
                            <span class="stat-value">{{ number_format($item['total_orders']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">نرخ موفقیت</span>
                            <span class="stat-value {{ $successRate >= 70 ? 'success' : ($successRate >= 40 ? 'warning' : 'danger') }}">
                                {{ $successRate }}٪
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">سفارش موفق</span>
                            <span class="stat-value success">{{ number_format($item['successful_orders']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">سفارش ناموفق</span>
                            <span class="stat-value danger">{{ number_format($item['failed_orders']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">مرجوعی</span>
                            <span class="stat-value warning">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">موجودی انبار</span>
                            <span class="stat-value">
                                <span class="stock-indicator {{ $stockClass }}">
                                    {{ $stockIcon }} {{ number_format($stock) }} {{ $stockLabel }}
                                </span>
                            </span>
                        </div>
                        <div class="stat-item wide">
                            <span class="stat-label">مبلغ کل سفارشات</span>
                            <span class="stat-value purple">{{ number_format($item['total_order_amount']) }} تومان</span>
                        </div>
                    </div>

                    <div class="profit-row">
                        <span class="profit-label">💰 سود خالص فروش</span>
                        <span class="profit-value">{{ number_format($item['total_profit']) }} تومان</span>
                    </div>

                    <div class="card-actions">
                        <a href="{{ route('admin.orders.index', [
                            'fullname'        => '',
                            'username'        => '',
                            'id'              => '',
                            'status'          => 'all',
                            'shipping_status' => 'all',
                            'product_name'    => '',
                            'product_id'      => $item['product_id'],
                            'from_date'       => '',
                            'to_date'         => '',
                        ]) }}" target="_blank" class="btn-view-orders">
                            📋 مشاهده سفارشات
                        </a>
                        <a href="{{ route('admin.products.edit', $item['product_slug']) }}" target="_blank" class="btn-view-product">
                            ✏️ ویرایش محصول
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@else
    <div class="empty-state">
        <i>📦</i>
        <p>محصولی برای نمایش وجود ندارد.<br>فیلترهای جستجو را تغییر دهید.</p>
    </div>
@endif

<div class="row flex-column mt-2">
    {{ $products->withPath(route('admin.statistics.orders.products'))->links() }}
</div>
