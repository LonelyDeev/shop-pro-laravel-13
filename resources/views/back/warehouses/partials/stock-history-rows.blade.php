@forelse($movements as $movement)
    <tr>
        <td>
            <small class="text-muted">{{ jdate($movement->created_at)->format('d F Y H:i:s') }}</small>
        </td>
        <td>
            @php
                $typeLabels = [
                    'in' => ['label' => 'ورود', 'class' => 'bg-success'],
                    'out' => ['label' => 'خروج', 'class' => 'bg-danger'],
                    'reserve' => ['label' => 'رزرو', 'class' => 'bg-warning'],
                    'unreserve' => ['label' => 'لغو رزرو', 'class' => 'bg-info'],
                    'adjustment' => ['label' => 'تنظیم دستی', 'class' => 'bg-secondary'],
                ];
                $typeInfo = $typeLabels[$movement->type] ?? ['label' => $movement->type, 'class' => 'bg-secondary'];
            @endphp
            <span class="badge {{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
        </td>
        <td>
            @if($movement->price && $movement->price->attributes)
                @foreach($movement->price->attributes as $attr)
                    <span class="badge bg-light text-dark me-1">{{ $attr->name }}</span>
                @endforeach
                <br>
                <small class="text-muted">کد: {{ $movement->price_id }}</small>
            @else
                <span class="text-muted">کد تنوع: {{ $movement->price_id }}</span>
            @endif
        </td>
        <td class="text-center fw-bold">{{ number_format($movement->quantity) }}</td>
        <td class="text-center">
            <small>{{ number_format($movement->before_stock) }} ← {{ number_format($movement->after_stock) }}</small>
            @php
                $diff = $movement->after_stock - $movement->before_stock;
            @endphp
            @if($diff != 0)
                <br>
                <small class="{{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                    ({{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }})
                </small>
            @endif
        </td>
        <td>
            <small class="text-muted">{{ Str::limit($movement->description ?? '-', 60) }}</small>
        </td>
        <td>
            <small class="text-muted">{{ $movement->operator_type ?? '-' }}<br>{{ $movement->operator_id ?? '-' }}</small>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <i class="feather icon-inbox fa-2x d-block mb-2 text-muted"></i>
            <span class="text-muted">هیچ حرکتی برای این محصول یافت نشد</span>
        </td>
    </tr>
@endforelse
