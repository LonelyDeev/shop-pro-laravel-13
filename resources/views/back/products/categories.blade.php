@extends('back.layouts.master')

@push('styles')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('back/app-assets/plugins/nestable2/jquery.nestable.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.css') }}">
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
                                    <li class="breadcrumb-item">مدیریت محصولات
                                    </li>
                                    <li class="breadcrumb-item active">دسته بندی ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <div class="form-group breadcrum-right">
                        <div id="save-changes" class="spinner-border text-success" role="status" style="display: none">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">ّ
                <!-- Categories -->
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title">مدیریت دسته بندی ها</h4>
                    </div>
                    <div id="main-block" class="card-content">
                        <div class="card-body p-rl-0-responsive-500">
                            <!-- Categories -->
                            <div class="col-12 offset-xl-2">

                                <form id="create-category" action="{{ route('admin.categories.store') }}" method="POST">
                                    <div class="form-group">
                                        <div class="row">
                                            <input type="hidden" name="type" value="productcat">
                                            <div class="col-md-5 col-sm-12 col-12 mb-1">
                                                <input id="title" type="text" class="form-control" name="title" placeholder="افزودن دسته بندی جدید...">
                                            </div>
                                            <div class="col-md-2 col-sm-12 col-12">
                                                <button type="submit" class="btn personal-success-btn waves-effect waves-light w-100">افزودن</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- List categories -->
                                <div class="dd mt-4">
                                    <ol class="dd-list">
                                        @foreach ($categories as $category)
                                            @include('back.partials.child_category', ['child_category' => $category])
                                        @endforeach
                                    </ol>
                                </div>
                                <!-- END List categories -->
                                <p class="card-text mt-3"><i class="feather icon-info mr-1 align-middle"></i><span class="text-info">برای ایجاد زیر دسته، دسته بندی مورد نظر را به  سمت چپ بکشید.</span></p>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade text-left" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف این دسته بندی تمامی زیر دسته های آن حذف خواهند شد، آیا برای حذف
                            مطمئن هستید؟
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success waves-effect waves-light" data-dismiss="modal">خیر</button>
                    <button id="confirm-delete" type="button" class="btn btn-danger waves-effect waves-light">بله حذف شود</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END Delete Modal -->

    <!-- Edit Modal -->
    <div class="modal fade text-left" id="modal-edit" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ویرایش دسته بندی </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="edit-form" action="#">
                    @method('put')
                    <div class="modal-body">

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn personal-danger-btn waves-effect waves-light" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn personal-success-btn waves-effect waves-light">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END Edit Modal -->

@endsection

@push('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('back/app-assets/plugins/nestable2/jquery.nestable.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/ckeditor/ckeditor.js') }}"></script>

    <script>
        var maxDepth = 10;
    </script>

    <!-- Page JS Code -->
    <script src="{{ asset('back/assets/js/pages/categories.js') }}"></script>
@endpush
