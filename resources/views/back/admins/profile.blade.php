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
                                    <li class="breadcrumb-item active">ویرایش پروفایل
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <section  class="card">
                    <div class="card-header">
                        <h4 class="card-title">ویرایش پروفایل</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                     <!-- users edit media object start -->
                                    <div class="media mb-2">
                                        <a class="mr-2 my-25">
                                            <img id="profile-pic" src="{{ auth('adminPanel')->user()->imageUrl }}" alt="users avatar" class="users-avatar-shadow rounded" height="90" width="90">
                                        </a>
                                        <div class="media-body mt-50">

                                            <div class="col-12 d-flex mt-1 px-0">
                                                <button id="edit-image-btn" class="btn btn-primary mr-75">ویرایش تصویر</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- users edit media object ends -->
                                    <form id="profile-form" action="{{ route('admin.admin.profile.update') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                                        @csrf
                                        @method('put')
                                        <div class="row">
                                            <input type="file" name="image" id="profile-image" accept="image/*" style="display: none;">
                                            <div class="col-12 col-sm-6">
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <div class="controls">
                                                            <label>نام</label>
                                                            <input name="first_name" type="text" class="form-control" placeholder="نام" value="{{ auth('adminPanel')->user()->first_name }}" required >
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <div class="controls">
                                                            <label>نام خانوادگی</label>
                                                            <input name="last_name" type="text" class="form-control" placeholder="نام خانوادگی" value="{{ auth('adminPanel')->user()->last_name }}" required >
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>نام کاربری</label>
                                                        <input name="username" type="text" class="form-control" placeholder="نام کاربری" value="{{ auth('adminPanel')->user()->username }}" required >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>گذرواژه</label>
                                                        <input id="password" name="password" type="text" class="form-control pw" placeholder="گذرواژه">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>تکرار گذرواژه</label>
                                                        <input name="password_confirmation" type="text" class="form-control pw" placeholder="تکرار گذرواژه">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>رنگ سایدبار</label>
                                                        <select name="theme_color" class="form-control">
                                                            <option value="light" {{ user_option('theme_color') == 'light' ? 'selected' : '' }}>سفید</option>
                                                            <option value="dark" {{ user_option('theme_color') == 'dark' ? 'selected' : '' }}>مشکی</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>فونت</label>
                                                        <select name="theme_font" class="form-control">
                                                            <option value="iranyekan" {{ user_option('theme_font') == 'iranyekan' ? 'selected' : '' }}>iranyekan</option>
                                                            <option value="Yekan" {{ user_option('theme_font') == 'Yekan' ? 'selected' : '' }}>Yekan</option>
                                                            <option value="Vazir" {{ user_option('theme_font') == 'Vazir' ? 'selected' : '' }}>Vazir</option>
                                                            <option value="IransansDN" {{ user_option('theme_font') == 'IransansDN' ? 'selected' : '' }}>IransansDN</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>نوع منو</label>
                                                        <select name="menu_type" class="form-control">
                                                            <option value="normal" {{ user_option('menu_type') == 'normal' ? 'selected' : '' }}>معمولی</option>
                                                            <option value="collapsed" {{ user_option('menu_type') == 'collapsed' ? 'selected' : '' }}>جمع شده</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>درباره خودتان</label>
                                                    <textarea name="bio" id="bio" rows="3" class="form-control">{{ auth('adminPanel')->user()->bio }}</textarea>
                                                </div>
                                            </div>
                                            @can('notifications.panel')
                                            <div class="col-12">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="subscribe-to-webpush" {{ auth('adminPanel')->user()->pushSubscriptions()->first() ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="subscribe-to-webpush">
                                                    </label>
                                                    <span class="switch-label">دریافت نوتیفیکیشن</span>

                                                </div>
                                            </div>
                                            @endcan
                                            <div class="col-12">
                                                <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                                    <i class="feather icon-info ml-1 align-middle"></i>
                                                    <span>در صورتی که نمیخواهید گذرواژه تان را عوض کنید، فیلدهای گذرواژه را خالی بگذارید.</span>
                                                </div>
                                            </div>


                                        </div>
                                        <hr>
                                        <div class="row">

                                            <div class="col-12 mb-1">
                                                <h5 class="card-title"> شبکه های اجتماعی شخصی </h5>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>اینستاگرام</label>
                                                        <input name="instagram" type="text" class="form-control" placeholder="نشانی صفحه اینستاگرام" value="{{ auth()->user()->instagram ?? '' }}" >
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>واتساپ</label>
                                                        <input name="whatsapp" type="text" class="form-control" placeholder="شماره واتساپ با کد کشور" value="{{ auth()->user()->whatsapp ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>تلگرام</label>
                                                        <input name="telegram" type="text" class="form-control" placeholder="آیدی تلگرام" value="{{ auth()->user()->telegram ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>توییتر</label>
                                                        <input name="twitter" type="text" class="form-control" placeholder="نشانی صفحه توییتر" value="{{ auth()->user()->twitter ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>فیسبوک</label>
                                                        <input name="facebook" type="text" class="form-control" placeholder="نشانی صفحه فیسبوک" value="{{ auth()->user()->facebook ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>روبیکا</label>
                                                        <input name="rubika" type="text" class="form-control" placeholder="آیدی روبیکا" value="{{ auth()->user()->rubika ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>بله</label>
                                                        <input name="bale" type="text" class="form-control" placeholder="آیدی بله" value="{{ auth()->user()->bale ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="controls">
                                                        <label>ایتا</label>
                                                        <input name="eitaa" type="text" class="form-control" placeholder="آیدی ایتا" value="{{ auth()->user()->eitaa ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex flex-sm-row flex-column justify-content-end mt-1">
                                                <button type="submit" class="btn btn-primary glow mb-1 mb-sm-0 mr-0 mr-sm-1">ذخیره تغییرات</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>


            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/admins/profile.js') }}"></script>
@endpush
