@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/sliders.css')}}">
@endpush
@section('content')

    <div class="container-fluid py-4">

        {{-- هدر --}}
        <div class="sk-page-header d-flex align-items-center justify-content-between">
            <div>
                <h3>
                    <span class="icon-wrap"><i class="fa fa-pen-to-square"></i></span>
                    ویرایش اسلایدر
                </h3>
                <p>اسلایدر را در چند صفحه و چند گروه به‌صورت همزمان نمایش دهید.</p>
            </div>
            <a href="{{ route('admin.sliders.index') }}" class="btn btn-light">
                <i class="fa fa-arrow-right ms-1"></i> بازگشت به لیست
            </a>
        </div>

        <form id="slider-edit-form"
              method="POST"
              action="{{ route('admin.sliders.update', $slider) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div id="main-card">

                {{-- دراپ‌زون --}}
                @include('back.sliders._image_uploader', [
                    'currentImage' => old('image', $slider->image),
                    'required'     => true,
                ])

                <div class="row g-4">
                    {{-- چک‌باکس‌ها --}}
                    <div class="col-lg-7">

                        {{-- بخش صفحات (هاردکد شده با ۳ صفحه ثابت) --}}
                        @include('back.sliders._pages_section', [
                            'selected' => old('pages', $slider->pages ?: []),
                        ])

                        @include('back.sliders._checkbox_grid', [
                            'name'    => 'groups',
                            'options' => $groups,
                            'selected'=> old('groups', $slider->groups ?: []),
                            'title'   => 'گروه‌های نمایش',
                            'icon'    => 'fa-layer-group',
                            'variant' => 'groups',
                        ])

                    </div>

                    {{-- اطلاعات --}}
                    <div class="col-lg-5">
                        <div class="sk-info-card">
                            <div class="sk-info-card-header">
                                <i class="fa fa-circle-info"></i>
                                <h5>اطلاعات اسلایدر</h5>
                            </div>
                            <div class="sk-info-card-body">

                                <div class="mb-3">
                                    <label class="form-label">عنوان</label>
                                    <input type="text" name="title"
                                           value="{{ old('title', $slider->title) }}"
                                           class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">عنوان متحرک</label>
                                    <input type="text" name="motionTitle"
                                           value="{{ old('motionTitle', $slider->motionTitle) }}"
                                           class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">لینک</label>
                                    <input type="text" name="link"
                                           value="{{ old('link', $slider->link) }}"
                                           class="form-control slider-link">
                                    <small class="text-muted">با تایپ کردن، صفحات داخلی پیشنهاد داده می‌شوند.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">توضیحات</label>
                                    <textarea name="description" rows="4"
                                              class="form-control">{{ old('description', $slider->description) }}</textarea>
                                </div>

                                <div class="publish-box">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               name="published" value="1" id="published"
                                               {{ old('published', $slider->published) ? 'checked' : '' }}>
                                    </div>
                                    <div class="publish-info">
                                        <strong>انتشار اسلایدر</strong>
                                        <small>اگر فعال باشد، اسلایدر در سایت نمایش داده می‌شود.</small>
                                    </div>
                                </div>

                            </div>
                            <div class="sk-info-card-footer">
                                <a href="{{ route('admin.sliders.index') }}" class="btn btn-light">
                                    انصراف
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa fa-save ms-1"></i> به‌روزرسانی
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>


@endsection
@push('scripts')
    <script>
      window.BASE_URL = "{{ url('/') }}";
      window.pages = @json(array_keys(\App\Models\Slider::availablePages()));
    </script>
    <script src="{{ asset('back/assets/js/pages/sliders/all.js') }}?v=2"></script>
@endpush
