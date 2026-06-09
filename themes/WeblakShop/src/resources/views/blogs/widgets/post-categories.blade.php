
@php
    $variables    = get_widget($widget);
    $categories   = $variables['categories'];
@endphp

    <!-- Start Category-Section -->
@if ($categories->count())

    <div class="col-lg-12 col-md-12 col-xs-12 pull-right mt-3">
        <div class="row">
            <div class="col-12">
                <div class="widget widget-product card widget-posts-category">
                    @if($widget->option('title'))
                        <div
                            class="blog-article-image-box--header d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="title">{{$widget->option('title')}}</div>
                                @if($widget->option('link_title') and $widget->option('link'))
                                    <a class="more text-white bg-secondary badge shadow-secondary el-center fs-9"
                                       href="{{$widget->option('link')}}">{{$widget->option('link_title')}} <i
                                            class="fa fa-angle-left mr-2"></i>
                                    </a>
                                @endif

                            </div>
                        </div>
                    @endif

                    <div class="posts-category-carousel owl-carousel owl-theme owl-rtl owl-loaded owl-drag posts-category-carousel">
                        <div class="owl-stage-outer">
                            <div class="owl-stage d-flex">
                                @foreach ($categories as $category)

                                    <div class="owl-item {{$loop->index<=10 ? 'active' : ''}} ">
                                        <a href="{{route('front.articles.index').'?cat='.$category->slug}}" class="image-data-src promotion-category" style="background-image: url('{{ $category->image ? asset($category->image) : asset('/no-image-product.svg') }}')">
                                            <div class="promotion-category-name">{{ $category->title }}</div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
<!-- End Category-Section -->
