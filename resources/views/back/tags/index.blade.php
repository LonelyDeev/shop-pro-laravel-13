@extends('back.layouts.master')

@push('styles')
    <style>
        .tag-usage {
            font-size: 14px;
            font-weight: 600;
        }
        .tag-views {
            color: #17a2b8;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            color: white;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
        }
        .stat-card .stat-label {
            font-size: 14px;
            opacity: 0.9;
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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item active">تگ‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-3 col-12 mb-2">
                    <div class="btn-group float-md-right">
                        @can('tags.create')
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createTagModal">
                            <i class="fa fa-plus"></i> ایجاد تگ جدید
                        </button>
                        @endcan
                      {{--  <a href="{{ route("admin.tags.export") }}" type="button" class="btn btn-info" id="export-tags">
                            <i class="fa fa-download"></i> خروجی اکسل
                        </a>--}}
                    </div>
                </div>
            </div>

            <div class="content-body">
                <!-- آمار -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-value">{{ $statistics['total'] }}</div>
                                <div class="stat-label">کل تگ‌ها</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body">
                                <div class="stat-value">{{ $statistics['total_views'] }}</div>
                                <div class="stat-label">کل بازدیدها</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="card-body">
                                <div class="stat-value">{{ $statistics['most_used']->name ?? '-' }}</div>
                                <div class="stat-label">پربازدیدترین تگ</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="card-body">
                                <div class="stat-value">{{ $statistics['most_viewed']->view_count ?? 0 }}</div>
                                <div class="stat-label">بیشترین بازدید</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- فیلترها -->
                <div class="card">
                    <div class="card-header filter-card" style="padding-bottom: 1.5rem;">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse" class=""><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse {{request('search')?'show' : ''}}" style="">
                        <div class="card-body">
                            <div class="users-list-filter">
                                <form id="filter-products-form" method="GET" action="{{ route('admin.tags.index') }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>عنوان</label>
                                            <fieldset class="form-group">
                                                <input class="form-control datatable-filter"  placeholder="عنوان یا اسلاگ..." name="search" value="{{ request('search') }}">
                                            </fieldset>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary mr-1 waves-effect waves-light">فیلتر </button>
                                            <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary waves-effect waves-light">پاک کردن</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول تگ‌ها -->
                @if($tags->count())
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست تگ‌ها</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">

                                @can('tags.delete')
                                <div class="mb-2 collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3">
                                            <span id="datatable-selected-rows">0</span> مورد انتخاب شده:
                                        </div>
                                        <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">
                                            حذف موارد انتخاب شده
                                        </button>
                                    </div>
                                </div>
                                @endcan

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th data-field="id" class="datatable-cell-center">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary checkbox-all">
                                                        <input type="checkbox" id="select-all">
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    </div>
                                                </fieldset>
                                            </th>
                                            <th>شناسه</th>
                                            <th>عنوان تگ</th>
                                            <th>اسلاگ</th>
                                            <th class="text-center">تعداد استفاده</th>
                                            <th class="text-center">بازدید</th>
                                            <th class="text-center">تاریخ ایجاد</th>
                                            <th class="text-center" style="width: 120px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($tags as $tag)
                                            <tr id="row-{{ $tag->id }}" data-id="{{ $tag->id }}">
                                                <td class="datatable-cell-center">
                                                    <fieldset class="checkbox">
                                                        <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" class="tag-checkbox" value="{{ $tag->id }}">
                                                            <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        </div>
                                                    </fieldset>
                                                </td>
                                                <td>{{ $tag->id }}</td>
                                                <td>
                                                    <strong>{{ $tag->name }}</strong>
                                                </td>
                                                <td><code>{{ $tag->slug }}</code></td>
                                                <td class="text-center">
                                            <span class="badge badge-info tag-usage">
                                                {{ $tag->taggables()->count() }} بار
                                            </span>
                                                </td>
                                                <td class="text-center">
                                            <span class="tag-views">
                                                <i class="fa fa-eye"></i> {{ number_format($tag->view_count) }}
                                            </span>
                                                </td>
                                                <td class="text-center">
                                                    {{ jdate($tag->created_at)->format('d %B Y') }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            @can('tags.show')
                                                            <button class="dropdown-item btn-show-details" data-id="{{ $tag->id }}" data-action="{{ route('admin.tags.details', $tag) }}">
                                                                <i class="fa fa-info-circle"></i> جزئیات
                                                            </button>
                                                            @endcan
                                                            @can('tags.update')
                                                            <div class="dropdown-divider"></div>
                                                            <button class="dropdown-item btn-edit-tag" data-id="{{ $tag->id }}" data-name="{{ $tag->name }}" data-action="{{ route("admin.tags.update", $tag->id) }}">
                                                                <i class="fa fa-edit"></i> ویرایش
                                                            </button>
                                                            @endcan
                                                            @can('tags.delete')
                                                            <div class="dropdown-divider"></div>
                                                            <button class="dropdown-item btn-delete" data-toggle="modal" data-id="{{ $tag->id }}" data-target="#delete-modal" data-action="{{ route('admin.tags.destroy', $tag) }}">
                                                                <i class="fa fa-trash"></i> حذف
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

                                <div class="mt-3">
                                    {{ $tags->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body text-center">
                                <i class="fa fa-tags fa-3x text-muted mb-3"></i>
                                <p class="text-muted">هیچ تگی یافت نشد</p>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#createTagModal">
                                    <i class="fa fa-plus"></i> ایجاد اولین تگ
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('tags.create')
    <!-- مودال ایجاد تگ -->
    <div class="modal fade" id="createTagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ایجاد تگ جدید</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form id="create-tag-form" action="{{ route('admin.tags.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>عنوان تگ</label>
                            <input type="text" name="name" class="form-control" required>
                            <small class="text-muted">اسلاگ به طور خودکار ایجاد می‌شود</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @can('tags.update')
    <!-- مودال ویرایش تگ -->
    <div class="modal fade" id="editTagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش تگ</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <form id="edit-tag-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>عنوان تگ</label>
                            <input type="text" name="name" id="edit-tag-name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">به‌روزرسانی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @can('tags.show')
    <!-- مودال جزئیات تگ -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات تگ</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body" id="details-container">
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
    @endcan


    @can('tags.delete')
    <!-- مودال حذف گروهی -->
    <div class="modal fade" id="multiple-delete-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    با حذف تگ‌ها، اطلاعات آنها برای همیشه پاک خواهد شد
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.tags.multiple-destroy') }}" id="multiple-delete-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids" id="delete-ids">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال حذف تکی -->
    <div class="modal fade" id="delete-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    با حذف این تگ، اطلاعات آن برای همیشه پاک خواهد شد
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/tags/index.js') }}?v=2"></script>
@endpush
