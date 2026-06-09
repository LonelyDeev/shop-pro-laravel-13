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
                                    <li class="breadcrumb-item">گزارشات
                                    </li>
                                    <li class="breadcrumb-item active">فروش هر محصول
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="content-body">
                <!-- filter start -->
                @include('back.statistics.orders.partials.filters')
                <!-- filter end -->

                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست محصولات</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">

                            <div class="datatable datatable-bordered datatable-head-custom statistics-sell-products" id="orders_products" data-action="{{ route('admin.statistics.orders.products.get_product') }}" data-parameter='{{ json_encode(request()->query()) }}'>

                                <div id='productItems'>

                                </div>



                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

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
                    با حذف سفارشات دیگر قادر به بازیابی آنها نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.orders.multipleDestroy') }}" id="order-multiple-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- export modal -->
    <div class="modal fade text-left" id="orders-export-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="orders-export-form" action="{{ route('admin.orders.export') }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-row" type="checkbox" class="custom-control-input" name="filters[row]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-row">ردیف</label>
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
                                    <input id="export-checkbox-user_id" type="checkbox" class="custom-control-input" name="filters[user_id]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-user_id">آیدی کاربر</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-name" type="checkbox" class="custom-control-input" name="filters[name]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-name">نام و نام خانوادگی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-mobile" type="checkbox" class="custom-control-input" name="filters[mobile]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-mobile">شماره همراه</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-province" type="checkbox" class="custom-control-input" name="filters[province]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-province">استان</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-city" type="checkbox" class="custom-control-input" name="filters[city]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-city">شهر</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-postal_code" type="checkbox" class="custom-control-input" name="filters[postal_code]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-postal_code">کد پستی</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-address" type="checkbox" class="custom-control-input" name="filters[address]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-address">آدرس کامل</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-carrier" type="checkbox" class="custom-control-input" name="filters[carrier]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-carrier">شیوه تحویل</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-created_at" type="checkbox" class="custom-control-input" name="filters[created_at]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-created_at">تاریخ ثبت</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-shipping_cost" type="checkbox" class="custom-control-input" name="filters[shipping_cost]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-shipping_cost">هزینه ارسال</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-total_discount" type="checkbox" class="custom-control-input" name="filters[total_discount]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-total_discount">تخفیف</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-price" type="checkbox" class="custom-control-input" name="filters[price]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-price">جمع قیمت</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-description" type="checkbox" class="custom-control-input" name="filters[description]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-description">توضیحات سفارش</label>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox custom-checkbox-success">
                                    <input id="export-checkbox-products" type="checkbox" class="custom-control-input" name="filters[products]" value="1" checked>
                                    <label class="custom-control-label" for="export-checkbox-products">لیست محصولات</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">نوع خروجی</label>
                                    <select name="export_type" class="form-control">
                                        <option value="excel">اکسل</option>
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

    <div class="modal fade" id="shiping-status-change" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shiping-status-changeTitle">تغیر وضعیت ارسال گروهی سفارش ها</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="multiple-shipping-status-change" action="{{ route('admin.orders.shippings-status') }}">
                    <div class="modal-body">

                        <select name="status" class="form-control">
                            <option value="w-pending">در انتظار بررسی</option>
                            <option value="pending">در حال بررسی</option>
                            <option value="waiting">منتظر ارسال</option>
                            <option value="sent">ارسال شد</option>
                            <option value="canceled">ارسال لغو شد</option>
                        </select>



                        <div id='shiping-status-canceled' class="mt-2 d-none">
                            <fieldset class="checkbox">
                                <div class="vs-checkbox-con vs-checkbox-primary">
                                    <input type="checkbox" name="backMoney">
                                    <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                    <span>بازگشت مبلغ کالا به کیف پول؟</span>
                                </div>
                            </fieldset>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['datatable', 'persian-datepicker']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/statistics/products.js') }}?v=7"></script>
@endpush
