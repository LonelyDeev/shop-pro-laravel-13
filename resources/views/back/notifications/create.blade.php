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
                                    <li class="breadcrumb-item">مدیریت اعلان ها
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد اعلان ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد اعلان جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form class="form" id="notifications-create-form" data-redirect="{{ route('admin.notifications.index') }}" action="{{ route('admin.notifications.store') }}" method="post">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>عنوان</label>
                                                    <input type="text" class="form-control" name="title">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>وضعیت اعلان‌</label>
                                                    <select name="priority" class="form-control">
                                                        <option value="low">متوسط</option>
                                                        <option value="medium">مهم</option>
                                                        <option value="high">خیلی مهم</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" class="relevant-user" name="users" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>ارسال به کاربران سایت</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-12 " id="users-div">
                                                <div class="form-group">
                                                    <label>کاربر مربوطه</label>
                                                    <select id="user_id" name="user_id[]" class="form-control users" multiple>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}">{{$user->fullname .' (id=>'.$user->id.' mobile=>'.$user->mobile.')'}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" class="relevant-user" name="sellers" checked>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>ارسال به فروشندگان</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-12" id="sellers-div">
                                                <div class="form-group">
                                                    <label>فروشنده مربوطه</label>
                                                    <select id="seller_id" name="seller_id[]" class="form-control sellers" multiple>
                                                        @foreach ($sellers as $seller)
                                                            <option value="{{ $seller->id }}">{{$seller->fullname .' (id=>'.$seller->id.' mobile=>'.$seller->mobile.')'}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="message">پیام</label>
                                                    <textarea id="message" class="form-control" rows="4" name="message"></textarea>
                                                </div>
                                            </div>


                                            <div class="col-md-4 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="popup">
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>نمایش به صورت پاپ آپ در پنل</span>
                                                    </div>
                                                </fieldset>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ایجاد اعلان</button>
                                            </div>

                                            <div class="col-12">
                                                <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                                    <i class="feather icon-info ml-1 align-middle"></i>
                                                    <span>در صورتی که نمیخواهید اعلان به همه ی کاربران یا فروشندگان ارسال شود فیلد های مربوطه را انتخاب کنید.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/notifications/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/notifications/create.js') }}"></script>
@endpush
