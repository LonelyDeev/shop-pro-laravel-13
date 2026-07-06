@php
    $select_id = 'option-select-' . $option['key'];
@endphp

<div class="{{ $option['class'] ?? 'col-md-6 col-12' }}">
    <div class="form-group widget-field-select">
        <label class="field-label" for="{{ $select_id }}">{{ $option['title'] }}</label>

        <select id="{{ $select_id }}" class="styled-select" name="options[{{ $option['key'] }}]" {!! $option['attributes'] ?? '' !!}>
            @foreach ($option['options'] as $item)
                @php
                    $selected = isset($widget) && $widget->option($option['key']) == $item['value'];
                @endphp

                <option value="{{ $item['value'] }}" {{ $selected ? 'selected' : '' }}>{{ $item['title'] }}</option>
            @endforeach
        </select>

        @isset($option['help'])
            <p class="field-help">{{ $option['help'] }}</p>
        @endisset
    </div>
</div>
