<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>تصویر</th>
            <th>نام محصول</th>
            <th>دسته‌بندی</th>
            <th>برند</th>
            <th class="text-center">تنوع‌ها</th>
            <th class="text-center">کل موجودی</th>
            <th class="text-center">رزرو شده</th>
            <th class="text-center">فروش</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            @php
                $productPrices = $product->prices->where('warehouse_id', $warehouse->id);
                $totalStock = $productPrices->sum('stock');
                $totalReserved = $productPrices->sum('reserved_stock');
                $totalSold = $productPrices->sum('sold_count');
                $variationsCount = $productPrices->count();

                // جمع‌آوری اطلاعات تنوع‌ها برای نمایش در tooltip
                $variationsList = [];
                foreach($productPrices as $price) {
                    $attributes = $price->get_attributes->map(function($attr) {
                        return $attr->name;
                    })->implode(' - ');
                    $variationsList[] = [
                        'attributes' => $attributes ?: 'بدون ویژگی',
                        'stock' => $price->stock,
                        'price' => number_format($price->price)
                    ];
                }
            @endphp
            <tr>
                <td class="text-center">
                    <img src="{{ $product->image ? asset($product->image) : asset('empty.svg') }}"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product) }}" class="fw-bold">
                        {{ $product->title }}
                    </a>
                    <br>
                    <small class="text-muted">کد: {{ $product->id }}</small>
                </td>
                <td>{{ $product->category->title ?? '-' }}</td>
                <td>{{ $product->brand->name ?? '-' }}</td>
                <td class="text-center">
                                <span class="badge bg-info" style="cursor: pointer;" data-toggle="popover"
                                      data-html="true"
                                      data-title="<i class='feather icon-layers'></i> تنوع‌های محصول"
                                      data-content="<div class='variations-list' style='max-height: 200px; overflow-y: auto;'>
                                          @foreach($variationsList as $variation)
                                              <div class='border-bottom pb-1 mb-1'>
                                                  <small><strong>ویژگی‌ها:</strong> {{ $variation['attributes'] }}</small><br>
                                                  <small><strong>موجودی:</strong> {{ number_format($variation['stock']) }}</small><br>
                                                  <small><strong>قیمت:</strong> {{ $variation['price'] }} تومان</small>
                                              </div>
                                          @endforeach
                                      </div>">
                                    <i class="feather icon-layers"></i> {{ $variationsCount }}
                                </span>
                </td>
                <td class="text-center {{ $totalStock < 5 ? 'text-danger fw-bold' : '' }}">
                    {{ number_format($totalStock) }}
                </td>
                <td class="text-center">{{ number_format($totalReserved) }}</td>
                <td class="text-center">{{ number_format($totalSold) }}</td>
                <td>
                    <div class="dropdown dropdown-action">
                        <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="feather icon-more-horizontal"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}">
                                <i class="feather icon-edit"></i> ویرایش محصول
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.warehouses.product-variations', [ 'warehouse' => $warehouse,'product' => $product]) }}" target="_blank">
                                <i class="feather icon-layers"></i> مشاهده تنوع‌ها
                            </a>
                            <a class="dropdown-item" onclick="showStockHistory(this, {{ $product->id }})" data-action="{{ route("admin.warehouses.product.stock-history", ["warehouse" => $warehouse, "product" => $product]) }}">
                                <i class="feather icon-clock"></i> تاریخچه موجودی
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="feather icon-box fa-2x d-block mb-2 text-muted"></i>
                    <span class="text-muted">هیچ محصولی در این انبار وجود ندارد</span>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $products->appends(request()->all())->links() }}
</div>
