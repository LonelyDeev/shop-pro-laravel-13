<li class="dd-item" data-id="{{ $child_category->id }}">
    @if($child_category->categories->isEmpty())
        <div class="dd-handle"><span class="category-title">{{  $child_category->title  }}</span>
            <a data-category="{{ $child_category->slug }}" class="float-right delete-category dd-nodrag" href="javascript:void(0)"><i class="fa fa-trash text-danger px-1"></i>حذف</a>
            <a data-category="{{ $child_category->slug }}" class="float-right edit-category dd-nodrag text-info" href="javascript:void(0)"><i class="fa fa-pencil text-info px-1"></i>ویرایش</a>
        </div>
    @else
        <div class="dd-handle"><span class="category-title">{{ $child_category->title }}</span>
            <a data-category="{{ $child_category->slug }}" class="float-right delete-category dd-nodrag" href="javascript:void(0)"><i class="fa fa-trash text-danger px-1"></i>حذف</a>
            <a data-category="{{ $child_category->slug }}" class="float-right edit-category dd-nodrag text-info" href="javascript:void(0)"><i class="fa fa-pencil text-info px-1"></i>ویرایش</a>
        </div>
        <ol class="dd-list">
            @foreach ($child_category->childrenCategories as $childCategory)
                @include('back.partials.child_category', ['child_category' => $childCategory])
            @endforeach
        </ol>
    @endif
</li>
