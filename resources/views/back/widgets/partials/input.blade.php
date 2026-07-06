@php
    if (isset($widget)) {
        $input_value = $widget->option($option['key'], $option['default'] ?? '');
    } else {
        $input_value = $option['default'] ?? '';
    }

    $input_id = 'option-input-' . $option['key'];
@endphp

<div class="{{ $option['class'] ?? 'col-md-6 col-12' }}">
    <div class="form-group widget-field-input">
        <label class="field-label" for="{{ $input_id }}">{{ $option['title'] }}</label>
        <input id="{{ $input_id }}"
               type="{{ $option['type'] ?? 'text' }}"
               class="styled-input"
               name="options[{{ $option['key'] }}]"
               value="{{ $input_value }}"
            {!! $option['attributes'] ?? '' !!}>

        @isset($option['help'])
            <p class="field-help">{{ $option['help'] }}</p>
        @endisset
    </div>
</div>
