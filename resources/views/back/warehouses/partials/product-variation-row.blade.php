@php
$loop_first=null;
if ($loop){
    $loop_first=$loop->first;
}
@endphp
<tr id="variation-row-{{ $price->id }}" class="{{ @$loop_first && $price->sold_count > 0 ? 'best-seller-row' : '' }}">
    <td>{{ $loop->iteration }}</td>
    <td>
        @if($price->attributes && $price->attributes->count())
            @foreach($price->attributes as $attr)
                <span class="badge bg-light text-dark me-1">{{ $attr->name }}</span>
            @endforeach
        @else
            <span class="text-muted">بدون ویژگی</span>
        @endif
        @if($loop_first && $price->sold_count > 0)
            <span class="badge bg-warning text-dark ms-2">
                                    <i class="feather icon-star"></i> پرفروش‌ترین
                                </span>
        @endif
    </td>
    <td class="text-center">{{ number_format($price->price) }} تومان</td>
    <td class="text-center">
        @if($price->discount_price && $price->discount_price < $price->price)
            <span class="text-success">{{ number_format($price->discount_price) }} تومان</span>
            <small class="text-danger d-block">({{ $price->discount }}%)</small>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center">
        @if($price->stock > 0)
            <span class="badge bg-success">{{ number_format($price->stock) }}</span>
        @else
            <span class="badge bg-danger">ناموجود</span>
        @endif
    </td>
    <td class="text-center">
                            <span class="badge {{ $price->sold_count > 100 ? 'bg-success' : ($price->sold_count > 10 ? 'bg-info' : 'bg-secondary') }}">
                                {{ number_format($price->sold_count ?? 0) }}
                            </span>
        @if($price->sold_count > 0 && $stats['total_sold_current'] > 0)
            <div class="progress-sold">
                <div class="progress-sold-bar" style="width: {{ ($price->sold_count / $stats['total_sold_current']) * 100 }}%"></div>
            </div>
            <small class="text-muted">{{ round(($price->sold_count / $stats['total_sold_current']) * 100, 1) }}% از کل</small>
        @endif
    </td>
    <td class="text-center">
                            <span class="badge {{ $price->published ? 'bg-success' : 'bg-danger' }}">
                                {{ $price->published ? 'فعال' : 'غیرفعال' }}
                            </span>
    </td>
    <td class="text-center">
        {{-- دکمه ویرایش --}}
        <button type="button"
                class="btn btn-sm btn-warning btn-edit-variation"
                data-action="{{ route('admin.warehouses.product-variations.update', ['warehouse'=>$warehouse,'product'=>$product,'price'=> $price]) }}"
                data-price-data="{{route('admin.warehouses.product-variations.variation', ['warehouse'=>$warehouse,'product'=>$product,'price'=> $price])}}"
                data-price-id="{{ $price->id }}"
                data-toggle="modal"
                data-target="#editVariationModal"
                title="ویرایش">
            <i class="feather icon-edit-2"></i>
        </button>
        {{-- دکمه حذف --}}
        <button type="button"
                class="btn btn-sm btn-danger btn-delete-variation"
                data-price-id="{{ $price->id }}"
                data-url="{{ route('admin.warehouses.product-variations.destroy', ['warehouse'=>$warehouse,'product'=>$product,'price'=> $price]) }}"
                title="حذف">
            <i class="feather icon-trash-2"></i>
        </button>
    </td>
</tr>
