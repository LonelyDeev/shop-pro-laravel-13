@if($banners->count())
    <section class="card banners-sortable">
        <div class="card-header">
            <h4 class="card-title">{{ $title }}</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">ردیف</th>
                                <th>تصویر</th>
                                <th>عنوان</th>
                                <th class="text-center">وضعیت</th>
                                <th class="text-center" style='width: 150px'>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="banners-sortable-{{ $loop->index }}">
                            @foreach($banners as $banner)
                                <tr id="banner-{{ $banner->id }}">
                                    <td class="text-center draggable-handler">
                                        <div class="fonticon-wrap"><i class="feather icon-move"></i></div>
                                    </td>
                                    <td>
                                        <div class="slider-thumb">
                                            <img src="{{ asset($banner->image) }}" alt="banner image">
                                        </div>
                                    </td>
                                    <td>{{ $banner->title ?: '--' }}</td>
                                    <td class="text-center">
                                        @if($banner->published)
                                            <div class="badge badge-success">منتشر شده</div>
                                        @else
                                            <div class="badge badge-danger">پیش نویس</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown dropdown-action">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $banner->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $banner->id }}">
                                                @can('banners.update')
                                                    <a class="dropdown-item" href="{{ route('admin.banners.edit', ['banner' => $banner]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                    <div class="dropdown-divider"></div>
                                                @endcan
                                                @can('banners.delete')
                                                    <button class="dropdown-item btn-delete" data-banner="{{ $banner->id }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                @endcan
                                            </div>
                                        </div>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@else
  {{--  <section class="card">
        <div class="card-header">
            <h4 class="card-title">{{ $title }}</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <p>چیزی برای نمایش وجود ندارد!</p>
                </div>
            </div>
        </div>
    </section>--}}
@endif
