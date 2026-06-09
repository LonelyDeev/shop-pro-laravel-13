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
                                    <li class="breadcrumb-item">مدیریت تیکت ها
                                    </li>
                                    <li class="breadcrumb-item active">لیست تیکت ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($tickets->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست تیکت ها</h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>موضوع</th>
                                                <th>نوع کاربر </th>
                                                <th>کاربر مربوطه</th>
                                                <th>اولویت</th>
                                                <th>وضعیت</th>
                                                <th class="text-center" style='width: 150px'>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tickets as $ticket)
                                                <tr>

                                                    <td>{{ $ticket->subject }}</td>
                                                    <td>
                                                        @if($ticket->user_id)
                                                           کاربر سایت
                                                        @else
                                                            فروشنده
                                                        @endif

                                                    </td>
                                                    <td>
                                                        @if($ticket->user_id)
                                                            <a target='_blank' href='{{route('admin.users.show',['user'=>$ticket->user])}}'>{{ $ticket->user->fullname ?: $ticket->user->mobile }}</a>
                                                        @else
                                                            <a target='_blank' href='{{route('admin.sellers.show',['seller'=>$ticket->seller])}}'>{{ $ticket->seller->fullname }}</a>
                                                        @endif

                                                    </td>
                                                    <td>{{ $ticket->priorityText() }}</td>
                                                    <td>{{ $ticket->statusText() }}</td>

                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $ticket->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $ticket->id }}">
                                                                <a class="dropdown-item"  href="{{ route('admin.tickets.show', ['ticket' => $ticket]) }}"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                <div class="dropdown-divider"></div>
                                                                 <button class="dropdown-item btn-delete" data-action="{{ route('admin.tickets.destroy', ['ticket' => $ticket]) }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

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
                            <h4 class="card-title">لیست تیکت ها</h4>
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
                {{ $tickets->links() }}

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
    <script src="{{ asset('back/assets/js/pages/tickets/index.js') }}"></script>
@endpush
