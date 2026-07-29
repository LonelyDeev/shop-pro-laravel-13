@extends('back.layouts.master')

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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">مدیریت وبلاگ</li>
                                    <li class="breadcrumb-item active">ایجاد نوشته</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد نوشته جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form class="form" id="post-create-form" action="{{ route('admin.posts.store') }}"
                                  data-redirect="{{ route('admin.posts.index') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="nav-vertical">
                                    <div class="nav nav-tabs flex-column nav-left">
                                        <ul class="nav nav-tabs flex-column nav-vertical-right" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="baseVerticalLeft-tab1" data-toggle="tab"
                                                   aria-controls="tabVerticalLeft1" href="#tabVerticalLeft1" role="tab">
                                                    <i class="fas fa-clipboard-list"></i> اطلاعات کلی
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="productMetaTab" data-toggle="tab"
                                                   aria-controls="tabProductMeta" href="#tabProductMeta" role="tab">
                                                    <i class="fab fa-squarespace"></i> تنظیمات سئو
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="productMediaTab" data-toggle="tab"
                                                   aria-controls="tabProductMedia" href="#tabProductMedia" role="tab">
                                                    <i class="fas fa-image"></i> تصاویر و مدیا
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="nav-vertical-right mt-2">
                                            <div class="col-12">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="is_editor_pick" value="1">
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>انتخاب سردبیر؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-12">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="allow_comments" value="1" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>امکان ارسال دیدگاه؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-12">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="published" value="1" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>انتشار نوشته؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">
                                                    ایجاد نوشته
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tabVerticalLeft1" role="tabpanel">
                                            <div class="col-12 col-md-10 offset-md-1">
                                                <div class="form-body">
                                                    <!-- نوع مقاله -->
                                                    <div class="row">




                                                       {{-- <div class="col-md-6 col-12">
                                                            <div class="form-group">
                                                                <label>ایجاد نوشته توسط:</label>
                                                                <select name="created_by" id="created_by" class="form-control">
                                                                    <option value="admin">خودم</option>
                                                                    @can('posts.createAi')
                                                                        @if(option('AI_TOKEN_KEY'))
                                                                            <option value="ai">هوش مصنویی</option>
                                                                            <option value="ai-pro">هوش مصنویی پیشرفته</option>
                                                                        @else
                                                                            <option disabled>برای استفاده از هوش مصنویی ابتدا توکن را وارد کنید</option>
                                                                        @endif
                                                                    @endcan
                                                                </select>
                                                            </div>
                                                        </div>--}}
                                                    </div>

                                                    <!-- ادامه کدهای قبلی شما -->
                                                    <div class="row">
                                                        <div class="col-md-6 col-12">
                                                            <div class="form-group">
                                                                <label>عنوان</label>
                                                                <input type="text" class="form-control" name="title">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>url</label>
                                                                <input id="slug" type="text" class="form-control" name="slug">
                                                                <p>
                                                                    <small>
                                                                        <a id="generate-post-slug">ایجاد خودکار</a>
                                                                        <span id="slug-spinner" class="spinner-grow spinner-grow-sm text-success" role="status" style="display: none;"></span>
                                                                    </small>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>دسته بندی ها</label>
                                                                <select class="form-control product-categories" name="categories[]" multiple>
                                                                    @foreach ($categories as $category)
                                                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>تاریخ انتشار</label>
                                                                <input autocomplete="off" type="text" class="form-control" id="publish_date_picker">
                                                                <input type="hidden" name="publish_date" id="publish_date">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>خلاصه</label>
                                                                <textarea class="form-control" rows="3" name="summary"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>محتوا</label>
                                                                <textarea id="content" class="form-control" rows="3" name="content"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- فیلدهای سفارشی قبلی شما -->
                                                    @if (count($filds))
                                                        <div class="row">
                                                            @foreach ($filds as $fild)
                                                                <div class="col-md-6 col-12">
                                                                    <div class="form-group">
                                                                        <label>{{ $fild->title }}</label>
                                                                        @php
                                                                            $isRequired = $fild->required ? 'required' : '';
                                                                        @endphp
                                                                        @if ($fild->type == 'input')
                                                                            <input type="text" class="form-control" name="filds[{{ $fild->id }}]" value="{{ old('filds.' . $fild->id, $fild->value) }}" {{ $isRequired }}>
                                                                        @elseif ($fild->type == 'textarea')
                                                                            <textarea class="form-control" name="filds[{{ $fild->id }}]" {{ $isRequired }}>{{ old('filds.' . $fild->id, $fild->value) }}</textarea>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- تب سئو (ادامه کدهای قبلی) -->
                                        <div class="tab-pane" id="tabProductMeta" role="tabpanel">
                                            <div class="col-12">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>عنوان سئو</label>
                                                                <input type="text" class="form-control" name="meta_title" value="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>توضیحات سئو</label>
                                                                <textarea class="form-control" name="meta_description" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>کلمات کلیدی (تگ‌ها)</label>
                                                                <input id="tags" type="text" name="tags" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- تب مدیا -->
                                        <div class="tab-pane" id="tabProductMedia" role="tabpanel">
                                            <div class="col-md-12 col-12">
                                                <div class="form-group">
                                                    <label>نوع مقاله</label>
                                                    <select name="post_type" id="post_type" class="form-control">
                                                        <option value="text">📝 متنی</option>
                                                        <option value="video">🎥 ویدیویی</option>
                                                        <option value="podcast">🎙️ پادکست</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- فیلد ویدیو (پیش‌فرض مخفی) -->
                                            <div class="col-md-12 col-12" id="video_url_field" style="display: none;">
                                                <div class="form-group">
                                                    <label>لینک ویدیو</label>
                                                    <input type="url" class="form-control" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                                                    <small class="text-muted">از قسمت <a target="_blank" href="{{route('admin.file-manager')}}">فایلها</a> میتوانید فایل خود را بارگذاری کرده و لینک آن را کپی کنید و در اینجا قرار دهید.</small>
                                                </div>
                                            </div>

                                            <!-- فیلد پادکست (پیش‌فرض مخفی) -->
                                            <div class="col-md-12 col-12" id="podcast_url_field" style="display: none;">
                                                <div class="form-group">
                                                    <label>لینک پادکست</label>
                                                    <input type="url" class="form-control" name="podcast_url" placeholder="https://example.com/podcast.mp3">
                                                    <small class="text-muted">از قسمت <a target="_blank" href="{{route('admin.file-manager')}}">فایلها</a> میتوانید فایل خود را بارگذاری کرده و لینک آن را کپی کنید و در اینجا قرار دهید.</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group">
                                                    <label>تصویر شاخص</label>
                                                    <div class="custom-file">
                                                        <input id="image" type="file" accept="image/*" name="image" class="custom-file-input">
                                                        <label class="custom-file-label" for="image">انتخاب فایل</label>
                                                        <p><small>بهترین اندازه <span class="text-danger">{{ config('front.imageSizes.postImage') }}</span> پیکسل میباشد.</small></p>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/posts/all.js') }}?v=2"></script>
    <script src="{{ asset('back/assets/js/pages/posts/create.js') }}?v=3"></script>

@endpush
