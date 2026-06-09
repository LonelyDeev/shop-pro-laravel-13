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
                                    <li class="breadcrumb-item">تنظیمات
                                    </li>
                                    <li class="breadcrumb-item active">تنظیمات دیگر
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- users edit start -->
                <section class="users-edit">
                    <div class="card">
                        <div id="main-card" class="card-content">
                            <div class="card-body">
                                <div class="tab-content">
                                    <form id="others-form" action="{{ route('admin.settings.others') }}" method="POST">
                                        <h4 class="mt-2"> چند فروشندگی</h4>
                                        <div class="row">
                                            <div class="col-md-3 col-12">
                                                <div class="form-group">
                                                    <label>سیستم چند فروشندگی</label>
                                                    <select name="multi_vendor_system_status" class="form-control">
                                                        <option value="true" {{ option('multi_vendor_system_status') == 'true' ? 'selected' : '' }}>فعال</option>
                                                        <option value="false" {{ option('multi_vendor_system_status') == 'false' ? 'selected' : '' }}>غیرفعال</option>
                                                    </select>
                                                </div>
                                            </div>


                                        </div>

{{--                                        <h4 class="mt-2">تنظیمات قیمت ها</h4>--}}
{{--                                        <div class="row">--}}
{{--                                            <div class="col-md-3 col-12">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label>انتخاب ارز پیش فرض</label>--}}
{{--                                                    <select name="default_currency_id" class="form-control">--}}
{{--                                                        <option value="">تومان (پیش فرض)</option>--}}
{{--                                                        @foreach ($currencies as $currency)--}}
{{--                                                            <option value="{{ $currency->id }}" {{ option('default_currency_id') == $currency->id ? 'selected' : '' }}>{{ $currency->title }}</option>--}}
{{--                                                        @endforeach--}}
{{--                                                    </select>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="col-md-3 col-12">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label>گرد کردن</label>--}}
{{--                                                    <select name="default_rounding_amount" class="form-control">--}}
{{--                                                        <option value="no" {{ option('default_rounding_amount', 'no') == 'no' ? 'selected' : '' }}>خیر</option>--}}
{{--                                                        <option value="100" {{ option('default_rounding_amount') == 100 ? 'selected' : '' }}>100 تومان</option>--}}
{{--                                                        <option value="1000" {{ option('default_rounding_amount') == 1000 ? 'selected' : '' }}>1000 تومان</option>--}}
{{--                                                        <option value="10000" {{ option('default_rounding_amount') == 10000 ? 'selected' : '' }}>10000 تومان</option>--}}
{{--                                                        <option value="100000" {{ option('default_rounding_amount') == 100000 ? 'selected' : '' }}>100000 تومان</option>--}}
{{--                                                    </select>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="col-md-3">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label>نحوه گرد کردن</label>--}}
{{--                                                    <select name="default_rounding_type" class="form-control">--}}
{{--                                                        <option value="close" {{ option('default_rounding_type', 'close') == 'close' ? 'selected' : '' }}>نزدیک</option>--}}
{{--                                                        <option value="up" {{ option('default_rounding_type') == 'up' ? 'selected' : '' }}>رو به بالا</option>--}}
{{--                                                        <option value="down" {{ option('default_rounding_type') == 'down' ? 'selected' : '' }}>رو به پایین</option>--}}
{{--                                                    </select>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                        </div>--}}

                                        <h4 class="mt-2">تنظیمات فاکتور سفارشات</h4>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <fieldset class="form-group">
                                                    <label for="">لوگو</label>
                                                    <div class="custom-file">
                                                        <input type="file" accept="image/*" name="factor_logo" class="custom-file-input">
                                                        <label class="custom-file-label" for="">{{ option('factor_logo') }}</label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-3">
                                                <label>عنوان فاکتور</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_title" class="form-control" value="{{ option('factor_title', option('info_site_title')) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>فروشنده</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_seller_name" class="form-control" value="{{ option('factor_seller_name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شناسه ملی</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_national_code" class="form-control" value="{{ option('factor_national_code') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شناسه ثبت</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_registeration_id" class="form-control" value="{{ option('factor_registeration_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>شماره اقتصادی</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="factor_economical_number" class="form-control" value="{{ option('factor_economical_number') }}">
                                                </div>
                                            </div>

                                        </div>

                                       {{-- <h4 class="mt-2">تنظیمات مربوط به کاربران</h4>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>اعتبار هدیه ثبت نام کاربر</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="user_register_gift_credit" class="form-control" min="0" value="{{ option('user_register_gift_credit', 0) }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label>فعال کردن امکان معرفی افراد</label>
                                                <div class="input-group mb-75">
                                                    <select name="user_refrral_enable" class="form-control">
                                                        <option value="0" {{ option('user_refrral_enable', 0) == 0 ? 'selected' : '' }}>خیر</option>
                                                        <option value="1" {{ option('user_refrral_enable', 1) == 1 ? 'selected' : '' }}>بله</option>
                                                    </select>
                                                </div>


                                            </div>

                                            <div class="col-md-3">
                                                <label> مقدار تخفیف معرفی کننده به درصد</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="owner_refrral_amount" class="form-control" min="0" value="{{ option('owner_refrral_amount', 0) }}">
                                                </div>

                                            </div>
                                            <div class="col-md-3">

                                                <label> مقدار تخفیف معرفی شونده به درصد</label>
                                                <div class="input-group mb-75">
                                                    <input type="number" name="user_refrral_amount" class="form-control" min="0" value="{{ option('user_refrral_amount', 0) }}">
                                                </div>

                                            </div>
                                        </div>--}}

                                        <h4 class="mt-2">تنظیمات تصاویر</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>درصد بهینه سازی و کم کردن حجم (1-99) </label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="optimizeImage" class="form-control ltr" value="{{ option('optimizeImage', '10')}}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>تبدیل تصاویر به</label>
                                                    <select name="changePhotoFormat" class="form-control">
                                                        <option value="webp" {{ option('changePhotoFormat', 'webp') == 'webp' ? 'selected' : '' }}>webp</option>
                                                        <option value="jpg" {{ option('changePhotoFormat') == 'jpg' ? 'selected' : '' }}>jpg</option>
                                                        <option value="png" {{ option('changePhotoFormat') == 'png' ? 'selected' : '' }}>png</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>وضعیت واترمارک</label>
                                                    <select name="watermarkStatus" class="form-control">
                                                        <option value="true" {{ option('watermarkStatus') == 'true' ? 'selected' : '' }}>فعال</option>
                                                        <option value="false" {{ option('watermarkStatus') == 'false' ? 'selected' : '' }}>غیرفعال</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <fieldset class="form-group">
                                                    <label for="">تصویر واترمارک </label>
                                                    <div class="custom-file">
                                                        <input type="file" accept="image/*" name="watermarkImage" class="custom-file-input">
                                                        <label class="custom-file-label" for="">{{ option('watermarkImage') }}</label>
                                                    </div>
                                                </fieldset>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>موقعیت واترمارک</label>
                                                    <select name="watermarkImagePosition" class="form-control">
                                                        <option value="top-left" {{ option('watermarkImagePosition') == 'top-left' ? 'selected' : '' }}>🔼 بالا چپ</option>
                                                        <option value="top" {{ option('watermarkImagePosition') == 'top' ? 'selected' : '' }}>🔼 بالا وسط</option>
                                                        <option value="top-right" {{ option('watermarkImagePosition') == 'top-right' ? 'selected' : '' }}>🔼 بالا راست</option>

                                                        <option value="left" {{ option('watermarkImagePosition') == 'left' ? 'selected' : '' }}>⬅️ وسط چپ</option>
                                                        <option value="center" {{ option('watermarkImagePosition') == 'center' ? 'selected' : '' }}>🎯 وسط</option>
                                                        <option value="right" {{ option('watermarkImagePosition') == 'right' ? 'selected' : '' }}>➡️ وسط راست</option>
watermarkImagePlace
                                                        <option value="bottom-left" {{ option('watermarkImagePosition') == 'bottom-left' ? 'selected' : '' }}>🔽 پایین چپ</option>
                                                        <option value="bottom" {{ option('watermarkImagePosition') == 'bottom' ? 'selected' : '' }}>🔽 پایین وسط</option>
                                                        <option value="bottom-right" {{ option('watermarkImagePosition') == 'bottom-right' ? 'selected' : '' }}>🔽 پایین راست</option>
                                                    </select>

                                                </div>
                                            </div>

                                        </div>


                                        <h4 class="mt-2">تنظیمات pusher</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_ID</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_ID" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.app_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_KEY</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_KEY" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.key') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_SECRET</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_SECRET" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.secret') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PUSHER_APP_CLUSTER</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="PUSHER_APP_CLUSTER" class="form-control ltr" value="{{ config('broadcasting.connections.pusher.options.cluster') }}">
                                                </div>
                                            </div>

                                        </div>



                                        <h4 class="mt-2">تنظیمات هوش مصنویی</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>توکن</label>
                                                <div class="input-group mb-75">
                                                    <input type="text" name="AI_TOKEN_KEY" class="form-control ltr" value="{{ option('AI_TOKEN_KEY', null) }}">
                                                </div>
                                            </div>
                                            <p class="card-text mt-2 pt-1"><i class="feather icon-info mr-1 align-middle"></i><span class="text-info">برای دیافت توکن، ابتدا باید در پنل  <a target='_blank' href='https://ai.webtpro.ir'>webLakAi</a> ثبت نام کنید.</span></p>

                                        </div>

                                        <div class="row">
                                            <div class="col-12 d-flex flex-sm-row flex-column justify-content-end mt-1">
                                                <button type="submit" class="btn btn-primary glow">ذخیره تغییرات</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- users edit ends -->

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/settings/others.js') }}"></script>
@endpush
