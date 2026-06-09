@extends('back.layouts.master')
@push('styles')
    <style>
        .story-ring {
            background: linear-gradient(315deg, var(--primary), rgba(var(--primary-rgb), .5));
            position: relative;
            width: 50px;
            min-width: 50px;
            height: 50px;
            overflow: hidden;
            border-radius: 50%;
            padding: 2px;
            border: 1px solid #375ec8;
        }

        .story-ring img {
            width: 100%;
            height: 100%;
            border-radius: 100%;
        }
    </style>

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
                                    <li class="breadcrumb-item active">لیست استوری ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card">
                    <div class="card-header filter-card">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse {{ request()->except('page') ? 'show' : '' }}">
                        <form id="filter-comments-form" method="GET"
                              action="{{route('admin.stories.index')}}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>عنوان</label>
                                    <fieldset class="form-group">
                                        <input class="form-control" name="title" value="{{ request('title') }}">
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>مرتب سازی</label>
                                    <fieldset class="form-group">
                                        <select class="form-control" name="ordering">
                                            <option value="latest" {{ request('ordering') == 'latest' ? 'selected' : '' }}>جدیدترین</option>
                                            <option value="oldest" {{ request('ordering') == 'oldest' ? 'selected' : '' }}>قدیمی ترین</option>
                                            <option value="most_viewed" {{ request('ordering') == 'most_viewed' ? 'selected' : '' }}>پربازدیدترین</option>
                                            <option value="most_liked" {{ request('ordering') == 'most_liked' ? 'selected' : '' }}>پرلایک‌ترین</option>
                                            <option value="most_commented" {{ request('ordering') == 'most_commented' ? 'selected' : '' }}>پرمبحث‌ترین</option>
                                            <option value="most_product_click" {{ request('ordering') == 'most_product_click' ? 'selected' : '' }}>بیشترین کلیک روی محصول</option>
                                            <option value="most_widget_click" {{ request('ordering') == 'most_widget_click' ? 'selected' : '' }}>بیشترین کلیک روی ویجت</option>
                                        </select>
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>تعداد در صفحه</label>
                                    <fieldset class="form-group">
                                        <select class="form-control" name="paginate">
                                            <option value="10" {{ request('paginate') == '10' ? 'selected' : '' }}>10</option>
                                            <option value="20" {{ request('paginate') == '20' ? 'selected' : '' }}>20</option>
                                            <option value="50" {{ request('paginate') == '50' ? 'selected' : '' }}>50</option>
                                            <option value="all" {{ request('paginate') == 'all' ? 'selected' : '' }}>همه</option>
                                        </select>
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>وضعیت انتشار</label>
                                    <fieldset class="form-group">
                                        <select class="form-control" name="status">
                                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>همه</option>
                                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>انتشار شده</option>
                                            <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>منقضی شده</option>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label>بازه زمانی</label>
                                    <fieldset class="form-group">
                                        <select class="form-control" name="date_range">
                                            <option value="all" {{ request('date_range') == 'all' ? 'selected' : '' }}>همه زمان‌ها</option>
                                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>امروز</option>
                                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>دیروز</option>
                                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>این هفته</option>
                                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>این ماه</option>
                                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>ماه قبل</option>
                                        </select>
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>حداقل بازدید</label>
                                    <fieldset class="form-group">
                                        <input type="number" class="form-control" name="min_views" value="{{ request('min_views') }}" placeholder="بازدید بیشتر از">
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>حداقل لایک</label>
                                    <fieldset class="form-group">
                                        <input type="number" class="form-control" name="min_likes" value="{{ request('min_likes') }}" placeholder="لایک بیشتر از">
                                    </fieldset>
                                </div>

                                <div class="col-md-3">
                                    <label>نوع محتوا</label>
                                    <fieldset class="form-group">
                                        <select class="form-control" name="content_type">
                                            <option value="all" {{ request('content_type') == 'all' ? 'selected' : '' }}>همه</option>
                                            <option value="image" {{ request('content_type') == 'image' ? 'selected' : '' }}>تصویری</option>
                                            <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>ویدیویی</option>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-outline-success square mb-1 waves-effect waves-light">
                                            فیلتر کردن
                                        </button>
                                        <a href="{{route('admin.stories.index')}}" class="btn btn-outline-warning square mb-1 waves-effect waves-light">
                                            پاک کردن فیلترها
                                        </a>
                                    </div>
                                </div>


                        </div>
                        </form>
                    </div>
                </div>
                @if($stories->count())

                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست استوری ها</h4>

                        </div>


                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="mb-2 collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3"><span
                                                id="datatable-selected-rows">0</span> مورد انتخاب شده:
                                        </div>
                                        @can('stories.delete')
                                            <button class="btn personal-danger-btn mr-2" type="button"
                                                    data-toggle="modal" data-target="#multiple-delete-modal">حذف همه
                                            </button>
                                        @endcan
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th data-field="id"
                                                class="datatable-cell-center datatable-cell datatable-cell-check">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary checkbox-all ">
                                                        <input type="checkbox">
                                                        <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    </div>
                                                </fieldset>
                                            </th>
                                            <th>عنوان</th>
                                            <th>کاربر</th>
                                            <th class="text-center">وضعیت</th>
                                            <th class="text-center">تاریخ ایجاد</th>
                                            <th class="text-center">تاریخ انقضاء</th>
                                            <th class="text-center">آمار</th>

                                            <th class="text-center" style='width: 150px'>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($stories as $story)

                                            <tr id="post-{{ $story->id }}-tr">

                                                <td data-field="id" aria-label="{{$story->id}}">
                                                    <fieldset class="checkbox">
                                                        <div
                                                            class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" value="{{$story->id}}">
                                                            <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        </div>
                                                    </fieldset>
                                                </td>


                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="image-cover story-ring mr-1">
                                                            <img class="post-thumb"
                                                                 src="{{ $story->cover_image ? asset($story->cover_image) : asset('/empty.svg') }}"
                                                                 alt="image">
                                                        </div>
                                                        <a href="{{route('admin.stories.edit',$story)}}">{{$story->title}}</a>
                                                    </div>

                                                </td>
                                                <td>{{$story->admin_id ? $story->admin->full_name : ''}}</td>
                                                <td class="text-center">
                                                    @if($story->expiry_date < now())
                                                        <div class="badge badge-danger">منقضی شده</div>
                                                    @else
                                                        @if($story->status=="active")
                                                            <div class="badge badge-success">منتشر شده</div>
                                                        @elseif($story->status=="inactive")
                                                            <div class="badge badge-danger">منقضی شده</div>
                                                        @else
                                                            <div class="badge badge-danger">منقضی شده</div>
                                                        @endif

                                                    @endif


                                                </td>

                                                <td class="text-center">{{jdate($story->created_at)->format('%d %B %Y | H:m')}}</td>
                                                <td class="text-center">{{jdate($story->expiry_date)->format('%d %B %Y | H:m')}}</td>

                                                <td class="text-center">
                                                    <div
                                                        class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                                        {{-- بازدید کل --}}
                                                        <span class="badge badge-light-primary" data-toggle="tooltip"
                                                              title="بازدید کل" style="margin: 2px;">
            <i class="fa fa-eye"></i> {{ number_format($story->views_count) }}
        </span>

                                                        {{-- بازدید واقعی --}}
                                                        <span class="badge badge-light-success" data-toggle="tooltip"
                                                              title="بازدید واقعی" style="margin: 2px;">
            <i class="fa fa-user-check"></i> {{ number_format($story->real_views_count) }}
        </span>

                                                        {{-- لایک --}}
                                                        <span class="badge badge-light-danger" data-toggle="tooltip"
                                                              title="تعداد لایک" style="margin: 2px;">
            <i class="fa fa-heart"></i> {{ number_format($story->likes_count) }}
        </span>

                                                        {{-- کامنت با نشانگر کامنت‌های pending --}}
                                                        <a href="{{ route('admin.stories.details', $story) }}#comments"
                                                           class="badge badge-light-info position-relative"
                                                           data-toggle="tooltip" title="مدیریت کامنت‌ها"
                                                           style="text-decoration: none;margin: 2px;">
                                                            <i class="fa fa-comment"></i> {{ number_format($story->comments()->where('status', 'approved')->count()) }}
                                                            @php
                                                                $pendingCommentsCount = $story->comments()->where('status', 'pending')->count();
                                                            @endphp
                                                            @if($pendingCommentsCount > 0)
                                                                <span
                                                                    class="badge badge-warning pending-badge">{{ $pendingCommentsCount }}</span>
                                                            @endif
                                                        </a>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                                                id="dropdownMenu{{ $story->id }}" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                             aria-labelledby="dropdownMenu{{ $story->id }}">
                                                            @can('stories.details')
                                                                <a class="dropdown-item"
                                                                   href="{{ route('admin.stories.details', $story) }}">
                                                                    <i class="fa-solid fa-chart-simple mr-1"></i>جزئیات
                                                                    و آمار
                                                                </a>
                                                            @endcan
                                                            <div class="dropdown-divider"></div>
                                                            @can('stories.update')
                                                                <a class="dropdown-item"
                                                                   href="{{ route('admin.stories.edit', $story) }}"><i
                                                                        class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                <div class="dropdown-divider"></div>
                                                            @endcan
                                                            @can('stories.delete')
                                                                <button class="dropdown-item btn-delete"
                                                                        data-toggle="modal" data-id="{{$story->id}}"
                                                                        data-target="#delete-modal"
                                                                        data-action="{{route('admin.stories.destroy',$story)}}">
                                                                    <i class="fa-solid fa-trash-can mr-1"></i> حذف
                                                                </button>
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
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست استوری ها</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                {{ $stories->links() }}

            </div>

        </div>
    </div>

    @can('stories.delete')
        {{-- multiple delete modal --}}
        <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف استوری ها دیگر قادر به بازیابی آنها نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.stories.multipleDestroy') }}" id="story-multiple-delete-form">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light"
                                    data-dismiss="modal">خیر
                            </button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- delete product modal --}}
        <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف استوری دیگر قادر به بازیابی آن نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="story-delete-form">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light"
                                    data-dismiss="modal">
                                خیر
                            </button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/stories/index.js') }}"></script>

@endpush
