@if($sliders->count())
    <section class="card sliders-sortable">
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
                                @if($title=="استوری و هایلایت ها")
                                    <th>تصویر کاور</th>
                                @else
                                    <th>عنوان</th>
                                @endif

                                <th class="text-center">وضعیت</th>
                                <th class="text-center" style='width: 150px'>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="sliders-sortable-{{ $loop->index }}">
                            @foreach($sliders as $slider)
                                <tr id="slider-{{ $slider->id }}">
                                    <td class="text-center draggable-handler">
                                        <div class="fonticon-wrap"><i class="feather icon-move"></i></div>
                                    </td>
                                    <td>

                                        <div class="slider-thumb">
                                            <?php
                                                $pathInfo = pathinfo($slider->image);
                                                ?>
                                            @if($pathInfo['extension']=="mp4" or $pathInfo['extension']=="gif" or $pathInfo['extension']=="m4a")
                                                <video controls class='w-100' style='max-height: 150px'>
                                                    <source src="{{asset($slider->image)}}" type="video/mp4">
                                                </video>
                                            @else
                                                <img src="{{ asset($slider->image) }}" alt="avtar img holder">
                                            @endif


                                        </div>
                                    </td>
                                    @if($title=="استوری و هایلایت ها")
                                        <td>
                                            @if($slider->title)
                                                <img style='max-width: 100px' src="{{ asset($slider->title) }}" alt="avtar img holder">
                                            @endif

                                        </td>
                                    @else
                                        <td>{{ $slider->title ?: '--' }}</td>
                                    @endif

                                    <td class="text-center">
                                        @if($slider->published)
                                            <div class="badge  badge-success">منتشر شده</div>
                                        @else
                                            <div class="badge badge-danger">پیش نویس</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown dropdown-action">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $slider->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $slider->id }}">
                                                @can('sliders.update')
                                                    <a class="dropdown-item" href="{{ route('admin.sliders.edit', ['slider' => $slider]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                    <div class="dropdown-divider"></div>
                                                @endcan
                                                @can('sliders.delete')
                                                    <button class="dropdown-item btn-delete" data-slider="{{ $slider->id }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
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
