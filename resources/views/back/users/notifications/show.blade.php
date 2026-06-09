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
                                    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', ['user' => $user]) }}">{{ $user->fullname }}</a></li>
                                    <li class="breadcrumb-item active">نمایش اعلان ها
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
                        <h4 class="card-title">نمایش اعلان شماره {{$notification->id}}</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form class="form" id="notifications-create-form" data-redirect="{{ route('admin.users.notifications',['user'=>$user]) }}" action="{{ route('admin.users.notification.update', ['notification' => $notification,'user'=>$user]) }}" method="post">
                                    @csrf
                                    @method('put')
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>عنوان</label>
                                                    <input type="text" class="form-control" name="title" value="{{$notification->title}}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>وضعیت اعلان‌</label>
                                                    <select name="priority" class="form-control">
                                                        <option {{$notification->priority=="low" ? 'selected' : ''}} value="low">متوسط</option>
                                                        <option {{$notification->priority=="medium" ? 'selected' : ''}} value="medium">مهم</option>
                                                        <option {{$notification->priority=="high" ? 'selected' : ''}} value="high">خیلی مهم</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="message">پیام</label>
                                                    <textarea id="message" class="form-control" rows="4" name="message">{{$notification->message}}</textarea>
                                                </div>
                                            </div>


                                            <div class="col-md-4 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="popup"  {{$notification->popup ? 'checked' : ''}}>
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
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ویرایش اعلان</button>
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
    <script src="{{ asset('back/assets/js/pages/users/notifications/all.js') }}"></script>
@endpush
