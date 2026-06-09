{{--
    Reusable Image Uploader Partial
    Variables:
        $label   - Field label (string)
        $name    - Input name (string)
        $inputId - Hidden input ID (string)
        $btnId   - Uploader div ID (string)
        $value   - Current image URL (string|null)
        $hint    - Size hint text (string)
--}}

<div class="col-12 col-md-12 image-uploader-wrap">
    <label class="image-uploader-label">{{$label}}</label>
    <fieldset class="form-group image-uploader-card">


        <input type="text" id="{{$inputId}}"
               class="form-control display-hidden" name="{{$name}}"
               aria-label="Image" aria-describedby="{{$btnId}}" value="{{ $value }}">

        <span class="remove-img-uploader @if(!$value)display-hidden @endif">
                                                            <i class="fa fa-trash text-danger px-1"></i>
                                                    </span>

        <div class="file-uploader dropzone dropzone-area ui-sortable dz-clickable"
             id="{{$btnId}}">
            <div class="img-uploader @if(!$value)display-hidden @endif" style="text-align: center">
                <img src="{{ $value }}">
            </div>
            <div class="dz-message @if($value)display-hidden @endif uploader-placeholder">
                <i class="fa fa-image"></i>
                <span class="up-text">کلیک کنید</span>
                <span class="up-ext">PNG, JPG, SVG</span>
            </div>
        </div>

    </fieldset>
    <span class="img-hint"><i class="fas fa-info-circle" style="color: rgb(165, 180, 252); font-size: 0.65rem;"></i>{{$hint}}</span>
</div>
