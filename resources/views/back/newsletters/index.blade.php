@extends('back.layouts.master')
@push('styles')
    <style>
        .contact-ring {
            background: linear-gradient(315deg, var(--primary), rgba(var(--primary-rgb), .5));
            position: relative;
            width: 30px;
            min-width: 30px;
            height: 30px;
            overflow: hidden;
            border-radius: 50%;
            padding: 2px;
            border: 1px solid #375ec8;
        }
        .contact-ring img{
            width: 100%;
            height: 100%;
            border-radius: 100%;
        }
        .badge-email {
            background-color: #28a745;
            color: white;
        }
        .badge-mobile {
            background-color: #17a2b8;
            color: white;
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
                                    <li class="breadcrumb-item">خبرنامه</li>
                                    <li class="breadcrumb-item active">لیست مشترکین</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-3 col-12 mb-2">
                    <div class="btn-group float-md-right">
                        <button class="btn btn-primary btn-export" data-toggle="modal" data-target="#exportModal">
                            <i class="fa fa-download"></i> خروجی اکسل
                        </button>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- فیلترها -->
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
                        <div class="card-body">
                            <div class="users-list-filter">
                                <form id="filter-form" method="GET" action="{{route('admin.newsletters.index')}}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>جستجو</label>
                                            <fieldset class="form-group">
                                                <input class="form-control" name="search" placeholder="ایمیل یا شماره موبایل..." value="{{ request('search') }}">
                                            </fieldset>
                                        </div>
                                        <div class="col-md-3">
                                            <label>نوع اشتراک</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="contact_type">
                                                    <option value="">همه</option>
                                                    <option value="email" {{ request('contact_type') == 'email' ? 'selected' : '' }}>ایمیل</option>
                                                    <option value="mobile" {{ request('contact_type') == 'mobile' ? 'selected' : '' }}>شماره موبایل</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-3">
                                            <label>وضعیت</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="status">
                                                    <option value="">همه</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-3">
                                            <label>نوع دستگاه</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="device_type">
                                                    <option value="">همه</option>
                                                    <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>دسکتاپ</option>
                                                    <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>موبایل</option>
                                                    <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>تبلت</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <button type="submit" class="btn btn-outline-success square mb-1 waves-effect waves-light">
                                                فیلتر کردن
                                            </button>
                                            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-warning square mb-1 waves-effect waves-light">
                                                پاک کردن فیلترها
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if($subscribers->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست مشترکین خبرنامه</h4>
                            <div class="heading-elements">
                                <span class="badge badge-primary">تعداد کل: {{ $statistics['total'] }}</span>
                                <span class="badge badge-success">فعال: {{ $statistics['active'] }}</span>
                                <span class="badge badge-danger">غیرفعال: {{ $statistics['inactive'] }}</span>
                            </div>
                        </div>

                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="mb-2 collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>
                                        @can('newsletters.delete')
                                            <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                        @endcan
                                    </div>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th data-field="id" class="datatable-cell-center datatable-cell datatable-cell-check">
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
                                        <th>شناسه</th>
                                        <th>ایمیل / شماره موبایل</th>
                                        <th>نوع</th>
                                        <th class="text-center">وضعیت</th>
                                        <th class="text-center">دستگاه</th>
                                        <th class="text-center">تاریخ ثبت نام</th>
                                        <th class="text-center" style="width: 100px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($subscribers as $subscriber)
                                            <tr id="row-{{ $subscriber->id }}">
                                                <td data-field="id" aria-label="{{$subscriber->id}}">
                                                    <fieldset class="checkbox">
                                                        <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" value="{{$subscriber->id}}">
                                                            <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        </div>
                                                    </fieldset>
                                                </td>
                                                <td>{{ $subscriber->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="contact-ring mr-1">
                                                            @if($subscriber->contact_type == 'email')
                                                                <i class="fa fa-envelope" style="font-size: 12px; margin: 6px;"></i>
                                                            @else
                                                                <i class="fa fa-phone" style="font-size: 12px; margin: 6px;"></i>
                                                            @endif
                                                        </div>
                                                        <strong style="direction: ltr">{{ $subscriber->formatted_contact ?? $subscriber->contact }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($subscriber->contact_type == 'email')
                                                        <span class="badge badge-email">ایمیل</span>
                                                    @elseif($subscriber->contact_type == 'mobile')
                                                        <span class="badge badge-mobile">شماره موبایل</span>
                                                    @else
                                                        <span class="badge badge-warning">نامشخص</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($subscriber->is_active)
                                                        <span class="badge badge-success">فعال</span>
                                                    @else
                                                        <span class="badge badge-danger">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($subscriber->device_type == 'mobile')
                                                        <i class="fa fa-mobile-alt"></i> موبایل
                                                    @elseif($subscriber->device_type == 'tablet')
                                                        <i class="fa fa-tablet-alt"></i> تبلت
                                                    @elseif($subscriber->device_type == 'desktop')
                                                        <i class="fa fa-desktop"></i> دسکتاپ
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ jdate($subscriber->created_at)->format('%d %B %Y | H:i') }}</td>
                                                <td class="text-center">
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $subscriber->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $subscriber->id }}">
                                                            @can('newsletters.show')
                                                            <button class="dropdown-item btn-show-details" data-id="{{ $subscriber->id }}" data-action="{{ route('admin.newsletters.show', $subscriber) }}">
                                                                <i class="fa fa-eye mr-1"></i>جزئیات
                                                            </button>
                                                            <div class="dropdown-divider"></div>
                                                            @endcan
                                                            @can('newsletters.delete')
                                                                <button class="dropdown-item btn-delete" data-toggle="modal" data-id="{{ $subscriber->id }}" data-target="#delete-modal" data-action="{{ route('admin.newsletters.destroy', $subscriber) }}">
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
                    <div class="mt-3">
                        {{ $subscribers->appends(request()->query())->links() }}
                    </div>
                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست مشترکین خبرنامه</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text text-center">
                                    <p>هیچ مشترکی یافت نشد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    <!-- مودال نمایش جزئیات مشترک -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات مشترک</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="details-container">
                    <div class="text-center">در حال بارگذاری...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال خروجی اکسل -->
    <div class="modal fade text-left" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">خروجی اکسل</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="export-form" action="{{ route('admin.newsletters.export') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>نوع خروجی</label>
                            <select name="type" class="form-control" required>
                                <option value="">انتخاب کنید...</option>
                                <option value="all">همه مشترکین</option>
                                <option value="email">فقط ایمیل‌ها</option>
                                <option value="mobile">فقط شماره موبایل‌ها</option>
                                <option value="active">مشترکین فعال</option>
                                <option value="inactive">مشترکین غیرفعال</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>فرمت خروجی</label>
                            <select name="format" class="form-control" required>
                                <option value="xlsx">Excel (xlsx)</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn personal-primary-btn waves-effect waves-light">
                            <i class="fa fa-download"></i> دانلود
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @can('newsletters.delete')
        {{-- multiple delete modal --}}
        <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف مشترکین، اطلاعات آنها برای همیشه پاک خواهد شد
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.newsletters.multipleDestroy') }}" id="multiple-delete-form" method="POST">
                            @csrf
                            @method('delete')
                            <input type="hidden" name="ids" id="delete-ids">
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- delete single modal --}}
        <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف این مشترک، اطلاعات او برای همیشه پاک خواهد شد
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete-form" method="POST">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/newsletters/index.js') }}"></script>

@endpush
