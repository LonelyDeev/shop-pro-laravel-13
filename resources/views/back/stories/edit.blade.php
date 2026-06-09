@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/story.css') }}">
@endpush
@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت استوری ها
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد استوری
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد استوری جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="row g-4">

                                <div class="col-lg-7">
                                    <form id="update-story" method="post" action="{{route('admin.stories.update',$story)}}" data-redirect="{{ route('admin.stories.index') }}" class=" p-2">
                                        <input type="hidden" name="_method" value="PUT">
                                        <!-- عنوان -->
                                        <div class="form-group">
                                            <label class="form-label fw-semibold">عنوان</label>
                                            <input name="title" type="text" class="form-control" id="storyTitle"
                                                   placeholder="مثال: پیشنهاد شگفت‌انگیز" value="{{$story->title}}">
                                        </div>

                                        <!-- تصویر کاور (ثابت - برای نمایش روی جلد) -->
                                        <div class="form-group">
                                            <label class="form-label fw-semibold required-star">تصویر کاور
                                                </label>
                                            <div class="upload-box d-flex align-items-center justify-content-between"
                                                 id="uploadBoxCover">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-card-image fs-4 text-secondary"></i>
                                                    <span id="coverFileName" class="text-muted small" data-value="{{$story['cover_image']}}">{{$story['cover_image']}}</span>
                                                </div>
                                                <span class="badge bg-light text-dark">انتخاب +</span>
                                            </div>
                                            <input type="file" name="cover_image" id="coverUpload" value="{{$story['cover_image']}}" accept="image/*" class="d-none">
                                        </div>

                                        <!-- نوع استوری -->
                                        <div class="form-group">
                                            <label class="form-label fw-semibold required-star">نوع استوری</label>
                                            <select name="type" id="storyType" class="form-control">
                                                <option {{$story->type=="image" ? "selected" : ""}} value="image" >تصویری</option>
                                                <option {{$story->type=="video" ? "selected" : ""}} value="video">ویدیویی</option>
                                            </select>
                                        </div>

                                        <!-- محتوای استوری (تصویر یا ویدیو) -->
                                        <div class="form-group" id="imageContentContainer">
                                            <label class="form-label fw-semibold required-star">تصویر محتوای
                                                استوری</label>
                                            <div class="upload-box d-flex align-items-center justify-content-between"
                                                 id="uploadBoxContent">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-image fs-4 text-secondary"></i>
                                                    <span id="contentFileName" class="text-muted small" data-value="{{$story['image']}}">{{$story['image']}}</span>
                                                </div>
                                                <span class="badge bg-light text-dark">انتخاب +</span>
                                            </div>
                                            <input type="file" name="image" id="contentImageUpload" value="{{$story['image'] ? $story['image'] : ''}}" accept="image/*" class="d-none">

                                            <div class="form-group mt-1">
                                                <label class="form-label fw-semibold">مدت زمان (ثانیه)
                                                    <small>پیشفرض 5 ثانیه</small>
                                                </label>
                                                <input name="duration" type="number" value="{{$story->duration==0 ? '5' : $story->duration}}" class="form-control" id="storyDuration"
                                                       placeholder="مثال: 10">
                                            </div>
                                        </div>

                                        <div class="form-group" id="videoContentContainer" style="display: none;">
                                            <label class="form-label fw-semibold required-star">آدرس ویدیو</label>
                                            <input name="video" type="text" class="form-control" id="videoUrlInput"
                                                   placeholder="https://domain.com/video.mp4" value="{{$story->video}}">
                                            <div class="form-text">لینک مستقیم ویدیو (MP4)</div>
                                        </div>

                                        <hr>
                                        <!-- تاریخ انقضا -->

                                        <div class="form-group">
                                            <label class="required-star">تاریخ انقضا</label>
                                            <input autocomplete="off" type="text" class="form-control" id="expiry_date_picker" value="{{ $story->expiry_date ? jdate($story->expiry_date)->getTimestamp() : '' }}">
                                            <input type="hidden" name="expiry_date" id="expiryDate" value="{{ $story->expiry_date ? jdate($story->expiry_date) : '' }}">
                                        </div>


                                        <!-- ویجت -->
                                        <label class="form-label fw-semibold">ویجت (اختیاری)</label>

                                        <div class=" p-1 rounded-3 story-widget">
                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="form-label small">عنوان </label>
                                                        <input name="widget_title" type="text" class="form-control" id="widgetTitle" value="{{$story->widget_title}}"
                                                               placeholder="مثال: تخفیف یلدایی">
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="form-label small">لینک </label>
                                                        <input name="widget_link" type="text" class="form-control" id="widgetLink" value="{{$story->widget_link}}"
                                                               placeholder="https://domain.com/example">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <!-- محصول -->
                                        <div class=" p-1 mt-1 rounded-3 story-widget get-product">
                                            <label class="form-label fw-semibold"> افزودن محصول (شناسه
                                                محصول)</label>
                                            <div class="form-group d-flex">

                                                <input name="product_id" type="text" class="form-control" id="productId"
                                                       placeholder="مثال: 1234567" value="{{$story->product_id}}">
                                                <button class="btn btn-dark ms-1 btn-square ml-1" id="searchProductId"
                                                        data-action="{{route('admin.stories.get-product')}}"
                                                        type="button"><i class="fa fa-search"></i></button>

                                            </div>
                                        </div>

                                        <div class="col-md-8 mb-3 mt-2 d-flex justify-content-between">
                                            <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" name="active_likes" value="1" {{ $story->active_likes ? 'checked' : '' }}>
                                                    <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                    <span> فعال بودن لایک؟</span>
                                                </div>
                                            </fieldset>

                                            <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" name="active_comments" value="1" {{ $story->active_comments ? 'checked' : '' }}>
                                                    <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                    <span>فعال بودن نظرات ؟</span>
                                                </div>
                                            </fieldset>
                                        </div>


                                        <div class="d-flex gap-2 d-flex justify-content-between mt-2">
                                            <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light"> ویرایش استوری</button>

                                            <button type="button" class="btn btn-outline-secondary mr-1 mb-1 waves-effect waves-light" id="resetFormBtn">
                                                بازنشانی
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- ستون چپ: پیش‌نمایش -->
                                <div class="col-lg-5 order-lg-1 order-2 d-flex flex-column align-items-center">
                                    <div class="card shadow-sm border-0 rounded-4 w-100">

                                        <div
                                            class="card-body d-flex flex-column align-items-center justify-content-center bg-opacity-10"
                                            style="min-height: 550px;">
                                            <div class="story-preview-card">
                                                <!-- محتوای اصلی استوری (ویدیو یا تصویر) -->
                                                <img id="storyContentImage" class="story-media-layer" src="{{$story->image ? asset($story->image) : ''}}" alt="{{$story->title}}">
                                                <video id="storyVideo" class="story-media-layer" style="display: none;"
                                                       muted loop playsinline></video>

                                                <ul class="story-likes-comments" style="bottom: 12%;">
                                                    <li class="story-likes"><i class="fa fa-heart"></i><span>{{$story->likes_count}}</span></li>
                                                    <li class="story-comments"><i class="fa fa-comment"></i><span>0</span></li>

                                                </ul>


                                                <!-- ویجت -->
                                                <div id="liveWidget" class="story-widget-area" style="display: none;">
                                                    <span id="liveWidgetTitle"> </span>
                                                    <i class="fa-solid fa-link"></i>
                                                </div>

                                                @if(count($product))
                                                    <div class="story-product card ">
                                                        <div class="card-body d-flex align-items-center">
                                                            <div class="image"><img src="{{$product['image'] ? asset($product['image']) : ''}}" alt="{{$product['title']}}">
                                                            </div>
                                                            <div class="meta d-flex align-items-center flex-column">
                                                                <div class="title clickable lts-05" title="{{$product['title']}}">{{$product['title']}}</div>
                                                                <div class="discount-percent shadow-secondary shadow-1 me-2 {{$product['discount'] ? '' : 'hidden'}}">
                                                                    {{$product['discount']}}</div>
                                                                <div
                                                                    class="w-100 d-flex align-items-center justify-content-between">
                                                                    <ul class="product-colors">
                                                                        <li class="" style="background-color: {{$product['color'] ? $product['color']['value'] : ''}}" data-pd-tooltip="true" title="{{$product['color'] ? $product['color']['name'] : ''}}"></li>
                                                                    </ul>
                                                                    <div class="d-inline-flex align-items-center">
                                                                        {{--                                                                    <span class="product-rating-average fs-9 lh-15"><i--}}
                                                                        {{--                                                                            class="fa fa-star"></i><span--}}
                                                                        {{--                                                                            class="fw-bold">5</span></span>--}}
                                                                        <div class="d-flex flex-column justify-content-end">
                                                                        <span class="product-price-now fw-bold lts-05">
                                                                            <span class="unit unit-sm"></span>{{$product['price']}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="story-product card hidden">
                                                        <div class="card-body d-flex align-items-center">
                                                            <div class="image"><img src="" alt="">
                                                            </div>
                                                            <div class="meta d-flex align-items-center flex-column">
                                                                <div class="title clickable lts-05"></div>
                                                                <div class="discount-percent shadow-secondary shadow-1 me-2 hidden">7%</div>
                                                                <div
                                                                    class="w-100 d-flex align-items-center justify-content-between">
                                                                    <ul class="product-colors">
                                                                        <li class="" data-pd-tooltip="true"></li>
                                                                    </ul>
                                                                    <div class="d-inline-flex align-items-center">
                                                                        {{--                                                                    <span class="product-rating-average fs-9 lh-15"><i--}}
                                                                        {{--                                                                            class="fa fa-star"></i><span--}}
                                                                        {{--                                                                            class="fw-bold">5</span></span>--}}
                                                                        <div class="d-flex flex-column justify-content-end">
                                                                        <span class="product-price-now fw-bold lts-05">
                                                                            <span class="unit unit-sm"></span></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif


                                                <div class="story-header">
                                                    <div class="preview-coverImg" id="previewCoverImg">
                                                        <img id="coverThumb" class="preview-img-thumb" src="{{$story['cover_image'] ? asset($story['cover_image']) : ''}}" alt="{{$story['title']}}">
                                                    </div>
                                                    <!-- عنوان استوری -->
                                                    <div class="story-title-badge" id="liveTitle"></div>

                                                    <div class="expiry-chip">ده دقیقه قبل</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>
@endsection
@include('back.partials.plugins', [
    'plugins' => [
        'dropzone',
        'persian-datepicker',
        'jquery.validate'
    ],
])
@push('scripts')
    <script>
        var editStory=true;
        var existContentImage = @json($story['type'] == "image" && $story['image']);
        var existContentVideo = @json($story['type'] == "video" && $story['video']);
    </script>

    <script src="{{ asset('back/assets/js/pages/stories/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/stories/edit.js') }}"></script>
@endpush
