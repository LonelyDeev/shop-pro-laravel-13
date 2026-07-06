@php
    $input_value = isset($widget) && $widget ? $widget->option($option['key']) : '';
    $file_id = 'option-file-' . $option['key'];
@endphp

<div class="{{ $option['class'] ?? 'col-md-6 col-12' }}">
    <fieldset class="form-group widget-field-file">
        <label class="field-label" for="{{ $file_id }}">{{ $option['title'] }}</label>

        <div class="custom-file widget-file-drop">
            <input id="{{ $file_id }}" type="file" name="options[{{ $option['key'] }}]" class="custom-file-input" {!! $option['attributes'] ?? '' !!}>
            <label class="custom-file-label" for="{{ $file_id }}">
                <span class="file-icon"><i class="feather icon-upload"></i></span>
                <span class="file-text">{{ $input_value ?: 'برای انتخاب فایل کلیک کنید یا بکشید و رها کنید' }}</span>
            </label>
        </div>

        @isset($option['help'])
            <p class="field-help">{{ $option['help'] }}</p>
        @endisset
    </fieldset>
</div>
