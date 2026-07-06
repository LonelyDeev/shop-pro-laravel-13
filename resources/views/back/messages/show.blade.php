@extends('back.layouts.master')

@section('content')
    <div class="app-content content msg-page msg-show">
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
                                    <li class="breadcrumb-item active">مشاهده پیام</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @php
                    $totalRecipients = method_exists($messageItems, 'total') ? $messageItems->total() : count($messageItems);
                    $channelCount = 0;
                    if ($message->email) { $channelCount++; }
                    if ($message->sms) { $channelCount++; }
                    if ($message->notification) { $channelCount++; }
                    $patternCode = isset($message->pattern_code) ? $message->pattern_code : option('user_message_pattern_code');
                    $msgVars = null;
                    if (isset($message->variables)) {
                        $msgVars = is_string($message->variables) ? json_decode($message->variables, true) : $message->variables;
                        if (!is_array($msgVars) || count($msgVars) === 0) { $msgVars = null; }
                    }
                @endphp

                {{-- Page title --}}
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="msg-page-icon"><i class="feather icon-eye"></i></div>
                    <div class="flex-grow-1 pl-1">
                        <h3 class="mb-0 font-weight-bolder text-truncate">{{ $message->title }}</h3>
                        <small class="text-muted">مشاهده‌ی جزئیات پیام و وضعیت تحویل به گیرندگان</small>
                    </div>
                </div>

                {{-- Stat cards --}}
                <div class="row match-height mb-2">
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-teal">
                            <div class="msg-stat-icon"><i class="feather icon-users"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ $totalRecipients }}</span>
                                <span class="msg-stat-label">گیرندگان</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-violet">
                            <div class="msg-stat-icon"><i class="feather icon-message-square"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value">{{ $channelCount }}</span>
                                <span class="msg-stat-label">کانال ارسال</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-sky">
                            <div class="msg-stat-icon"><i class="feather icon-calendar"></i></div>
                            <div class="msg-stat-meta">
                                <span class="msg-stat-value msg-stat-date">{{ jdate($message->created_at) }}</span>
                                <span class="msg-stat-label">تاریخ ارسال</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="msg-stat stat-emerald">
                            <div class="msg-stat-icon"><i class="feather icon-check-circle"></i></div>
                            <div class="msg-stat-meta">
                                @if ($message->status_send == 'pending')
                                    <span class="status-pill status-pending"><span class="dot"></span> منتظر ارسال</span>
                                @elseif ($message->status_send == 'sent')
                                    <span class="status-pill status-sent"><span class="dot"></span> ارسال شده</span>
                                @else
                                    <span class="status-pill status-failed"><span class="dot"></span> ارسال نشده</span>
                                @endif
                                <span class="msg-stat-label">وضعیت</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message detail card --}}
                <section id="description" class="card msg-card">
                    <div class="card-header msg-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="msg-card-badge"><i class="feather icon-file-text"></i></span>
                            <div class="ml-1">
                                <h4 class="card-title mb-0">جزئیات پیام</h4>
                                <small class="text-muted">اطلاعات ارسال‌شده در این پیام</small>
                            </div>
                        </div>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <form id="messages-create-form">
                                {{-- Title (readonly) --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group">
                                            <label>عنوان</label>
                                            <input type="text" class="form-control msg-readonly" name="title" value="{{ $message->title }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                {{-- Description (readonly) --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group">
                                            <label>توضیحات</label>
                                            <textarea class="form-control msg-readonly" name="description" rows="3" readonly>{{ $message->description }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Channels (read-only chips) --}}
                                <div class="row">
                                    <div class="col-md-8 offset-md-2 col-12">
                                        <div class="form-group mb-0">
                                            <label class="d-block mb-1">کانال‌های ارسال</label>
                                            <div class="channel-pills-lg">
                                                @if ($message->email)
                                                    <span class="channel-chip ch-email">
                                                        <i class="feather icon-mail"></i> ایمیل
                                                        <i class="feather icon-check ch-tick"></i>
                                                    </span>
                                                @endif
                                                @if ($message->sms)
                                                    <span class="channel-chip ch-sms">
                                                        <i class="feather icon-message-square"></i> پیامک
                                                        <i class="feather icon-check ch-tick"></i>
                                                    </span>
                                                @endif
                                                @if ($message->notification)
                                                    <span class="channel-chip ch-notif">
                                                        <i class="feather icon-bell"></i> نوتیفیکیشن
                                                        <i class="feather icon-check ch-tick"></i>
                                                    </span>
                                                @endif
                                                @if (!$message->email && !$message->sms && !$message->notification)
                                                    <span class="text-muted small">هیچ کانالی ثبت نشده است.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SMS pattern section (shown server-side when sms was sent) --}}
                                @if ($message->sms)
                                    <div id="pattern-code-div" class="row mt-2">
                                        <div class="col-md-8 offset-md-2 col-12">
                                            <div class="msg-pattern-box">
                                                <div class="msg-alert">
                                                    <i class="feather icon-info"></i>
                                                    <span>پیامک فقط با پترن ارسال می‌شود؛ عنوان و توضیحات در پیامک درج نمی‌شوند.</span>
                                                </div>

                                                <div class="form-group mb-0 mt-2">
                                                    <label>کد پترن برای ارسال پیام</label>
                                                    <input type="text" class="form-control ltr msg-readonly" value="{{ $patternCode }}" name="user_message_pattern_code" readonly>
                                                </div>

                                                <div class="msg-vars-head">
                                                    <h6 class="msg-vars-title"><i class="feather icon-code"></i> متغیرهای پترن</h6>
                                                </div>

                                                <div id="variables" class="msg-vars-list">
                                                    @if ($msgVars)
                                                        @foreach ($msgVars as $varName => $varValue)
                                                            <div class="variable-item row variable-readonly">
                                                                <div class="col-5">
                                                                    <div class="form-group mb-0">
                                                                        <label>اسم متغییر</label>
                                                                        <input type="text" class="form-control ltr msg-readonly" value="{{ $varName }}" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-7">
                                                                    <div class="form-group mb-0">
                                                                        <label>مقدار</label>
                                                                        <input type="text" class="form-control msg-readonly" value="{{ $varValue }}" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="msg-vars-empty">
                                                            <i class="feather icon-code"></i>
                                                            <p class="mb-0">این پیام متغیر ندارد.</p>
                                                            <p class="mb-0">ارسال با پترنِ بدون متغیر انجام شده است.</p>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="msg-alert mt-2">
                                                    <i class="feather icon-info"></i>
                                                    <span>این مقادیر هنگام ارسال پیامک برای هر گیرنده استفاده شده‌اند.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Keep the div in DOM for any JS selectors, but hidden --}}
                                    <div id="pattern-code-div" class="row mt-2 d-none"></div>
                                @endif
                            </form>
                        </div>
                    </div>
                </section>

                {{-- Recipients --}}
                <div class="list-reviews mt-2">
                    @if (count($messageItems))
                        <section class="card msg-card">
                            <div class="card-header msg-card-header">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="msg-card-badge bg-soft-violet"><i class="feather icon-users"></i></span>
                                        <h4 class="card-title mb-0 ml-1">گیرندگان پیام</h4>
                                    </div>
                                    <span class="msg-count-chip">{{ $totalRecipients }} کاربر</span>
                                </div>
                            </div>
                            <div class="card-content" id="main-card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table msg-table mb-0">
                                            <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>نام و نام خانوادگی</th>
                                                <th class="text-center">تاریخ</th>
                                                <th class="text-center">وضعیت</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($messageItems as $item)
                                                @php
                                                    $uName = $item->user ? $item->user->full_name : 'کاربر حذف شده';
                                                    $initial = mb_substr($uName, 0, 1);
                                                @endphp
                                                <tr id="review-{{ $item->id }}-tr">
                                                    <td class="text-center text-muted">{{ $item->id }}</td>
                                                    <td>
                                                        <div class="rcpt-cell">
                                                            <span class="rcpt-avatar">{{ $initial }}</span>
                                                            <span class="rcpt-name">{{ $uName }}</span>
                                                            @if ($item->user)
                                                                <a href="{{ route('admin.users.show', $item->user) }}" target="_blank" class="rcpt-link" data-toggle="tooltip" title="مشاهده‌ی پروفایل کاربر">
                                                                    <i class="feather icon-external-link"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-muted text-nowrap">{{ jdate($item->created_at) }}</td>
                                                    <td class="text-center">
                                                        @if ($item->status == 'seen')
                                                            <span class="status-pill status-sent"><span class="dot"></span> دیده شده</span>
                                                        @else
                                                            <span class="status-pill status-pending"><span class="dot"></span> دیده نشده</span>
                                                        @endif
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
                                    <div class="msg-empty-icon"><i class="feather icon-users"></i></div>
                                    <h5 class="mb-0">چیزی برای نمایش وجود ندارد!</h5>
                                    <p class="text-muted mb-0">هیچ گیرنده‌ای برای این پیام ثبت نشده است.</p>
                                </div>
                            </div>
                        </section>
                    @endif
                    {{ $messageItems->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Delete modal (kept for compatibility) --}}
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

    {{-- Scoped styles (emerald/teal accent, consistent with create page) --}}
    <style>
        .msg-page { --msg-accent: #0d9488; --msg-accent-600: #0f766e; --msg-accent-50: #f0fdfa; --msg-accent-100: #ccfbf1; --msg-accent-200: #99f6e4; }
        .msg-page .breadcrumb { background: transparent; padding: 0; }

        .msg-page-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--msg-accent), var(--msg-accent-600)); color: #fff; box-shadow: 0 6px 16px -6px rgba(13,148,136,.55); flex-shrink: 0; }
        .msg-page-icon i { font-size: 22px; }

        /* Stat cards */
        .msg-stat { display: flex; align-items: center; gap: .9rem; background: #fff; border: 1px solid #ededed; border-radius: 14px; padding: 1rem 1.1rem; height: 100%; box-shadow: 0 2px 10px -8px rgba(0,0,0,.18); transition: box-shadow .2s, transform .2s; }
        .msg-stat:hover { box-shadow: 0 10px 24px -14px rgba(0,0,0,.25); transform: translateY(-2px); }
        .msg-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .msg-stat-meta { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }
        .msg-stat-value { font-size: 1.5rem; font-weight: 800; color: #2b2b2b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .msg-stat-date { font-size: 1rem; font-weight: 700; }
        .msg-stat-label { font-size: .8rem; color: #8a8a8a; }
        .stat-teal .msg-stat-icon { background: #f0fdfa; color: #0d9488; }
        .stat-violet .msg-stat-icon { background: #f5f3ff; color: #7c3aed; }
        .stat-sky .msg-stat-icon { background: #f0f9ff; color: #0284c7; }
        .stat-emerald .msg-stat-icon { background: #ecfdf5; color: #059669; }

        /* Cards */
        .msg-card { border: 0; border-radius: 16px; box-shadow: 0 4px 24px -16px rgba(0,0,0,.22); overflow: hidden; }
        .msg-card-header { background: linear-gradient(90deg, var(--msg-accent-50), transparent); border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem; }
        .msg-card-badge { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: var(--msg-accent-100); color: var(--msg-accent-600); }
        .msg-card-badge i { font-size: 19px; }
        .bg-soft-violet { background: #f5f3ff !important; color: #7c3aed !important; }
        .msg-count-chip { background: var(--msg-accent-50); color: var(--msg-accent-600); border: 1px solid var(--msg-accent-200); border-radius: 999px; padding: .25rem .8rem; font-size: .78rem; font-weight: 700; }

        /* Read-only fields */
        .msg-readonly { background: #fafbfc !important; border-color: #ececec !important; color: #555 !important; cursor: default; opacity: 1; }
        .msg-readonly:focus { box-shadow: none !important; border-color: #ececec !important; }

        /* Channel chips (read-only) */
        .channel-pills-lg { display: flex; flex-wrap: wrap; gap: .5rem; padding: .5rem 0; }
        .channel-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .85rem; border-radius: 10px; font-size: .85rem; font-weight: 700; border: 1.5px solid; }
        .channel-chip .ch-tick { font-size: 14px; opacity: .9; }
        .channel-chip.ch-email { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
        .channel-chip.ch-sms { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
        .channel-chip.ch-notif { background: #fffbeb; color: #b45309; border-color: #fde68a; }

        /* Pattern box */
        .msg-pattern-box { border: 1px solid var(--msg-accent-200); background: var(--msg-accent-50); border-radius: 12px; padding: 1rem; }
        @media (min-width: 640px) { .msg-pattern-box { padding: 1.25rem; } }
        .msg-alert { display: flex; align-items: flex-start; gap: .625rem; background: rgba(255,255,255,.6); border: 0; border-radius: 8px; padding: .75rem; }
        .msg-alert i { color: var(--msg-accent-600); font-size: 16px; line-height: 1.4; flex-shrink: 0; }
        .msg-vars-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; margin-top: 1.25rem; }
        .msg-vars-title { display: flex; align-items: center; gap: .5rem; font-size: .875rem; font-weight: 600; margin: 0; color: #2b2b2b; }
        .msg-vars-title i { color: var(--msg-accent); font-size: 16px; }
        .msg-vars-list { margin-top: .75rem; }
        .msg-vars-list .variable-item { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 8px 12px; margin-bottom: 10px; }
        .variable-readonly { pointer-events: none; }
        .msg-vars-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .25rem; border: 1px dashed #e5e7eb; border-radius: 8px; padding: 1.5rem 1rem; text-align: center; margin-top: .75rem; }
        .msg-vars-empty i { color: rgba(138,138,138,.5); font-size: 20px; margin-bottom: .25rem; }
        .msg-vars-empty p { font-size: .75rem; color: #8a8a8a; }
        .msg-vars-empty p + p { color: rgba(138,138,138,.7); font-size: .6875rem; }

        /* Table */
        .msg-table thead th { background: #fafbfc; color: #6b6b6b; font-weight: 700; font-size: .82rem; border-bottom: 2px solid #f0f0f0; white-space: nowrap; }
        .msg-table tbody td { vertical-align: middle; border-bottom: 1px solid #f4f4f4; }
        .msg-table tbody tr { transition: background .15s; }
        .msg-table tbody tr:hover { background: #fcfdfd; }

        /* Recipient cell */
        .rcpt-cell { display: flex; align-items: center; gap: .6rem; }
        .rcpt-avatar { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--msg-accent), var(--msg-accent-600)); color: #fff; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
        .rcpt-name { font-weight: 600; color: #2b2b2b; }
        .rcpt-link { color: #9a9a9a; padding: .15rem .25rem; border-radius: 6px; transition: all .15s; }
        .rcpt-link:hover { color: var(--msg-accent); background: var(--msg-accent-50); }

        /* Status pills */
        .status-pill { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .7rem; border-radius: 999px; font-size: .76rem; font-weight: 700; white-space: nowrap; }
        .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
        .status-sent { background: #ecfdf5; color: #047857; }
        .status-sent .dot { background: #10b981; }
        .status-pending { background: #fffbeb; color: #b45309; }
        .status-pending .dot { background: #f59e0b; }

        /* Empty state */
        .msg-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .4rem; padding: 3rem 1rem; text-align: center; }
        .msg-empty-icon { width: 72px; height: 72px; border-radius: 18px; background: #f5f5f5; color: #b0b0b0; display: flex; align-items: center; justify-content: center; margin-bottom: .5rem; }
        .msg-empty-icon i { font-size: 34px; }

        /* Modal */
        .msg-modal { border: 0; border-radius: 16px; overflow: hidden; }
        .msg-modal-header { background: #fff5f5; border-bottom: 1px solid #ffe3e3; }
        .msg-modal-header .modal-title { font-size: 1.05rem; display: flex; align-items: center; gap: .5rem; }

        /* Pagination harmony */
        .msg-page .pagination .page-item.active .page-link { background: var(--msg-accent); border-color: var(--msg-accent); }
    </style>
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/messages/create.js') }}?v=2"></script>
    <script>
        // Read-only page: neutralize any create.js handlers that expect editable fields.
        (function () {
            $(function () {
                var $form = $('#messages-create-form');
                if ($form.length) { $form.on('submit', function (e) { e.preventDefault(); }); }
            });
        })();
    </script>
@endpush
