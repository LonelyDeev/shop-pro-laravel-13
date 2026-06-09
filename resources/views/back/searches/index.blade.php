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
        .story-ring img{
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
              {{--  <div class="card">
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
                        <div class="card-body">
                            <div class="users-list-filter">
                                <form id="filter-comments-form" method="GET"
                                      action="{{route('admin.stories.index')}}">
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
                                                    <option value="latest" {{ request('ordering') == 'latest' ? 'selected' : '' }}>
                                                        جدیدترین
                                                    </option>
                                                    <option value="oldest" {{ request('ordering') == 'oldest' ? 'selected' : '' }}>
                                                        قدیمی ترین
                                                    </option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-3">
                                            <label>تعداد در صفحه</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="paginate">
                                                    <option value="10" {{ request('paginate') == '10' ? 'selected' : '' }}>
                                                        10
                                                    </option>
                                                    <option value="20" {{ request('paginate') == '20' ? 'selected' : '' }}>
                                                        20
                                                    </option>
                                                    <option value="50" {{ request('paginate') == '50' ? 'selected' : '' }}>
                                                        50
                                                    </option>
                                                    <option value="all" {{ request('paginate') == 'all' ? 'selected' : '' }}>
                                                        همه
                                                    </option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-3">
                                            <label>وضعیت انتشار</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="status">
                                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                                                        همه
                                                    </option>
                                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                                                        انتشار شده
                                                    </option>
                                                    <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>
                                                        منقضی شده
                                                    </option>
                                                </select>
                                            </fieldset>
                                        </div>


                                    </div>
                                    <div class="row">

                                        <div class="col-12 text-right">
                                            <button type="submit"
                                                    class="btn btn-outline-success square  mb-1 waves-effect waves-light">
                                                فیلتر کردن
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>--}}
                @if($searches->count())


                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست جستجوها</h4>

                        </div>



                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="mb-2 collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>
                                        @can('searches.delete')
                                        <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                        @endcan
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th data-field="id" class="datatable-cell-center">
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
                                            <th>کلمه جستجو</th>
                                            <th>نوع جستجو</th>
                                            <th class="text-center">تعداد جستجو</th>
                                            <th class="text-center">میانگین نتایج</th>
                                            <th class="text-center">آخرین جستجو</th>
                                            <th class="text-center">اولین جستجو</th>
                                            <th class="text-center" style="width: 120px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($searches as $search)
                                            <tr class="search-row" id="row-{{ $search->keyword }}__{{ $search->search_type }}"
                                                data-keyword="{{ $search->keyword }}"
                                                data-type="{{ $search->search_type }}">

                                                <td>

                                                    <fieldset class="checkbox">
                                                        <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" value="{{ $search->keyword }}__{{ $search->search_type }}">
                                                            <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        </div>
                                                    </fieldset>

                                                </td>

                                                <td>
                                                    <strong>{{ $search->keyword }}</strong>
                                                </td>

                                                <td>
                                                    @if($search->search_type == 'products')
                                                        <span class="badge badge-primary">محصولات</span>
                                                    @else
                                                        <span class="badge badge-success">پست‌ها</span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                    <span class="badge badge-info" style="font-size: 14px;">
                        {{ $search->search_count }} بار
                    </span>
                                                </td>

                                                <td class="text-center">
                                                    {{ round($search->avg_results) }}
                                                </td>

                                                <td class="text-center">
                                                    {{ jdate($search->last_searched)->format('%d %B %Y | H:i') }}
                                                </td>

                                                <td class="text-center">
                                                    {{ jdate($search->first_searched)->format('%d %B %Y') }}
                                                </td>

                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-info btn-show-details font-s-10"
                                                            data-keyword="{{ $search->keyword }}"
                                                            data-type="{{ $search->search_type }}"
                                                            data-action="{{route('admin.searches.details')}}">
                                                        <i class="fa fa-users"></i> جزئیات
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">هیچ جستجویی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست جستجوها</h4>
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
                {{ $searches->links() }}

            </div>

        </div>
    </div>

    <!-- مودال نمایش جزئیات جستجو -->
    <div class="modal fade" id="searchDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات جستجو</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="search-details-container">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    @can('searches.delete')
    {{-- multiple delete modal --}}
    <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog"  aria-hidden="true">
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
                    <form action="{{ route('admin.searches.multipleDestroy') }}" id="story-multiple-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
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
                    <form action="#" id="search-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">
                            خیر
                        </button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/searches/index.js') }}"></script>

@endpush
