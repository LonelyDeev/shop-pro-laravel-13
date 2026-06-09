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
                                    <li class="breadcrumb-item active">اعلان ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">

                <section id="statistics-card">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="card">

                                <div class="card-content">
                                    <div class="card-body">
                                        @if($notifications->count())
                                            <ul class="activity-timeline timeline-left list-unstyled">
                                                @foreach ($notifications as $notification)
                                                    @php
                                                        $notification_link = notification_link($notification);
                                                    @endphp

                                                    @if($notification->type == 'OrderPaid')
                                                        @can('orders.view')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-primary">
                                                                <i class="feather icon-plus font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">سفارش جدید ثبت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'SellerRegistered')
                                                        @can('sellers.view')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-success">
                                                                <i class="feather icon-user font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">فروشنده جدید ثبت نام کرد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'SellerEditProfile')
                                                        @can('sellers.update')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-success">
                                                                <i class="feather icon-user font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">فروشنده اطلاعات خود را ویرایش کرد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'UserRegistered')
                                                        @can('users.view')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-success">
                                                                <i class="feather icon-user font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">کاربر جدید ثبت نام کرد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'ContactCreated')
                                                        @can('contacts.index')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'TicketCreated')
                                                        @can('tickets.show')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">تیکت جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'CommentPostCreated')
                                                        @can('comments.index')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'CommentProductCreated')
                                                        @can('comments.index')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                         @endcan
                                                    @elseif($notification->type == 'UserRequestDeposit')
                                                        @can('request_deposit')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'SellerRequestDeposit')
                                                        @can('request_deposit')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'QuestionProductCreated')
                                                        @can('comments.index')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="feather icon-message-square font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">پیام جدید دریافت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'SellerProductCreated')
                                                        @can('sellers.products')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="fa-solid fa-cart-shopping"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">محصول جدید ثبت شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @elseif($notification->type == 'SellerProductUpdate')
                                                        @can('sellers.products')
                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-info">
                                                                <i class="fa-solid fa-cart-shopping"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">محصول ویرایش شد</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                        @endcan
                                                    @endif

                                                    @if ($notification_link)
                                                        <p style='margin-top: -15px;'><a class="" href="{{ $notification_link }}">مشاهده</a></p>
                                                    @endif

                                                @endforeach
                                            </ul>

                                        @else
                                            <p>چیزی برای نمایش وجود ندارد!</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>

                {{ $notifications->links() }}

            </div>
        </div>
    </div>

@endsection
