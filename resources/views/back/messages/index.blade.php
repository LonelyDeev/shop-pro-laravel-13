@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/messages/index.css')}}">
@endpush
@section('content')
    <div class="app-content content msg-page">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            {{-- Breadcrumbs --}}
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">مدیریت پیام‌ها</li>
                                    <li class="breadcrumb-item active">ارسال پیام</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                {{-- Page title --}}
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="msg-page-icon"><i class="feather icon-send"></i></div>
                    <div>
                        <h3 class="mb-0 font-weight-bolder">ارسال پیام</h3>
                        <small class="text-muted">یک پیام جدید به کاربران انتخابی یا همه‌ی کاربران ارسال کنید.</small>
                    </div>
                </div>

                @php
                    $totalMessages = method_exists($messages, 'total') ? $messages->total() : $messages->count();
                    $sentCount     = $messages->where('status_send', 'sent')->count();
                    $pendingCount  = $messages->whereIn('status_send', ['pending', 'failed'])->count();
                @endphp

                {{-- Stat cards --}}
                <div class="row match-height mb-2">
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-teal">
                            <div class="msg-stat-icon"><i class="feather icon-mail"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ $totalMessages }}</span>
                                <span class="msg-stat-label">کل پیام‌ها</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-sky">
                            <div class="msg-stat-icon"><i class="feather icon-users"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ count($users) }}</span>
                                <span class="msg-stat-label">کاربران</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-emerald">
                            <div class="msg-stat-icon"><i class="feather icon-check-circle"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ $sentCount }}</span>
                                <span class="msg-stat-label">ارسال شده</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-amber">
                            <div class="msg-stat-icon"><i class="feather icon-clock"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ $pendingCount }}</span>
                                <span class="msg-stat-label">در انتظار / ناموفق</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create card --}}
                <section id="description" class="card msg-card">
                    <div class="card-header msg-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="msg-card-badge"><i class="feather icon-plus"></i></span>
                            <div>
                                <h4 class="card-title mb-0">ارسال پیام جدید</h4>
                                <small class="text-muted">فیلدهای عنوان، توضیحات و حداقل یک کانال ارسال الزامی هستند.</small>
                            </div>
                        </div>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form id="messages-create-form" action="{{ route('admin.messages.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Title --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group">
                                            <label>عنوان <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" placeholder="مثلاً: اطلاع‌رسانی رویداد ویژه" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- Recipients --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group">
                                            <label>کاربر مربوطه</label>
                                            <select id="users" name="users[]" class="form-control users" multiple>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->fullname . ' (id=>' . $user->id . ' mobile=>' . $user->mobile . ')' }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">اگر این فیلد را خالی بگذارید، پیام به همه‌ی کاربران ارسال می‌شود.</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group">
                                            <label>توضیحات <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="description" rows="3" placeholder="متن پیام…" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Channels (toggle cards, pure CSS) --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group mb-0">
                                            <label class="d-block mb-1">کانال‌های ارسال</label>
                                            <div class="channel-grid">
                                                <label class="channel-card" tabindex="0">
                                                    <input type="checkbox" name="email" value="1" class="channel-input">
                                                    <span class="channel-body">
                                                        <span class="channel-ico ch-email"><i class="feather icon-mail"></i></span>
                                                        <span class="channel-text">
                                                            <span class="channel-name">ایمیل</span>
                                                            <span class="channel-desc">ارسال از طریق ایمیل</span>
                                                        </span>
                                                        <i class="feather icon-check-circle channel-check"></i>
                                                    </span>
                                                </label>

                                                <label class="channel-card" tabindex="0">
                                                    <input type="checkbox" name="sms" value="1" class="channel-input">
                                                    <span class="channel-body">
                                                        <span class="channel-ico ch-sms"><i class="feather icon-message-square"></i></span>
                                                        <span class="channel-text">
                                                            <span class="channel-name">پیامک</span>
                                                            <span class="channel-desc">ارسال با پترن پیامکی</span>
                                                        </span>
                                                        <i class="feather icon-check-circle channel-check"></i>
                                                    </span>
                                                </label>

                                                <label class="channel-card" tabindex="0">
                                                    <input type="checkbox" name="notification" value="1" class="channel-input">
                                                    <span class="channel-body">
                                                        <span class="channel-ico ch-notif"><i class="feather icon-bell"></i></span>
                                                        <span class="channel-text">
                                                            <span class="channel-name">نوتیفیکیشن</span>
                                                            <span class="channel-desc">اعلان درون‌برنامه‌ای</span>
                                                        </span>
                                                        <i class="feather icon-check-circle channel-check"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SMS pattern section (toggled by create.js on sms change) --}}
                                <div id="pattern-code-div" class="row mt-2 d-none">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="msg-pattern-box">
                                            <div class="alert alert-info msg-alert" role="alert">
                                                <i class="feather icon-info ml-1 align-middle"></i>
                                                <span>پیامک فقط با پترن ارسال می‌شود؛ عنوان و توضیحات در پیامک درج نمی‌شود.</span>
                                            </div>

                                            <div class="form-group">
                                                <label>کد پترن برای ارسال پیام</label>
                                                <input type="text" class="form-control ltr" value="{{ option('user_message_pattern_code') }}" name="user_message_pattern_code">
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                                <h6 class="mb-0">اگر کد پترن شما دارای متغیر است، روی دکمه‌ی زیر کلیک کنید.</h6>
                                                <button type="button" class="btn btn-outline-teal btn-sm waves-effect waves-light add-variable-item">
                                                    <i class="feather icon-plus"></i> افزودن متغیر
                                                </button>
                                            </div>

                                            <div id="variables" class="mt-1">
                                                {{-- Hidden template row (cloned by create.js) --}}
                                                <div class="variable-item row d-none variable-template">
                                                    <div class="col-5">
                                                        <div class="form-group mb-0">
                                                            <label>اسم متغییر</label>
                                                            <input type="text" class="form-control ltr" name="variables[]" placeholder="code">
                                                        </div>
                                                    </div>
                                                    <div class="col-5">
                                                        <div class="form-group mb-0">
                                                            <label>مقدار</label>
                                                            <input type="text" class="form-control" name="values[]" placeholder="12345">
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <button type="button" class="btn btn-flat-danger waves-effect waves-light remove-variable-item w-100" style="margin-top: 35px !important;">
                                                            <i class="feather icon-trash-2"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info msg-alert mt-2" role="alert">
                                                <i class="feather icon-info ml-1 align-middle"></i>
                                                <span>در صورتی که پترن شما متغیر ندارد، حتماً متغیرهای موجود را حذف کنید تا پیام ارسال شود.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="row mt-1">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <button type="submit" class="btn btn-msg-primary waves-effect waves-light">
                                            <i class="feather icon-send"></i> ارسال پیام
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                {{-- Sent messages --}}
                <div class="list-reviews mt-2">
                    @if ($messages->count())
                        <section class="card msg-card">
                            <div class="card-header msg-card-header">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="msg-card-badge bg-soft-emerald"><i class="feather icon-list"></i></span>
                                        <h4 class="card-title mb-0">پیام‌های ارسال شده</h4>
                                    </div>
                                    <span class="msg-count-chip">{{ $totalMessages }} پیام</span>
                                </div>
                            </div>
                            <div class="card-content" id="main-card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table msg-table mb-0">
                                            <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>عنوان</th>
                                                <th>متن</th>
                                                <th class="text-center">کانال‌ها</th>
                                                <th class="text-center">تاریخ</th>
                                                <th class="text-center">وضعیت</th>
                                                <th class="text-center">عملیات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($messages as $message)
                                                <tr id="review-{{ $message->id }}-tr">
                                                    <td class="text-center text-muted">{{ $message->id }}</td>
                                                    <td style="max-width: 200px">
                                                        <span class="d-inline-block text-truncate" style="max-width:200px">{{ $message->title }}</span>
                                                    </td>
                                                    <td style="max-width: 200px">
                                                        <span class="d-inline-block text-muted text-truncate" style="max-width:200px">{{ $message->description }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="channel-pills">
                                                            @isset($message->email)
                                                                @if ($message->email) <span class="channel-pill ch-email">ایمیل</span> @endif
                                                            @endisset
                                                            @isset($message->sms)
                                                                @if ($message->sms) <span class="channel-pill ch-sms">پیامک</span> @endif
                                                            @endisset
                                                            @isset($message->notification)
                                                                @if ($message->notification) <span class="channel-pill ch-notif">نوتیف</span> @endif
                                                            @endisset
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-muted text-nowrap">{{ jdate($message->created_at) }}</td>
                                                    <td class="text-center">
                                                        @if ($message->status_send == 'pending')
                                                            <span class="status-pill status-pending"><span class="dot"></span> منتظر ارسال</span>
                                                        @elseif ($message->status_send == 'sent')
                                                            <span class="status-pill status-sent"><span class="dot"></span> ارسال شده</span>
                                                        @else
                                                            <span class="status-pill status-failed"><span class="dot"></span> ارسال نشده</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center text-nowrap">
                                                        <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-icon-msg btn-view waves-effect waves-light" data-toggle="tooltip" title="مشاهده">
                                                            <i class="feather icon-eye"></i>
                                                        </a>
                                                        <button data-review="{{ $message->id }}" data-action="{{ route('admin.messages.destroy', $message) }}" type="button" class="btn btn-icon-msg btn-del waves-effect waves-light btn-delete" data-toggle="modal" data-target="#delete-modal" title="حذف">
                                                            <i class="feather icon-trash-2"></i>
                                                        </button>
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
                        <section class="card msg-card">
                            <div class="card-body">
                                <div class="msg-empty">
                                    <div class="msg-empty-icon"><i class="feather icon-inbox"></i></div>
                                    <h5 class="mb-0">چیزی برای نمایش وجود ندارد!</h5>
                                    <p class="text-muted mb-0">با ارسال اولین پیام، لیست اینجا نمایش داده می‌شود.</p>
                                </div>
                            </div>
                        </section>
                    @endif
                    {{ $messages->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content msg-modal">
                <div class="modal-header msg-modal-header">
                    <h4 class="modal-title"><i class="feather icon-alert-triangle text-danger"></i> آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف پیام، دیگر قادر به بازیابی آن نخواهید بود.
                </div>
                <div class="modal-footer">
                    <form action="#" id="messages-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn btn-light waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light"><i class="feather icon-trash-2"></i> بله، حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/messages/create.js') }}?v=3"></script>

@endpush
