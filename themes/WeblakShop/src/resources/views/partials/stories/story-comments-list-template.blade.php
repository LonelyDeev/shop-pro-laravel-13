{{-- resources/views/front/partials/story-comments-list.blade.php --}}

@if($comments->count() > 0)
    <div class="comments-list">
        @foreach($comments as $comment)
            <div class="comment-item" id="story-comment-{{ $comment->id }}">
                <div class="comment-avatar">
                    @if($comment->user)
                        <img src="{{ $comment->user->image_url }}" alt="{{ $comment->user->full_name ?? 'کاربر' }}">
                    @else
                        <img src="{{ asset('default-avatar.jpg') }}" alt="مهمان">
                    @endif
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-user">
                            @if($comment->user)
                                {{ $comment->user->fullname ?? $comment->user->name ?? 'کاربر' }}
                            @else
                                کاربر مهمان
                            @endif
                        </span>
                        <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="comment-text">{{ $comment->comment }}</div>
                </div>
            </div>
        @endforeach


    </div>
    @if($comments->hasMorePages())
        <div class="text-center mt-2 mb-2">
            <button class="btn btn-sm btn-outline-primary load-more-comments" data-action="{{ route('front.story.comments', ['story' => $comment->story_id, 'page' => $comments->currentPage() + 1]) }}" data-page="{{ $comments->currentPage() + 1 }}">
                مشاهده بیشتر
            </button>
        </div>
    @endif
@else
    <div class="text-center p-4 text-muted">
        <i class="fa fa-comment fa-2x mb-2 d-block"></i>
        <p>هیچ نظری برای این استوری ثبت نشده است.</p>
        <small>اولین نفری باشید که نظر می‌دهید!</small>
    </div>
@endif
