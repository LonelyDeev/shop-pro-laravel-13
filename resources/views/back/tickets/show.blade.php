@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/vendors/css/file-uploaders/dropzone.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/css-rtl/plugins/file-uploaders/dropzone.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/tickets/show.css') }}">

    <style>
        /* ─── Reset & base ─── */
        .tk-page { padding: 1.5rem; }

        /* ─── Breadcrumb ─── */
        .tk-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #8e8ea0;
            margin-bottom: 1.5rem;
        }
        .tk-breadcrumb span { color: #4f46e5; font-weight: 600; }
        .tk-breadcrumb i { font-size: 0.65rem; }

        /* ─── Info card ─── */
        .tk-info-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #ebebf0;
            padding: 1.4rem 1.75rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .tk-info-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f5;
        }
        .tk-ticket-id {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: #8e8ea0;
            text-transform: uppercase;
        }
        .tk-ticket-id span {
            font-size: 1.05rem;
            color: #1c1c2e;
            margin-right: 0.3rem;
        }

        .tk-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem 1.5rem;
        }
        .tk-meta-item { display: flex; flex-direction: column; gap: 0.3rem; }
        .tk-meta-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #a0a0b8;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .tk-meta-value {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.88rem;
            font-weight: 500;
            color: #1c1c2e;
        }
        .tk-meta-value a {
            color: #4f46e5;
            opacity: 0.7;
            transition: opacity .2s;
        }
        .tk-meta-value a:hover { opacity: 1; }

        /* priority badges */
        .tk-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.7rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .tk-badge-high   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .tk-badge-medium { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .tk-badge-low    { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        /* status select */
        .tk-status-wrap { position: relative; display: inline-flex; align-items: center; }
        .tk-status-wrap::before {
            content: '';
            position: absolute;
            right: 0.7rem;
            width: 8px; height: 8px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }
        .tk-status-wrap.status-pending::before  { background: #f59e0b; }
        .tk-status-wrap.status-answered::before { background: #4f46e5; }
        .tk-status-wrap.status-open::before     { background: #10b981; }
        .tk-status-wrap.status-close::before    { background: #6b7280; }

        #tickets-type {
            -webkit-appearance: none;
            appearance: none;
            border: 1.5px solid #e5e7eb;
            border-radius: 50px;
            padding: 0.3rem 2.2rem 0.3rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            background: #f9fafb;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            min-width: 145px;
        }
        #tickets-type:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
        }

        /* ─── Chat section ─── */
        .tk-chat-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #ebebf0;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            overflow: hidden;
        }
        .tk-chat-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f5;
            background: #fafafa;
        }
        .tk-chat-header-icon {
            width: 34px; height: 34px;
            background: #ede9fe;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #4f46e5;
        }
        .tk-chat-header h6 {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 600;
            color: #1c1c2e;
        }
        .tk-chat-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            max-height: 520px;
            overflow-y: auto;
        }
        .tk-chat-body::-webkit-scrollbar { width: 5px; }
        .tk-chat-body::-webkit-scrollbar-track { background: transparent; }
        .tk-chat-body::-webkit-scrollbar-thumb { background: #e0e0ef; border-radius: 10px; }

        .tk-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #a0a0b8;
            font-size: 0.72rem;
            font-weight: 500;
            margin: 0.25rem 0;
        }
        .tk-divider::before, .tk-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ebebf0;
        }

        /* bubbles */
        .tk-msg { display: flex; gap: 0.75rem; align-items: flex-end; }
        .tk-msg.own { flex-direction: row-reverse; }

        .tk-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .tk-avatar.admin { background: #ede9fe; color: #4f46e5; }
        .tk-avatar.user  { background: #ecfdf5; color: #059669; }

        .tk-bubble-wrap { display: flex; flex-direction: column; gap: 0.3rem; max-width: 68%; }
        .tk-msg.own .tk-bubble-wrap { align-items: flex-end; }

        .tk-bubble {
            padding: 0.75rem 1rem;
            border-radius: 16px;
            font-size: 0.86rem;
            line-height: 1.65;
            color: #1c1c2e;
        }
        .tk-msg:not(.own) .tk-bubble {
            background: #f4f4f8;
            border-bottom-right-radius: 4px;
        }
        .tk-msg.own .tk-bubble {
            background: #ede9fe;
            color: #312e81;
            border-bottom-left-radius: 4px;
        }

        .tk-bubble-time {
            font-size: 0.68rem;
            color: #b0b0c8;
            padding: 0 0.25rem;
        }

        .tk-bubble-attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.4rem;
        }
        .tk-attach-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,255,255,.7);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            color: #4f46e5;
            text-decoration: none;
        }
        .tk-attach-item:hover { background: #fff; }

        /* ─── Reply card ─── */
        .tk-reply-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #ebebf0;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            overflow: hidden;
        }
        .tk-reply-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f5;
            background: #fafafa;
        }
        .tk-reply-header-icon {
            width: 34px; height: 34px;
            background: #e0f2fe;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #0284c7;
        }
        .tk-reply-header h6 {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 600;
            color: #1c1c2e;
        }
        .tk-reply-body { padding: 1.5rem; }

        .tk-field-label {
            font-size: 0.77rem;
            font-weight: 600;
            color: #6b7280;
            letter-spacing: .03em;
            text-transform: uppercase;
            margin-bottom: 0.45rem;
            display: block;
        }

        textarea#message {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
            color: #1c1c2e;
            resize: vertical;
            min-height: 110px;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            font-family: inherit;
            background: #fdfdff;
        }
        textarea#message::placeholder { color: #c0c0d0; }
        textarea#message:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.1);
            background: #fff;
        }

        /* dropzone override */
        .tk-dropzone {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            background: #fafafa;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color .2s, background .2s;
        }
        .tk-dropzone:hover, .tk-dropzone.dz-drag-hover {
            border-color: #4f46e5;
            background: #f5f3ff;
        }
        .tk-dropzone .dz-message {
            font-size: 0.84rem;
            color: #9ca3af;
        }

        /* submit button */
        .tk-btn-send {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }
        .tk-btn-send:hover { background: #4338ca; }
        .tk-btn-send:active { transform: scale(0.97); }
        .tk-btn-send svg { width: 16px; height: 16px; }

        /* empty state */
        .tk-empty {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="app-content content tk-page" dir="rtl">
        <div class="content-wrapper">

            {{-- breadcrumb --}}
            <div class="tk-breadcrumb">
                مدیریت
                <i class="feather icon-chevron-left"></i>
                مدیریت تیکت‌ها
                <i class="feather icon-chevron-left"></i>
                <span>مشاهده تیکت #{{ $ticket->id }}</span>
            </div>

            {{-- ── Info card ── --}}
            <div class="tk-info-card">
                <div class="tk-info-card-header">
                    <div class="tk-ticket-id">
                        شماره تیکت <span>#{{ $ticket->id }}</span>
                    </div>
                    <div class="tk-status-wrap status-{{ $ticket->status }}">
                        <select
                            name="type"
                            id="tickets-type"
                            data-action="{{ route('admin.tickets.type', $ticket) }}"
                        >
                            <option {{ $ticket->status == 'pending'  ? 'selected' : '' }} value="pending">در انتظار پاسخ</option>
                            <option {{ $ticket->status == 'answered' ? 'selected' : '' }} value="answered">پاسخ داده شده</option>
                            <option {{ $ticket->status == 'open'     ? 'selected' : '' }} value="open">باز</option>
                            <option {{ $ticket->status == 'close'    ? 'selected' : '' }} value="close">بسته</option>
                        </select>
                    </div>
                </div>

                <div class="tk-meta-grid">
                    <div class="tk-meta-item">
                        <span class="tk-meta-label">کاربر مربوطه</span>
                        <span class="tk-meta-value">
                        @if($ticket->user_id)
                                <i class="feather icon-user" style="color:#6b7280; font-size:.9rem;"></i>
                                {{ $ticket->user->fullname }}
                                <a href="{{ route('admin.users.show', ['user' => $ticket->user]) }}" target="_blank">
                                <i class="feather icon-external-link"></i>
                            </a>
                            @else
                                <i class="feather icon-briefcase" style="color:#6b7280; font-size:.9rem;"></i>
                                {{ $ticket->seller->fullname }}
                                <a href="{{ route('admin.sellers.show', ['seller' => $ticket->seller]) }}" target="_blank">
                                <i class="feather icon-external-link"></i>
                            </a>
                            @endif
                    </span>
                    </div>

                    <div class="tk-meta-item">
                        <span class="tk-meta-label">تاریخ ایجاد</span>
                        <span class="tk-meta-value">
                        <i class="feather icon-calendar" style="color:#6b7280; font-size:.9rem;"></i>
                        {{ jdate($ticket->created_at) }}
                    </span>
                    </div>

                    <div class="tk-meta-item">
                        <span class="tk-meta-label">اولویت</span>
                        <span class="tk-meta-value">
                        @php $priority = $ticket->priority ?? 'low'; @endphp
                        <span class="tk-badge tk-badge-{{ $priority == 'high' ? 'high' : ($priority == 'medium' ? 'medium' : 'low') }}">
                            {{ $ticket->priorityText() }}
                        </span>
                    </span>
                    </div>
                </div>
            </div>

            {{-- ── Chat card ── --}}
            <div class="tk-chat-card">
                <div class="tk-chat-header">
                    <div class="tk-chat-header-icon">
                        <i class="feather icon-message-circle" style="font-size:1rem;"></i>
                    </div>
                    <h6>مکالمات تیکت</h6>
                </div>

                <div class="tk-chat-body" id="chat-body">
                    @forelse ($ticket->messages()->oldest()->get() as $message)

                        @if ($loop->first)
                            <div class="tk-divider">{{ jdate($message->created_at)->ago() }}</div>
                        @endif

                        @php $isAdmin = (bool) $message->admin_id; @endphp

                        <div class="tk-msg {{ $isAdmin ? 'own' : '' }}">
                            <div class="tk-avatar {{ $isAdmin ? 'admin' : 'user' }}">
                                {{ $isAdmin ? 'ادم' : mb_substr(optional($ticket->user_id ? $ticket->user : $ticket->seller)->fullname ?? '?', 0, 2) }}
                            </div>
                            <div class="tk-bubble-wrap">
                                <div class="tk-bubble">
                                    {!! nl2br(e($message->message)) !!}

                                    @if($message->files && count($message->files) > 0)
                                        <div class="tk-bubble-attachments">
                                            @foreach($message->files as $file)
                                                <a href="{{ $file->url ?? '#' }}" class="tk-attach-item" target="_blank">
                                                    <i class="feather icon-paperclip" style="font-size:.8rem;"></i>
                                                    {{ $file->name ?? 'پیوست' }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="tk-bubble-time">{{ jdate($message->created_at)->format('H:i - Y/m/d') }}</span>
                            </div>
                        </div>

                    @empty
                        <div class="tk-empty">
                            <i class="feather icon-inbox" style="font-size:1.8rem; display:block; margin-bottom:.5rem;"></i>
                            هنوز پیامی ارسال نشده است
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── Reply card ── --}}
            <div class="tk-reply-card">
                <div class="tk-reply-header">
                    <div class="tk-reply-header-icon">
                        <i class="feather icon-send" style="font-size:1rem;"></i>
                    </div>
                    <h6>ارسال پاسخ</h6>
                </div>

                <div class="tk-reply-body">
                    <form
                        id="ticket-update-form"
                        action="{{ route('admin.tickets.update', ['ticket' => $ticket]) }}"
                        method="post"
                    >
                        @csrf
                        @method('put')

                            <div class="row">
                                <div class="col-md-8 form-group mb-1">
                                    <label class="tk-field-label" for="message">متن پیام</label>
                                    <textarea id="message" name="message" rows="4" placeholder="پاسخ خود را اینجا بنویسید…"></textarea>
                                </div>

                                <div class="col-md-4 mb-1-5">
                                    <label class="tk-field-label">فایل‌های پیوست</label>
                                    <div
                                        class="dropzone tk-dropzone"
                                        id="ticket-file-dropzone"
                                        data-url="{{ route('admin.tickets.file.store') }}"
                                        data-remove-url="{{ route('admin.tickets.file.destroy') }}"
                                    >
                                        <div class="dz-message">
                                            <i class="feather icon-upload-cloud" style="font-size:1.4rem; display:block; margin:0 auto .3rem;"></i>
                                            فایل‌ها را اینجا بکشید یا کلیک کنید
                                        </div>
                                    </div>
                                </div>
                            </div>




                        <div style="margin-top: 1.25rem;">
                            <button type="submit" class="tk-btn-send">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12zm0 0h7.5" />
                                </svg>
                                ارسال پاسخ
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        // auto-scroll chat to bottom
        document.addEventListener('DOMContentLoaded', function () {
            var cb = document.getElementById('chat-body');
            if (cb) cb.scrollTop = cb.scrollHeight;
        });
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/vendors/js/extensions/dropzone.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/tickets/show.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/tickets/all.js') }}"></script>
@endpush
