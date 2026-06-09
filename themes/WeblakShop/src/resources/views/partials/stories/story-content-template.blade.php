@php
    $storyDuration = 10000;
    if($story->type != 'video' && !empty($story->duration)) {
        $storyDuration = $story->duration * 1000;
    } elseif($story->type == 'video') {
        $storyDuration = 0;
    }

    $product = null;
    if($story->product_id && $story->product) {
        $productItem = $story->product;
        $color = $productItem->getPrices()->first()->get_attributes()->first();
        $product = [
            'slug' => $productItem->slug,
            'title' => $productItem->title,
            'price' => number_format($productItem->getPrices()->first()->discount_price) . ' تومان ',
            'discount' => $productItem->getPrices()->first()->discount ? $productItem->getPrices()->first()->discount . '%' : '',
            'image' => asset($productItem->image),
            'color' => [
                'name' => $color->name,
                'value' => $color->value,
            ]
        ];
    }
@endphp

<div class="story-item"
     data-story-id="{{ $story->id }}"
     data-duration="{{ $storyDuration }}"
     data-type="{{ $story->type }}">

    {{-- هدر استوری --}}
    <div class="story-header">
        <div class="story-progress-bars"></div>
        <div class="story-counter">
            <span class="story-current-index">1</span> / <span class="story-total-count">1</span>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                @if($story->cover_image)
                    <img src="{{ asset($story->cover_image) }}" alt="avatar">
                @else
                    <i class="fa fa-store"></i>
                @endif
            </div>
            <div class="user-details">
                <div class="user-name">{{ $story->title }}</div>
                <div class="story-time">{{ $story->published_at ? $story->published_at->diffForHumans() : 'چند لحظه پیش' }}</div>
            </div>
        </div>
        <button class="close-story">
            <i class="fa fa-times"></i>
        </button>
    </div>

    {{-- دکمه‌های قبلی و بعدی --}}
    <button class="story-nav story-nav-prev">
        <i class="fa fa-chevron-right"></i>
    </button>
    <button class="story-nav story-nav-next">
        <i class="fa fa-chevron-left"></i>
    </button>

    {{-- محتوای استوری --}}
    <div class="story-content {{ $story->product_id ? 'story-content-with-video' : '' }}">
        @if($story->type == "video")
            <video id="video-{{ $story->id }}"
                   autoplay
                   class="w-100 story-video-player">
                <source src="{{ $story->video }}" type="video/mp4">
            </video>
            <div class="story-video-sound" data-story-id="{{ $story->id }}">
                <i class="fa-solid fa-volume-up"></i>
            </div>
        @else
            <img class="d-block w-100"
                 src="{{ asset($story->image) }}"
                 alt="{{ $story->title ?: $story->image }}">
        @endif
    </div>

    {{-- فوتر استوری --}}
    <div class="story-footer">
        <ul class="story-likes-comments" style="{{ $story->product_id ? 'bottom: 90%' : 'bottom: 0' }}">
            @if($story->active_likes)
                <li class="story-likes {{ $story->isLikedByUser() ? 'liked' : '' }}"
                    data-story-id="{{ $story->id }}"
                    data-action="{{ route('front.story.like') }}">
                    <i class="fa fa-heart"></i>
                    <span>{{ $story->likes()->count()}}</span>
                </li>
            @endif
            @if($story->active_comments)
                <li class="story-comments" data-story-id="{{$story->id}}" data-action="{{route('front.story.comments',$story)}}">
                    <i class="fa fa-comment"></i>
                    <span>{{$commentsCount ?? 0}}</span>
                </li>
            @endif
        </ul>

        @if($story->widget_title && $story->widget_link)
            <a href="{{route('front.story.widget.redirect',$story)}}" style="{{$story->product_id ? 'bottom: 90px' : ''}}"
               target="_blank"
               class="story-widget-area {{$story->product_id ? 'position-absolute' : ''}}"
               data-story-id="{{ $story->id }}"
               data-interaction-type="widget_click">
                <span>{{ $story->widget_title }}</span>
                <i class="fa-solid fa-link"></i>
            </a>
        @endif

        @if($story->product_id && $product)
            <div class="story-product card">
                <a href="{{route('front.story.product.redirect',['story'=>$story,'product'=>$product['slug']])}}"
                   target="_blank"
                   class="card-body d-flex align-items-center"
                   data-story-id="{{ $story->id }}"
                   data-interaction-type="product_click">
                    <div class="image">
                        <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}">
                    </div>
                    <div class="meta d-flex align-items-center flex-column">
                        <div class="title clickable lts-05" title="{{ $product['title'] }}">
                            {{ $product['title'] }}
                        </div>
                        @if($product['discount'] != "")
                            <div class="discount-percent shadow-secondary shadow-1 me-2">
                                {{ $product['discount'] }}
                            </div>
                        @endif
                        <div class="w-100 d-flex align-items-center justify-content-between">
                            <ul class="product-colors">
                                <li style="background-color: {{ $product['color']['value'] }};"
                                    title="{{ $product['color']['name'] }}"></li>
                            </ul>
                            <div class="d-inline-flex align-items-center">
                                <div class="d-flex flex-column justify-content-end">
                                    <span class="product-price-now fw-bold lts-05">{{ $product['price'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>
</div>



