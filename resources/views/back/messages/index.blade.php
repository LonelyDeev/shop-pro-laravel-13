@extends('back.layouts.master')

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

    {{-- Scoped styles (emerald/teal accent, RTL-friendly) --}}
    <style>
        .msg-page { --msg-accent: #0d9488; --msg-accent-600: #0f766e; --msg-accent-50: #f0fdfa; --msg-accent-100: #ccfbf1; --msg-accent-200: #99f6e4; }
        .msg-page .breadcrumb { background: transparent; padding: 0; }

        .msg-page-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--msg-accent), var(--msg-accent-600)); color: #fff; box-shadow: 0 6px 16px -6px rgba(13,148,136,.55);margin-left: 10px }
        .msg-page-icon i { font-size: 22px; }

        .msg-stat { display: flex; align-items: center; gap: .9rem; background: #fff; border: 1px solid #ededed; border-radius: 14px; padding: 1rem 1.1rem; height: 100%; box-shadow: 0 2px 10px -8px rgba(0,0,0,.18); transition: box-shadow .2s, transform .2s; }
        .msg-stat:hover { box-shadow: 0 10px 24px -14px rgba(0,0,0,.25); transform: translateY(-2px); }
        .msg-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .msg-stat-meta { display: flex; flex-direction: column; line-height: 1.2; }
        .msg-stat-value { font-size: 1.5rem; font-weight: 800; color: #2b2b2b; }
        .msg-stat-label { font-size: .8rem; color: #8a8a8a; }
        .stat-teal .msg-stat-icon { background: #f0fdfa; color: #0d9488; }
        .stat-sky .msg-stat-icon { background: #f0f9ff; color: #0284c7; }
        .stat-emerald .msg-stat-icon { background: #ecfdf5; color: #059669; }
        .stat-amber .msg-stat-icon { background: #fffbeb; color: #d97706; }

        .msg-card { border: 0; border-radius: 16px; box-shadow: 0 4px 24px -16px rgba(0,0,0,.22); overflow: hidden; }
        .msg-card-header { background: linear-gradient(90deg, var(--msg-accent-50), transparent); border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem; }
        .msg-card-badge { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: var(--msg-accent-100); color: var(--msg-accent-600);margin-left: 10px }
        .msg-card-badge i { font-size: 19px; }
        .bg-soft-emerald { background: #ecfdf5 !important; color: #059669 !important; }
        .msg-count-chip { background: var(--msg-accent-50); color: var(--msg-accent-600); border: 1px solid var(--msg-accent-200); border-radius: 999px; padding: .25rem .8rem; font-size: .78rem; font-weight: 700; }

        .channel-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; }
        @media (max-width: 575.98px) { .channel-grid { grid-template-columns: 1fr; } }
        .channel-card { position: relative; display: block; cursor: pointer; margin: 0; }
        .channel-input { position: absolute; opacity: 0; pointer-events: none; }
        .channel-body { display: flex; align-items: center; gap: .7rem; padding: .85rem .9rem; border: 1.5px solid #e7e7e7; border-radius: 12px; background: #fff; transition: all .18s ease; }
        .channel-card:hover .channel-body { border-color: var(--msg-accent-200); }
        .channel-input:checked ~ .channel-body { border-color: var(--msg-accent); background: var(--msg-accent-50); box-shadow: 0 0 0 3px rgba(13,148,136,.12); }
        .channel-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f4f4f5; color: #8a8a8a; transition: all .18s; }
        .channel-ico i { font-size: 18px; }
        .channel-input:checked ~ .channel-body .ch-email { background: #f0f9ff; color: #0284c7; }
        .channel-input:checked ~ .channel-body .ch-sms { background: #f5f3ff; color: #7c3aed; }
        .channel-input:checked ~ .channel-body .ch-notif { background: #fffbeb; color: #d97706; }
        .channel-text { display: flex; flex-direction: column; line-height: 1.25; flex: 1; }
        .channel-name { font-weight: 700; font-size: .92rem; color: #2b2b2b; }
        .channel-desc { font-size: .72rem; color: #9a9a9a; }
        .channel-check { color: var(--msg-accent); opacity: 0; transform: scale(.6); transition: all .18s; }
        .channel-input:checked ~ .channel-body .channel-check { opacity: 1; transform: scale(1); }

        .msg-pattern-box { border: 1.5px solid var(--msg-accent-200); background: var(--msg-accent-50); border-radius: 14px; padding: 1rem; }
        .msg-alert { border: 0; background: #fff; border-radius: 10px; padding: .65rem .8rem; display: flex; align-items: center; gap: .4rem; }
        .msg-alert i { color: var(--msg-accent-600); }
        .btn-outline-teal { color: var(--msg-accent-600); border-color: var(--msg-accent-200); background: #fff; }
        .btn-outline-teal:hover { background: var(--msg-accent); color: #fff; border-color: var(--msg-accent); }
        .btn-msg-primary { background: linear-gradient(135deg, var(--msg-accent), var(--msg-accent-600)); color: #fff; border: 0; padding: .6rem 1.6rem; border-radius: 10px; font-weight: 700; box-shadow: 0 8px 18px -10px rgba(13,148,136,.7); }
        .btn-msg-primary:hover { color: #fff; filter: brightness(1.05); }

        .msg-table thead th { background: #fafbfc; color: #6b6b6b; font-weight: 700; font-size: .82rem; border-bottom: 2px solid #f0f0f0; white-space: nowrap; }
        .msg-table tbody td { vertical-align: middle; border-bottom: 1px solid #f4f4f4; }
        .msg-table tbody tr { transition: background .15s; }
        .msg-table tbody tr:hover { background: #fcfdfd; }

        .status-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .7rem; border-radius: 999px; font-size: .76rem; font-weight: 700; white-space: nowrap; }
        .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
        .status-pending { background: #fffbeb; color: #b45309; } .status-pending .dot { background: #f59e0b; }
        .status-sent { background: #ecfdf5; color: #047857; } .status-sent .dot { background: #10b981; }
        .status-failed { background: #fff1f2; color: #be123c; } .status-failed .dot { background: #f43f5e; }

        .channel-pills { display: flex; flex-wrap: wrap; gap: .25rem; justify-content: center; }
        .channel-pill { display: inline-flex; align-items: center; gap: .25rem; padding: .12rem .5rem; border-radius: 6px; font-size: .7rem; font-weight: 700; }
        .channel-pill.ch-email { background: #f0f9ff; color: #0369a1; }
        .channel-pill.ch-sms { background: #f5f3ff; color: #6d28d9; }
        .channel-pill.ch-notif { background: #fffbeb; color: #b45309; }

        .btn-icon-msg { width: 34px; height: 34px; padding: 0; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #ededed; background: #fff; }
        .btn-icon-msg i { font-size: 16px; }
        .btn-view { color: #047857; } .btn-view:hover { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
        .btn-del { color: #be123c; } .btn-del:hover { background: #fff1f2; border-color: #fecdd3; color: #be123c; }

        .msg-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .4rem; padding: 3rem 1rem; text-align: center; }
        .msg-empty-icon { width: 72px; height: 72px; border-radius: 18px; background: #f5f5f5; color: #b0b0b0; display: flex; align-items: center; justify-content: center; margin-bottom: .5rem; }
        .msg-empty-icon i { font-size: 34px; }

        .msg-modal { border: 0; border-radius: 16px; overflow: hidden; }
        .msg-modal-header { background: #fff5f5; border-bottom: 1px solid #ffe3e3; }
        .msg-modal-header .modal-title { font-size: 1.05rem; display: flex; align-items: center; gap: .5rem; }

        .msg-page .pagination .page-item.active .page-link { background: var(--msg-accent); border-color: var(--msg-accent); }
    </style>
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/messages/create.js') }}?v=3"></script>
    <script>
        $(function() {
            // -------- مدیریت نمایش بخش پترن بر اساس تیک پیامک --------
            var $smsCheckbox = $('input[name="sms"]');
            var $patternBox = $('#pattern-code-div');

            function togglePatternBox() {
                if ($smsCheckbox.is(':checked')) {
                    $patternBox.removeClass('d-none');
                } else {
                    $patternBox.addClass('d-none');
                }
            }

            // اجرا در بارگذاری اولیه
            togglePatternBox();

            // اتصال به تغییر وضعیت چک‌باکس
            $smsCheckbox.on('change', togglePatternBox);

            // -------- مدیریت افزودن و حذف متغیرها --------
            var $variablesContainer = $('#variables');
            var $template = $('.variable-template');
            var $emptyAlert = $('#vars-empty'); // اگر این آی‌دی وجود ندارد، آن را به alert مورد نظر اضافه کنید

            // در صورتی که alert دارای id="vars-empty" نیست، می‌توانید با کلاس یا محتوا آن را انتخاب کنید:
            // var $emptyAlert = $('.msg-alert:contains("متغیرهای موجود را حذف کنید")');

            function updateEmptyState() {
                var $visibleRows = $variablesContainer.find('.variable-item:not(.d-none)');
                if ($visibleRows.length === 0) {
                    $emptyAlert.removeClass('d-none');
                } else {
                    $emptyAlert.addClass('d-none');
                }
            }

            // افزودن متغیر جدید
            $(document).on('click', '.add-variable-item', function(e) {
                e.preventDefault();

                // clone از قالب
                var $newRow = $template.clone();
                $newRow.removeClass('d-none variable-template');
                // خالی کردن مقادیر ورودی‌ها
                $newRow.find('input').val('');
                // افزودن به انتهای ظرف
                $variablesContainer.append($newRow);
                // به‌روزرسانی وضعیت خالی بودن
                updateEmptyState();
            });

            // حذف متغیر
            $(document).on('click', '.remove-variable-item', function(e) {
                e.preventDefault();
                var $row = $(this).closest('.variable-item');
                // اگر ردیف قالب نباشد (یعنی قابل حذف باشد)
                if (!$row.hasClass('variable-template')) {
                    $row.remove();
                    updateEmptyState();
                }
            });

            // وضعیت اولیه پس از بارگذاری
            updateEmptyState();
        });
    </script>
@endpush
