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
                                    <li class="breadcrumb-item"> فیلدهای اختصاصی
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد فیلد اختصاصی
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد فیلد جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form id="fild-create-form" class="form" id="province-create-form" action="{{ route('admin.filds.store') }}" data-redirect="{{ route('admin.filds.index') }}" method="post">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>فیلد متعلق است به</label>
                                                    <select name="belongs_to" id="belongs_to" class="form-control valid" aria-invalid="false">
                                                        <option value="users">کاربران</option>
                                                        <option value="products">محصولات</option>
                                                        <option value="posts">مقالات</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>نوع محصول</label>
                                                    <select name="type" id="type" class="form-control valid" aria-invalid="false">
                                                        <option value="input">(input) ورودی کلمات</option>
                                                        <option value="textarea">(textarea) ورودی متن</option>
                                                        <option value="number">(number) ورودی عدد</option>
                                                        <option value="email">(email) ورودی ایمیل</option>
                                                        <option value="colorPicker">(colorPicker) انتخاب رنگ</option>
                                                        <option value="checkbox">(checkbox) چک باکس</option>
                                                        <option value="select">(select) انتخابی</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>عنوان</label>
                                                    <input type="text" class="form-control" name="title">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>مقدار پیشفرض (اختیاری)</label>
                                                    <input type="text" class="form-control" name="value">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group d-none" id="select_options">
                                                    <label>مقادیر (select)</label>
                                                    <input type="text" id="tags" class="form-control" name="select_options">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="published" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>انتشار </span>
                                                    </div>
                                                </fieldset>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="required" >
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span> اجباری</span>
                                                    </div>
                                                </fieldset>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="user_show" >
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span> نمایش به کاربر</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد فیلد</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate','jquery-tagsinput']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/filds/all.js') }}"></script>
@endpush
