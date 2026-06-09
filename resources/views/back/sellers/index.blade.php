@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/datatable/datatable.css') }}">
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
                                    <li class="breadcrumb-item">مدیریت فروشگاه ها
                                    </li>
                                    <li class="breadcrumb-item active">لیست فروشگاه ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <!-- filter start -->
                @include('back.sellers.partials.index-filters', ['filter_action' => route('admin.sellers.index')])
                <!-- filter end -->

                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست فروشگاه ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="mb-2 collapse datatable-actions">
                                <div class="d-flex align-items-center">
                                    <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>

                                    <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                </div>
                            </div>
                            <div class="datatable datatable-bordered datatable-head-custom" id="products_datatable" data-action="{{ route('admin.sellers.apiIndex') }}"></div>
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
                    با حذف فروشنده ها دیگر قادر به بازیابی آنها نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.sellers.multipleDestroy') }}" id="product-multiple-delete-form">
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
                    با حذف فروشنده دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="product-delete-form">
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


@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/datatable/scripts.bundle.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/datatable/core.datatable.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/datatable/datatable.checkbox.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/sellers/index.js') }}?v=4"></script>
    <script src="{{ asset('back/assets/js/pages/sellers/filters.js') }}?v=2"></script>
@endpush
