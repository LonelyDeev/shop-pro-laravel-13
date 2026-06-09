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
                                            <th class="text-center">عملیات</th>
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
                                                    @php $read=\Illuminate\Support\Facades\DB::table('notification_manage_users')->where(['notification_manage_id'=>$notification->id,'user_id'=>$user->id])->first()->read; @endphp
                                                    @if($read)
                                                        <i class="fa-regular fa-eye" title="seen"></i>
                                                    @endif

                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.users.notification.show', ['user'=>$user,'notification' => $notification]) }}" class="btn btn-info waves-effect waves-light">مشاهده</a>
                                                    <button type="button" data-action="{{ route('admin.notifications.destroy', ['notification' => $notification]) }}" class="btn btn-danger waves-effect waves-light btn-delete"  data-toggle="modal" data-target="#delete-modal">حذف</button>
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
                    با حذف صفحه دیگر قادر به بازیابی آن نخواهید بود
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
