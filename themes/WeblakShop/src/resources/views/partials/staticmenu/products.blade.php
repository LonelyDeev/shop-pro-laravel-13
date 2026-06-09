<li class="item-list-menu megamenu-1 category nav-overlay">
    <a href="{{ route('front.products.index') }}" class="list-category first after" style="font-weight: bold;"><i class="{{$menu->icon}}"></i>
        {{ $menu->title }}</a>

    @if(count($productcats))
        <ul class="list-menu-level-2">
            @foreach ($productcats as $category)
            <li class="item-menu-2">
                <a href="{{ $category->link }}" class="list-category-menu-2"><i class="{{$category->icon}}"></i>{{ $category->title }}</a>
                @if ($category->getCategoriesCount())
                <ul class="megamenu-level-3 row">

                    <li class="list-category">
                        <a href="{{ $category->link }}" class="list-category-megamenu">همه دسته بندی های {{ $category->title }}</a>
                    </li>
                        @foreach ($category->getCategories() as $childCategory)
                        @include('front::partials.megamenu.child-category', ['category' => $childCategory])
                        @endforeach

                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    @endif


</li>

