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
                                <li class="breadcrumb-item">مدیریت کاربران
                                </li>
                                <li class="breadcrumb-item active">ایجاد کاربران از فایل Excel
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="content-body">
            <!-- Description -->
            <section class="card">
                <div class="card-header">
                    <h4 class="card-title">افزودن کاربران از فایل Excel</h4>
                </div>

                <div id="main-card" class="card-content">
                    <div class="card-body">
                        <form class="form" id="excel-create-form" action="{{ route('admin.import.users.store') }}" method="post">
                            @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myModalLabel19">فیلدهای مورد نظر را انتخاب کنید</h5>

                            </div>
                            <div class="modal-body">
                                    <ul class="row" style='list-style: persian;'>

                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-first_name" type="checkbox" class="custom-control-input" name="filters[first_name]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-first_name">نام</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-last_name" type="checkbox" class="custom-control-input" name="filters[last_name]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-last_name">نام خانوادگی</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-username" type="checkbox" class="custom-control-input" name="filters[username]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-username">نام کاربری</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-email" type="checkbox" class="custom-control-input" name="filters[email]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-email">ایمیل</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-mobile" type="checkbox" class="custom-control-input" name="filters[mobile]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-mobile">موبایل</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-password" type="checkbox" class="custom-control-input" name="filters[password]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-password">رمز ورود</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-image" type="checkbox" class="custom-control-input" name="filters[image]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-image">تصویر پروفایل</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-national_code" type="checkbox" class="custom-control-input" name="filters[national_code]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-national_code">کد ملی</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-birth_date" type="checkbox" class="custom-control-input" name="filters[birth_date]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-birth_date">تاریخ تولد</label>
                                            </div>
                                        </li>
                                        <li class="col-md-6">
                                            <div class="custom-control custom-checkbox custom-checkbox-success">
                                                <input id="export-checkbox-card_number" type="checkbox" class="custom-control-input" name="filters[card_number]" value="1" checked>
                                                <label class="custom-control-label" for="export-checkbox-card_number">شماره کارت </label>
                                            </div>
                                        </li>


                                    </ul>


                                </div>

                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <span class='d-block'>
                                      <i class="feather icon-info ml-1 align-middle"></i>
                                   بسیار مهم می باشد که جایگاه فیلد ها به همین ترتیب باشد.
                                    اگر فایل شما به عنوان مثال موبایل ویا ایمیل و... را دارا نمی باشد، این فیلد ها را خالی بگذارید و فیلدها را با همین عناوین ایجاد کنید.
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <i class="feather icon-info ml-1 align-middle"></i>
                                <span>
                                    در فایل اکسل فیلد های
                                    <span class="badge badge-danger">موبایل</span>
                                    یا
                                    <span class="badge badge-danger">ایمیل</span>
                                    <span class="badge badge-danger">رمز ورود</span>
                                    الزامی است.
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                <i class="feather icon-info ml-1 align-middle"></i>
                                <span>
                                    بعد از آپلود فایل به منظور آپلود، اگر پیغام
                                    <span class="badge badge-success">موفقیت</span>
                                    را دریافت نکردید، به منظور خطا و ایجاد نشدن کاربران می باشد.

                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 offset-md-1">

                                <div class="form-body">
                                    <div class="row">
                                        <fieldset class="form-group">
                                            <label>انتخاب فایل Excel</label>
                                            <div class="custom-file">
                                                <input id="file" type="file"  accept=".xlsx" name="file" class="custom-file-input" required>
                                                <label class="custom-file-label" for="file"></label>
                                            </div>
                                        </fieldset>
                                    </div>



                                    <div class="row">
                                        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">افزودن فایل</button>
                                    </div>
                                </div>



                        </div>

                        </form>
                    </div>
                </div>
            </section>
            <div id="form-progress" class="progress progress-bar-success progress-xl" style="display: none;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
            </div>
            <!--/ Description -->

        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/users/import.js') }}"></script>
@endpush

@php
session()->forget('ImportError');
session()->forget('ImportSuccess');
 @endphp
