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
                                    <li class="breadcrumb-item">مدیریت پیام ها
                                    </li>

                                    <li class="breadcrumb-item active">تنظیمات پیام تبریک تولد
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title"> تنظیمات پیام تبریک تولد</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="card-body">
                                <form id="birthday-create-form" action="{{ route('admin.messages.birthday.store') }}">
                                    @csrf


                                    <div class="row">
                                        <div class="col-md-6 offset-md-3 col-12">
                                            <div class='row'>
                                                <fieldset class="checkbox col-md-12" title='در صورت غیرفعال بودن، پیام ارسال نخواهد شد'>
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="user_birthday_sms_send" {{option('user_birthday_sms_send') ? 'checked' : ''}}  value="1" >
                                                        <span class="vs-checkbox">
                                                                    <span class="vs-checkbox--check">
                                                                        <i class="vs-icon feather icon-check"></i>
                                                                    </span>
                                                                </span>
                                                        <span>ارسال پیام تبریک تولد</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="col-md-6 offset-md-3 col-12 mt-2">
                                            <div class="form-group">
                                                <label>کد پترن تبریک تولد</label>
                                                <input type="text" class="form-control ltr " value='{{option('user_birthday_pattern_code')}}' name="user_birthday_pattern_code" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 offset-md-3 col-12">
                                            <div class="form-group">
                                                <label>متن نمونه ایجاد پترن</label>
                                                <textarea readonly class="form-control sms_on_user_register" rows="4">%fullname% عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید و با تکیه بر تجربه های سال های گذشته سال های آتی زندگی را به بهترین شکل ممکن بگذرانید تولدتان مبارک.&#13;&#10 {{ option('info_site_title') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <h6>پیام تبریک به این کاربر ها ارسال می شود</h6>

                                    <div class='users mb-4'>
                                        <div class='row  mt-2'>
                                            @foreach($users as $user)
                                                <div class="col-md-6">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="users[]" {{$user->notification ? 'checked' : ''}}  value="{{$user->id}}" >
                                                        <span class="vs-checkbox">
                                                                    <span class="vs-checkbox--check">
                                                                        <i class="vs-icon feather icon-check"></i>
                                                                    </span>
                                                                </span>
                                                        <span>{{$user->full_name .' '. $user->mobile}} <span>(<span>{{jdate($user->birthday)->format('d-m-Y')}}</span>)</span></span>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light"> ذخیره تنظیمات</button>
                                        </div>
                                    </div>
                                </form>







                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!--/ Description -->

        </div>
    </div>
@endsection
@include('back.partials.plugins', ['plugins' => ['jquery.validate']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/messages/birthday.js') }}?v=2"></script>
@endpush
