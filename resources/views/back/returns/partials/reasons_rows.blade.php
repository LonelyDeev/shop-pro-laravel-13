@foreach($reasons as $reason)
    <tr id="reason-{{ $reason->id }}">
        <td>{{ $reason->id }}</td>
        <td>{{ $reason->title }}</td>
        <td>{{ $reason->description ?? '—' }}</td>
        <td>
            @if($reason->is_active)
                <span class="badge badge-success status-badge-{{ $reason->id }}">فعال</span>
            @else
                <span class="badge badge-secondary status-badge-{{ $reason->id }}">غیرفعال</span>
            @endif
        </td>
        <td>{{ $reason->returnRequests()->count() }}</td>
        <td>
            <button type="button"
                    class="btn btn-sm btn-outline-{{ $reason->is_active ? 'success' : 'secondary' }} toggle-reason"
                    data-id="{{ $reason->id }}"
                    data-url="{{ route('admin.returns.reasons.toggle', $reason) }}">
                <i class="fas fa-{{ $reason->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-danger delete-reason"
                    data-id="{{ $reason->id }}"
                    data-url="{{ route('admin.returns.reasons.destroy', $reason) }}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
@endforeach
