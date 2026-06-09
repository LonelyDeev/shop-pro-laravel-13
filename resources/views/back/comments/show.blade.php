<div class="table-responsive">
    <form id="comment-edit-form" action="{{ route('admin.comments.update', ['comment' => $comment]) }}">
        @method('put')
        @csrf

        <table class="table">
            <tbody>
            <tr>
                <th scope="row">نام</th>
                <td>
                    @if($comment->user)
                        [کاربر] {{ $comment->user->fullname ?? $comment->user->name ?? $comment->name }}
                        <a class="float-right" href="{{ route('admin.users.show', ['user' => $comment->user]) }}" target="_blank">
                            <i class="feather icon-external-link"></i>
                        </a>
                    @elseif($comment->admin)
                        [مدیر سایت] {{ $comment->admin->fullname ?? '' }}
                    @elseif($comment->seller)
                        [فروشنده] {{ $comment->seller->business_name ?? '' }}
                        <a class="float-right" href="{{ route('admin.sellers.show', ['seller' => $comment->seller]) }}" target="_blank">
                            <i class="feather icon-external-link"></i>
                        </a>
                    @endif
                </td>
            </tr>

            @if($comment->commentable)
                <tr>
                    <th scope="row">در</th>
                    <td>
                        {{ $comment->commentable->title ?? '' }}
                        <a class="float-right" href="{{ $comment->commentable->link() ?? '#' }}" target="_blank">
                            <i class="feather icon-external-link"></i>
                        </a>
                    </td>
                </tr>
            @endif

            <table>
                <th scope="row" style="min-width: 100px;">متن دیدگاه</th>
                <td>
                    <div id="comment-body">
                        {{ $comment->body }}
                        <div class="mt-1">
                            <button id="edit-comment-btn" type="button" class="btn btn-flat-primary waves-effect waves-light">
                                <i class="feather icon-edit"></i> ویرایش
                            </button>
                        </div>
                    </div>
                    <fieldset id="edit-comment-body" class="form-group" style="display: none;">
                        <textarea name="body" class="form-control" rows="4" required>{{ $comment->body }}</textarea>
                    </fieldset>
                </td>
                </tr>

                {{-- نمایش پاسخ‌های موجود --}}
                @if($comment->replies->count() > 0)
                    <tr>
                        <th scope="row">پاسخ‌ها</th>
                        <td>
                            <div class="replies-list">
                                @foreach($comment->replies as $reply)
                                    <div class="card mb-2 border" style="background-color: #f8f9fa;" id="reply-card-{{ $reply->id }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>
                                                        @if($reply->admin)
                                                            [مدیر] {{ $reply->admin->fullname ?? '' }}
                                                        @elseif($reply->user)
                                                            [کاربر] {{ $reply->user->fullname ?? $reply->user->name ?? '' }}
                                                        @elseif($reply->seller)
                                                            [فروشنده] {{ $reply->seller->business_name ?? '' }}
                                                        @endif
                                                    </strong>
                                                    <small class="text-muted mr-2">
                                                        {{ jdate($reply->created_at)->format('d F Y H:i') }}
                                                    </small>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    {{-- تغییر وضعیت پاسخ --}}
                                                    <select class="form-control form-control-sm reply-status-select"
                                                            data-reply-id="{{ $reply->id }}"
                                                            data-action="{{ route('admin.comments.update-reply-status', ['reply' => $reply]) }}"
                                                            style="width: 120px; font-size: 12px; margin-left: 5px;">
                                                        <option value="pending" {{ $reply->status == 'pending' ? 'selected' : '' }}>⏳ منتظر تایید</option>
                                                        <option value="accepted" {{ $reply->status == 'accepted' ? 'selected' : '' }}>✓ تایید شده</option>
                                                        <option value="unconfirmed" {{ $reply->status == 'unconfirmed' ? 'selected' : '' }}>✗ تایید نشده</option>
                                                    </select>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary edit-reply-btn"
                                                            data-reply-id="{{ $reply->id }}"
                                                            data-reply-body="{{ htmlspecialchars($reply->body) }}">
                                                        <i class="feather icon-edit"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-reply-btn"
                                                            data-reply-id="{{ $reply->id }}"
                                                            data-action="{{ route('admin.comments.destroy-reply', ['reply' => $reply]) }}">
                                                        <i class="feather icon-trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- نمایش متن پاسخ --}}
                                            <p class="mb-0 mt-2 reply-body-text" id="reply-body-{{ $reply->id }}">
                                                {{ $reply->body }}
                                            </p>

                                            {{-- نمایش وضعیت فعلی به صورت Badge --}}
                                            <div class="mt-2">
                                                @if($reply->status == 'pending')
                                                    <span class="badge badge-warning">⏳ منتظر تایید</span>
                                                @elseif($reply->status == 'accepted')
                                                    <span class="badge badge-success">✓ تایید شده</span>
                                                @else
                                                    <span class="badge badge-danger">✗ تایید نشده</span>
                                                @endif
                                            </div>

                                            {{-- فرم ویرایش پاسخ (مخفی در ابتدا) --}}
                                            <div class="edit-reply-form" id="edit-reply-form-{{ $reply->id }}" style="display: none;">
                                                <textarea class="form-control reply-edit-textarea" rows="3" id="reply-edit-textarea-{{ $reply->id }}">{{ $reply->body }}</textarea>
                                                <div class="mt-2">
                                                    <button type="button"
                                                            class="btn btn-sm btn-success save-reply-edit"
                                                            data-reply-id="{{ $reply->id }}"
                                                            data-action="{{ route('admin.comments.update-reply', ['reply' => $reply]) }}">
                                                        <i class="feather icon-check"></i> ذخیره
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary cancel-reply-edit"
                                                            data-reply-id="{{ $reply->id }}">
                                                        <i class="feather icon-x"></i> انصراف
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- فرم پاسخ جدید --}}
                <tr>
                    <th scope="row">پاسخ جدید</th>
                    <td>
                        <fieldset class="form-group">
                            <textarea id="reply-textarea" name="replay" class="form-control" rows="4" placeholder="پاسخ خود را بنویسید..."></textarea>
                        </fieldset>
                        <button type="button"
                                id="submit-reply-btn"
                                class="btn btn-primary btn-sm"
                                data-comment-id="{{ $comment->id }}"
                                data-action="{{ route('admin.comments.reply', ['comment' => $comment]) }}">
                            <i class="feather icon-send"></i> ارسال پاسخ
                        </button>
                    </td>
                </tr>

                <tr>
                    <th scope="row">تاریخ ارسال</th>
                    <td>{{ jdate($comment->created_at)->format('d F Y H:i') }} ({{ jdate($comment->created_at)->ago() }})</td>
                </tr>

                <tr>
                    <th scope="row">وضعیت</th>
                    <td>
                        <select class="form-control" name="status">
                            <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>منتظر تایید</option>
                            <option value="accepted" {{ $comment->status == 'accepted' ? 'selected' : '' }}>تایید شده</option>
                            <option value="unconfirmed" {{ $comment->status == 'unconfirmed' ? 'selected' : '' }}>تایید نشده</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

@push('scripts')
    <script>

    </script>
@endpush
