@extends('back.layouts.master')

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
                                    <li class="breadcrumb-item active">مدیریت درخواستی ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <!-- filter start -->
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
                    <div class="card-content collapse show">
                        <div class="card-body pt-0">
                            <div class="users-list-filter">
                                <form id="filter-comments-form">
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label for="filter-status">وضعیت</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="status_pay" id="filter-status">
                                                    <option value="">همه</option>
                                                    <option value="waiting" {{ request('status_pay') == 'waiting' ? 'selected' : '' }}>در انتظار پرداخت</option>
                                                    <option value="pay" {{ request('status_pay') == 'pay' ? 'selected' : '' }}>پرداخت شده</option>
                                                    <option value="unpay" {{ request('status_pay') == 'unpay' ? 'selected' : '' }}>پرداخت نشده</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label for="filter-ordering">نوع کاربر</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="source" id="filter-ordering">
                                                    <option value="">همه</option>
                                                    <option value="user" {{ request('source') == 'user' ? 'selected' : '' }}>کاربران سایت</option>
                                                    <option value="seller" {{ request('source') == 'seller' ? 'selected' : '' }}>فروشندگان</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label for="filter-ordering">مرتب سازی</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="ordering" id="filter-ordering">
                                                    <option value="latest" {{ request('ordering') == 'latest' ? 'selected' : '' }}>جدیدترین</option>
                                                    <option value="oldest" {{ request('ordering') == 'oldest' ? 'selected' : '' }}>قدیمی ترین</option>
                                                </select>
                                            </fieldset>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- filter end -->

                <div class="list-comments">
                    @if($items->count())
                        <section class="card">
                            <div class="card-header">
                                <h4 class="card-title">مدیریت درخواستی ها</h4>

                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><button type="button" data-toggle="modal" data-target="#request-deposit-export-modal" class="btn btn-outline-primary waves-effect waves-light"><i class="fa fa-file-excel-o"></i> خروجی گرفتن از لیست</button></li>
                                        </ul>
                                    </div>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="mb-2 collapse datatable-actions">
                                        <div class="d-flex align-items-center">
                                            <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>

                                            <button class="btn btn-success mr-2" type="button" data-toggle="modal" data-target="#multiple-status-modal">تغییر وضعیت همه</button>
                                        </div>
                                    </div>
                                    <div class="datatable datatable-bordered datatable-head-custom" id="users_datatable" data-action="{{ route('admin.users.apiIndex') }}"></div>
                                </div>
                            </div>
                            <div class="card-content" id="main-card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">ID</th>
                                                    <th>نام</th>
                                                    <th>نوع کاربر</th>
                                                    <th>مبلغ (تومان)</th>
                                                    <th >نوع تراکنش	</th>
                                                    <th>تاریخ</th>
                                                    <th class="text-center">وضعیت</th>
                                                    <th class="text-center">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $item)
                                                    @php
                                                        $is_deposit = $item->type == 'deposit';
                                                    @endphp
                                                    <tr id="data-{{ $item->id }}">
                                                        <td class="text-center">
                                                            {{ $item->id }}
                                                        </td>
                                                        <td>
                                                            @if($item->source=="user")
                                                                @if($item->wallet->user)
                                                                    <a href="{{ route('admin.users.show', ['user' => $item->wallet->user]) }}" target="_blank"><i class="feather icon-external-link"></i></a>
                                                                    {{$item->wallet->user->full_name}}
                                                                @else
                                                                    <div class="badge badge-danger ml-1">حذف شده</div>
                                                                @endif

                                                            @else
                                                                @if($item->wallet->seller)
                                                                    {{$item->wallet->seller->full_name}}
                                                                    <a href="{{ route('admin.sellers.show', ['seller' => $item->wallet->seller]) }}" target="_blank"><i class="feather icon-external-link"></i></a>
                                                                @else
                                                                    <div class="badge badge-danger ml-1">حذف شده</div>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($item->source=="seller")
                                                                فروشنده
                                                            @elseif($item->source=="user")
                                                                کاربر سایت
                                                            @endif
                                                        </td>
                                                        <td class="{{ $is_deposit ? 'text-success' : 'text-danger' }}">{{ number_format($item->amount) }}</td>
                                                        <td>
                                                            @if($item->withdraw)
                                                                برداشت وجه
                                                                <div class="badge badge-danger ml-1">
                                                                    <i class="feather icon-arrow-left"></i>
                                                                </div>
                                                            @else
                                                                @if ($is_deposit)
                                                                    افزایش اعتبار
                                                                    <div class="badge badge-success ml-1">
                                                                        <i class="feather icon-arrow-up"></i>
                                                                    </div>
                                                                @else
                                                                    کاهش اعتبار
                                                                    <div class="badge badge-danger ml-1">
                                                                        <i class="feather icon-arrow-down"></i>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td>{{ jdate($item->created_at) }}</td>
                                                        <td class="text-center">
                                                            <div class="status-pay">
                                                                    @if($item->withdraw)
                                                                        @if($item->status_pay=="waiting")
                                                                            <div class="badge  badge-warning ">در انتظار پرداخت</div>
                                                                        @elseif($item->status_pay=="pay")
                                                                            <div class="badge badge-success">پرداخت شده</div>
                                                                        @elseif($item->status_pay=="unpay")
                                                                            <div class="badge badge-danger">پرداخت نشده</div>
                                                                        @elseif($item->status_pay=="unpay-refund")
                                                                            <div class="badge badge-danger">پرداخت نشده و مبلغ عودت داده شده</div>
                                                                        @endif

                                                                    @endif
                                                                </div>
                                                        </td>

                                                        <td class="text-center">
                                                                <button data-action="{{ route('admin.request-deposit.history', ['history' => $item]) }}" class="btn btn-info waves-effect waves-light show-history">مشاهده</button>
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
                                <h4 class="card-title">مدیریت درخواستی ها</h4>
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
                    {{ $items->appends(request()->all())->links() }}
                </div>


            </div>
        </div>
    </div>

    <!-- show Modal -->
    <div class="modal fade text-left" id="show-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel21">جزئیات تراکنش</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="history-detail" class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="request-deposit-export-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="users-export-form" action="{{ route('admin.request-deposit.export') }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">نوع کاربر</label>
                                    <select name="source_type" class="form-control">
                                        <option value="all">همه</option>
                                        <option value="users">کاربران</option>
                                        <option value="sellers">فروشندگان</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-id" type="checkbox" class="custom-control-input" name="filters[id]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-id">آیدی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[source]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-first_name">نوع کاربر</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[name]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-first_name">نام و نام خانوادگی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[mobile]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-first_name">شماره موبایل</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[national_identity_number]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-first_name">کدملی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-username" type="checkbox" class="custom-control-input" name="filters[amount]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-username">مبلغ</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-email" type="checkbox" class="custom-control-input" name="filters[card_number]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-email">شماره کارت</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-email" type="checkbox" class="custom-control-input" name="filters[shaba_number]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-email">شماره شبا</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-created_at" type="checkbox" class="custom-control-input" name="filters[created_at]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-created_at">تاریخ ثبت </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">نوع خروجی</label>
                                    <select name="export_type" class="form-control">
                                        <option value="excel">اکسل</option>
                                        {{-- <option value="print">چاپ</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn personal-success-btn waves-effect waves-light">خروجی گرفتن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- export modal -->
    <div class="modal fade text-left" id="multiple-status-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="users-export-form" action="{{ route('admin.users.export') }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-id" type="checkbox" class="custom-control-input" name="filters[id]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-id">آیدی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[first_name]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-first_name">نام</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-last_name" type="checkbox" class="custom-control-input" name="filters[last_name]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-last_name">نام خانوادگی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-username" type="checkbox" class="custom-control-input" name="filters[username]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-username">نام کاربری</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-email" type="checkbox" class="custom-control-input" name="filters[email]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-email">ایمیل</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-created_at" type="checkbox" class="custom-control-input" name="filters[created_at]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-created_at">تاریخ ثبت نام</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">نوع خروجی</label>
                                    <select name="export_type" class="form-control">
                                        <option value="excel">اکسل</option>
                                        {{-- <option value="print">چاپ</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn personal-success-btn waves-effect waves-light">خروجی گرفتن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/autosize-js/autosize.min.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/requestDeposit/index.js') }}"></script>
@endpush
