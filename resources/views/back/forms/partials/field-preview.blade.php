@foreach($fields as $index => $field)
    <li class="preview-field dd-item" data-id="{{ $field['id'] }}" data-order="{{ $index }}">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center">

                    <strong class="mr-2 ml-2" data-title="title">{{ $field['label'] }}</strong>

                    <span class="badge badge-secondary" data-title="{{$field['type']}}">{{ $typeNames[$field['type']] ?? $field['type'] }}</span>
                    @if($field['required'] ?? false)
                        <span class="badge badge-danger mr-1 ml-2" data-title="required">ضروری</span>
                    @endif
                </div>
                <div class="mt-2">
                    <small class="text-muted" data-title="name">نام: <code>{{ $field['name'] }}</code></small>
                    @if(!empty($field['placeholder']))
                        <small class="text-muted mr-2" data-title="help_text">| متن نمایشی: {{ $field['placeholder'] }}</small>
                    @endif
                    @if(!empty($field['help_text']))
                        <small class="text-muted mr-2" data-title="help_text">| راهنما: {{ $field['help_text'] }}</small>
                    @endif
                    @if(!empty($field['validation']) || !empty($field['rules_validation']))
                        <small class="text-muted mr-2" data-title="rules_validation">| اعتبارسنجی: {{ $field['validation'] ?? $field['rules_validation'] }}</small>
                    @endif
                    @if(!empty($field['default_value']))
                        <small class="text-muted mr-2" data-title="default_value">| مقدار پیش‌فرض: {{ $field['default_value'] }}</small>
                    @endif
                    @if(!empty($field['class']))
                        <small class="text-muted mr-2" data-title="class">| کلاس: {{ $field['class'] }}</small>
                    @endif
                </div>
                @if(!empty($field['options']) && is_array($field['options']))
                    <div class="mt-1">
                        <small>گزینه‌ها: </small>
                        @foreach($field['options'] as $option)
                            <span class="badge badge-light">{{ $option }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-field" data-id="{{ $field['id'] }}">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </li>
@endforeach

@if(empty($fields))
    <div class="text-center text-muted p-5" id="empty-fields-msg">
        <i class="fa fa-arrow-right fa-2x"></i>
        <p class="mt-2">از بخش سمت راست فیلدها را اضافه کنید</p>
    </div>
@endif
