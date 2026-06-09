<table class="table table-striped">
    <thead>
    <tr><th>انبار</th><th>ویژگی‌ها</th><th class="text-center">قیمت</th><th class="text-center">قیمت با تخفیف</th><th class="text-center">موجودی</th><th class="text-center">فروش</th></tr>
    </thead>
    <tbody>
    @foreach($variations as $price)
        <tr>
            <td><strong>{{ $price->warehouse_name ?? 'نامشخص' }}</strong><br><small class="text-muted">کد: {{ $price->warehouse_code ?? '-' }}</small></td>
            <td>@if($price->attributes && $price->attributes->count()) @foreach($price->attributes as $attr)<span class="badge bg-light text-dark me-1">{{ $attr->name }}</span>@endforeach @else <span class="text-muted">بدون ویژگی</span> @endif</td>
            <td class="text-center">{{ number_format($price->price) }} تومان</td>
            <td class="text-center">@if(($price->discount_price ?? 0) && $price->discount_price < $price->price)<span class="text-success">{{ number_format($price->discount_price) }} تومان</span><br><small class="text-danger">({{ $price->discount }}%)</small>@else <span class="text-muted">-</span>@endif</td>
            <td class="text-center">@if($price->stock > 0)<span class="badge bg-success">{{ number_format($price->stock) }}</span>@else<span class="badge bg-danger">ناموجود</span>@endif</td>
            <td class="text-center"><span class="badge bg-secondary">{{ number_format($price->sold_count ?? 0) }}</span></td>
        </tr>
    @endforeach
    </tbody>
</table>
