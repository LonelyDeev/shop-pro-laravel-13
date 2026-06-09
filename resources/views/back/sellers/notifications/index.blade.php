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
                                    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.show', ['seller' => $seller]) }}">{{ $seller->fullname }}</a></li>
                                    <li class="breadcrumb-item active">لیست اعلان ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($notifications->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست اعلان ها</h4>
                            <a href="{{route('admin.sellers.notification.create',['seller'=>$seller])}}"><div class="btn personal-success-btn uk-margin-remove">
                                    ارسال پیام یا اعلان جدید
                                    <i class="fa-solid fa-plus mr-0-5"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان</th>
                                            <th>پیام </th>
                                            <th>اولویت</th>
                                            <th class="text-center">وضعیت بازدید</th>
                                            <th class="text-center" style='width: 150px'>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($notifications as $notification)
                                            <tr>

                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $notification->title }}</td>
                                                <td>{!! $notification->message !!}</td>
                                                <td>
                                                    <div class="badge badge-{{$notification->priorityText()['color']}}">{{ $notification->priorityText()['title'] }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @php $read=\Illuminate\Support\Facades\DB::table('notification_manage_users')->where(['notification_manage_id'=>$notification->id,'seller_id'=>$seller->id])->first()->read; @endphp
                                                    @if($read)
                                                        <i class="fa-regular fa-eye" title="seen"></i>
                                                    @endif

                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $notification->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $notification->id }}">
                                                            <a class="dropdown-item" href="{{ route('admin.sellers.notification.show', ['seller'=>$seller,'notification' => $notification]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                            <div class="dropdown-divider"></div>
                                                            <button class="dropdown-item btn-delete"  data-action="{{ route('admin.notifications.destroy', ['notification' => $notification]) }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست اعلان ها</h4>
                            <a href="{{route('admin.sellers.notification.create',['seller'=>$seller])}}"><div class="btn personal-success-btn uk-margin-remove">
                                    ارسال پیام یا اعلان جدید
                                    <i class="fa-solid fa-plus mr-0-5"></i>
                                </div>
                            </a>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                {{ $notifications->links() }}

            </div>
        </div>
    </div>

    {{-- delete ticket modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف پیام دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="ticket-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/notifications/index.js') }}"></script>
@endpush
