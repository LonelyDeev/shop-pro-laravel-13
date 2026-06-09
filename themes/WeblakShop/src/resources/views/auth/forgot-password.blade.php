@extends('front::auth.layouts.master', ['title' => 'فراموشی رمز عبور'])

@section('content')
    <div class="col-lg-4 col-md-6 col-xs-12 mx-auto">
        <div class="account-box">
            <a href="/" class="logo-account">
                <img src="{{ option('info_logo', theme_asset('img/logo.png')) }}" alt="{{ option('info_site_title', 'او پی شاپ') }}">

            </a>
            <span class="account-head-line">یاد آوری کلمه عبور</span>
            <div class="content-account pb-5">
                <form id="forgot-password-form" data-redirect="{{ route('one-time-login') }}" action="{{ route('password.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="mobile">شماره موبایل خود را وارد کنید</label>
                        <input type="text" name="mobile" id="mobile" class="input-email-account" placeholder="">
                    </div>
                    <div class="form-row mt-4 mt-2">
                        <div class="col-md-8 col-6  ">
                            <div class="form-group">
                                <input type="text" class="input-ui pl-2 captcha" autocomplete="off" name="captcha" placeholder="کد امنیتی" required="">
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mt-2">
                            <img class="captcha w-100" src="{{ captcha_src('flat') }}" alt="captcha">
                        </div>
                    </div>

                    <div class="parent-btn">
                        <button class="dk-btn dk-btn-info">
                            درخواست کد تایید
                            <i class="mdi mdi-lock sign-in"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script src="{{ theme_asset('js/pages/forgot-password.js') }}?v=2"></script>
@endpush
