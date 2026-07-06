@extends('front::auth.layouts.master', ['title' => 'ورود به سایت'])

@php
    $redirect_url = request("redirect") ?: \Illuminate\Support\Facades\Redirect::intended()->getTargetUrl();
  $params = null;
        if (request('ref')) {
            $params = "?ref=" . request('ref');
        }
@endphp

@section('style')
    <style>
        .dk-btn:before{
            right: -97px;
        }
        #main .account-box .content-account {
            padding: 10px 30px 30px;
        }
    </style>
@endsection
@section('content')
    <div class="col-lg-4 col-md-6 col-xs-12 mx-auto">
        <div class="account-box">
            <a href="/" class="logo-account">
                <img src="{{ option('info_logo', theme_asset('img/logo.png')) }}" alt="{{ option('info_site_title', 'او پی شاپ') }}">

            </a>
            <span class="account-head-line">ورود | ثبت‌نام</span>
            <div class="content-account">
                <form action="{{ route('front.user.CheckMobileEmail').$params }}" id="login" method="post">
                    @csrf
                    <label for="email-phone">سلام!<br>

                        لطفا شماره موبایل یا ایمیل خود را وارد کنید</label>
                    <input name="username" type="text" id="email-phone" value="{{old('username')}}" class="input-email-account" placeholder="" autofocus required>
                    @error('username')
                    <span class="invalid-feedback" role="alert" style="margin: 10px 0;">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                    <div class="parent-btn">
                        <button class="dk-btn dk-btn-info text-center mt-5">
                            ورود
                        </button>
                    </div>
                    <label class="mt-3">ورود شما به معنای پذیرش شرایط {{ option('info_site_title', 'او پی شاپ') }} وقوانین حریم‌خصوصیاست

                    </label>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        var redirect_url = '{{ $redirect_url }}';
    </script>

    <script src="{{ theme_asset('js/pages/login.js') }}"></script>
@endpush
