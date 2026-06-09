@extends('back.auth.layouts.master', ['title' => 'ورود'])

@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <section class="row flexbox-container">
                <div class="col-xl-8 col-11 d-flex justify-content-center">

                    <div class="card bg-authentication rounded-5 mb-0 overflow-hidden">

                        <div class="row m-0">

{{--                            <div class="col-lg-6 d-lg-block d-none text-center align-self-center px-1 py-0">--}}
{{--                                <img style='width: 90%' src="{{ asset('back/app-assets/images/pages/login.png') }}" alt="admin logo">--}}
{{--                            </div>--}}
                            <div id="main-card" class="col-lg-12 col-12 p-0">
                                <div class="card mb-0 px-2">
                                    <div class="logo-wrap text-center mt-2">
                                        <a href="{{ route('front.index') }}">
                                            <img src="{{ option('info_logo', theme_asset('img/logo.png')) }}" alt="{{ option('info_site_title', 'او پی شاپ') }}">
                                        </a>
                                    </div>
                                    <div class="card-header pb-1">
                                        <div class="card-title">
                                            <h4 class="mb-0">به {{ option('info_site_title', 'او پی شاپ') }} خوش آمدید!</h4>
                                        </div>
                                    </div>
                                    <p class="px-2">وارد حساب خود شده و ماجراجویی را شروع کنید.</p>
                                    <div class="card-content mb-2">
                                        <div class="card-body pt-1">
                                            <form id="login-form" method="POST" action="{{ route('admin.login-submit') }}">
                                                @csrf
                                                <label for="user-name" class="form-label">نام کاربری</label>
                                                <fieldset class="form-label-group form-group position-relative has-icon-left">
                                                    <input type="text" class="form-control" id="user-name" placeholder="نام کاربری را وارد کنید" name="username" value="{{ old('username') }}">
                                                    <div class="form-control-position">
                                                        <i class="feather icon-user"></i>
                                                    </div>
                                                   {{-- <label for="user-name">نام کاربری را وارد کنید</label>--}}
                                                </fieldset>

                                                <label for="user-password" class="form-label">رمز عبور</label>
                                                <fieldset class="form-label-group position-relative has-icon-left">
                                                    <input type="password" class="form-control" id="user-password" name="password" placeholder="رمز عبور را وارد کنید">
                                                    <div class="form-control-position">
                                                        <i class="feather icon-lock"></i>
                                                    </div>
                                                   {{-- <label for="user-password">رمز عبور را وارد کنید</label>--}}
                                                </fieldset>
                                                <div class="form-group d-flex justify-content-between align-items-center">
                                                    <div class="text-left">
                                                        <fieldset class="checkbox">
                                                            <div class="vs-checkbox-con vs-checkbox-primary">
                                                                <input type="checkbox" name="remember" {{ old('username') ? 'checked' : '' }}>
                                                                <span class="vs-checkbox">
                                                                    <span class="vs-checkbox--check">
                                                                        <i class="vs-icon feather icon-check"></i>
                                                                    </span>
                                                                </span>
                                                                <span>مرا بخاطر بسپار</span>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <button type="submit" class="w-100 btn btn-primary float-right btn-inline">ورود به سیستم</button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
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
    <script>
        var redirect_url = '{{ Redirect::intended()->getTargetUrl() }}';

    </script>
    @if(session('admin_login_error'))
        <script>
            toastr.error(
                '<?= session('admin_login_error') ?>',
                '',
                {
                    positionClass: 'toast-bottom-left',
                    containerId: 'toast-bottom-left'
                }
            );
        </script>
    @endif
    @error('username')
    <script>
        toastr.error(
            '<?= $message ?>',
            '',
            {
                positionClass: 'toast-top-right',
                containerId: 'toast-bottom-left'
            }
        );
    </script>
    @enderror
    @error('password')
    <script>
        toastr.error(
            '<?= $message ?>',
            '',
            {
                positionClass: 'toast-top-right',
                containerId: 'toast-bottom-left'
            }
        );
    </script>
    @enderror
    {{-- <script src="{{ asset('back/assets/js/pages/login.js') }}"></script>--}}
@endpush
@php
    session()->forget('admin_login_error')
@endphp
