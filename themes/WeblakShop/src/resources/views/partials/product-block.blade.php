<div class="owl-item @if($i==1 or $i==2 or $i==3 or $i==4)active @endif" style="width: 309.083px; margin-left: 10px;">
    <div class="item">
        <a href="{{ route('front.products.show', ['product' => $product]) }}" class="image-data-src">
            <img class="img-fluid" data-src="{{ $product->image ? asset($product->image) : asset('/no-image-product.svg') }}" src="{{ theme_asset('images/600-600.png') }}" alt="{{ $product->title }}">
        </a>
        <h2 class="post-title">
            <a href="{{ route('front.products.show', ['product' => $product]) }}">
                {{ $product->title }}
            </a>
        </h2>
        <div class="price">
            @if($product->getLowestDiscount())
                <del>
                                                <span>{{ $product->getLowestDiscount() }}
{{--                                                    <span>تومان</span>--}}
                                                </span>
                </del>
            @endif
            <ins>
                                                <span>{{ $product->getLowestPrice() }}
{{--                                                    <span>تومان</span>--}}
                                                </span>
            </ins>
        </div>
    </div>
</div>

