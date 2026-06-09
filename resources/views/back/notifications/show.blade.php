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
                                <form class="form" id="notifications-create-form" data-redirect="{{ route('admin.notifications.index') }}" action="{{ route('admin.notifications.update', ['notification' => $notification]) }}" method="post">
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
                                            <div class="col-md-12 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" class="relevant-user" name="users" {{ count($notification->users) ? 'checked' : '' }}>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>ارسال به کاربران سایت</span>
                                                    </div>
                                                </fieldset>
                                            </div>

                                            <div class="col-md-12 {{ count($notification->users) ? '' : 'd-none' }}" id="users-div">
                                                <div class="form-group">
                                                    <label>کاربر مربوطه</label>
                                                    <select id="user_id" name="user_id[]" class="form-control users" multiple>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}" @if($notification->allUsers=="0") {{ ($notification->users()->find($user->id)) ? 'selected' : '' }} @endif>{{$user->fullname .' (id=>'.$user->id.' mobile=>'.$user->mobile.')'}}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($notification->allUsers)
                                                        <label id="message-error" class="invalid-feedback animated fadeInDown" for="message" style="display: inline-block;">ارسال شده به همه کاربران سایت.</label>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-2">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" class="relevant-user" name="sellers" {{ count($notification->sellers) ? 'checked' : '' }}>
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>ارسال به فروشندگان</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-12 {{ count($notification->sellers) ? '' : 'd-none' }}" id="sellers-div">
                                                <div class="form-group">
                                                    <label>فروشنده مربوطه</label>
                                                    <select id="seller_id" name="seller_id[]" class="form-control sellers" multiple>
                                                        @foreach ($sellers as $seller)
                                                            <option value="{{ $seller->id }}" @if($notification->allSellers=="0") {{ ($notification->sellers()->find($seller->id)) ? 'selected' : '' }} @endif>{{$seller->fullname .' (id=>'.$seller->id.' mobile=>'.$seller->mobile.')'}}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($notification->allSellers)
                                                        <label id="message-error" class="invalid-feedback animated fadeInDown" for="message" style="display: inline-block;">ارسال شده به همه فروشندگان.</label>
                                                    @endif

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
                                            @can('notifications.update')
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ویرایش اعلان</button>
                                            </div>
                                            @endcan
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

                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <section class="card">
                            <div class="card-header">
                                <h4 class="card-title">لیست کاربران ارسال شده</h4>
                            </div>

                                <div class="card-content">
                                    @if(count($showUsers))
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>نام و نام خانوادگی</th>
                                                    <th>موبایل</th>
                                                    <th class="text-center">وضعیت</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($showUsers as $item)
                                                    <tr>
                                                        <td>{{$item->id}}</td>
                                                        <td>{{$item->fullname}}<a class="mr-1" href="{{route('admin.users.show',['user'=>$item])}}" target="_blank"><i class="feather icon-external-link"></i></a></td>
                                                        <td>{{$item->mobile}}</td>
                                                        <td class="text-center">
                                                            @php $read=\Illuminate\Support\Facades\DB::table('notification_manage_users')->where(['notification_manage_id'=>$item->pivot->notification_manage_id,'user_id'=>$item->id])->first()->read; @endphp
                                                            @if($read)
                                                                <i class="fa-regular fa-eye" title="seen"></i>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                @endforeach


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @else
                                        <div class="card-body">
                                            <div class="card-text">
                                                <p>این اعلان به کاربران سایت ارسال نشده است!</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>




                        </section>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <section class="card">
                            <div class="card-header">
                                <h4 class="card-title">لیست فروشندگان ارسال شده</h4>
                            </div>

                                <div class="card-content">
                                    @if(count($showSellers))
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>نام فروشگاه</th>
                                                    <th>نام و نام خانوادگی</th>
                                                    <th>موبایل</th>
                                                    <th class="text-center">وضعیت</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($showSellers as $item)
                                                    <tr>
                                                        <td>{{$item->id}}</td>
                                                        <td><a class="mr-1" href="{{route('admin.sellers.show',['seller'=>$item])}}" target="_blank"><i class="feather icon-external-link"></i></a>{{get_seller_info($item->id)->business_name}}</td>
                                                        <td>{{$item->fullname}}</td>
                                                        <td>{{$item->mobile}}</td>
                                                        <td class="text-center">
                                                            @php $read=\Illuminate\Support\Facades\DB::table('notification_manage_users')->where(['notification_manage_id'=>$item->pivot->notification_manage_id,'seller_id'=>$item->id])->first()->read; @endphp
                                                            @if($read)
                                                                <i class="fa-regular fa-eye" title="seen"></i>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                @endforeach


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    @else
                                        <div class="card-body">
                                            <div class="card-text">
                                                <p>این اعلان به فروشندگان ارسال نشده است!</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>




                        </section>
                    </div>
                    <div class="col-12">
                        <div class="card-footer text-muted text-right">
                            @if(count($showUsers->links()->elements[0])>= count($showSellers->links()->elements[0]))
                                {{$showUsers->links()}}
                            @else
                                {{$showSellers->links()}}
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/notifications/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/notifications/create.js') }}"></script>
@endpush
