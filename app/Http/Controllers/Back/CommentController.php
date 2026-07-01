<?php

namespace App\Http\Controllers\Back;

use App\Models\AdminSession;
use App\Models\Comment;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        //$this->authorizeResource(Comment::class, 'comment');
    }

    public function productComments(Request $request)
    {
        $this->authorize('products.comments');
        $comments = Comment::where('commentable_type', Product::class)
            ->whereNull('comment_id')
            ->filter($request)
            ->withCount('replies')
            ->with(['user', 'admin', 'seller', 'commentable'])
            ->paginate(15);

        return view('back.comments.index', compact('comments'));
    }

    public function postComments(Request $request)
    {
        $this->authorize('posts.comments');
        $comments = Comment::where('commentable_type', Post::class)
            ->whereNull('comment_id')
            ->filter($request)
            ->withCount('replies')
            ->with(['user', 'admin', 'seller', 'commentable'])
            ->paginate(15);

        return view('back.comments.index', compact('comments'));
    }

    public function show(Comment $comment)
    {
        if (auth('adminPanel')->user()->can('posts.comments') || auth('adminPanel')->user()->can('products.comments')) {
            return view('back.comments.show', compact('comment'))->render();
        } else {
            abort(403);
        }
    }

    public function destroy(Comment $comment)
    {
        if (!auth('adminPanel')->user()->can('posts.comments') || !auth('adminPanel')->user()->can('products.comments')) {
            abort(403);
        }

        $commentText = mb_substr($comment->body ?? '', 0, 50);
        $commentType = $comment->commentable_type === Product::class ? 'محصول' : 'مقاله';
        $commentableTitle = $comment->commentable->title ?? $comment->commentable->name ?? 'نامشخص';
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        $comment->delete();

        $logMessage = "مدیر {$adminName} نظر «{$commentText}» را در {$commentType} «{$commentableTitle}» حذف کرد";

        activity()
            ->performedOn($comment)
            ->causedBy(auth('adminPanel')->user())
            ->withProperties([
                'action' => 'delete_comment',
                'comment_text' => $commentText,
            ])
            ->log($logMessage);

        return response('success');
    }


    public function update(Comment $comment, Request $request)
    {
        if (!auth('adminPanel')->user()->can('posts.comments') || !auth('adminPanel')->user()->can('products.comments')) {
            abort(403);
        }

        $this->validate($request, [
            'status' => 'required',
            'body'   => 'required',
            'replay' => 'nullable|string',
        ]);

        $oldStatus = $comment->status;
        $oldBody = $comment->body;
        $commentText = mb_substr($comment->body ?? '', 0, 50);
        $commentType = $comment->commentable_type === Product::class ? 'محصول' : 'مقاله';
        $commentableTitle = $comment->commentable->title ?? $comment->commentable->name ?? 'نامشخص';

        $comment->update([
            'body'   => $request->body,
            'status' => $request->status
        ]);

        $newCommentText = mb_substr($comment->body ?? '', 0, 50);

        // ساخت description بدون نام مدیر
        $description = '';

        if ($oldBody != $request->body && $oldStatus != $request->status) {
            // هم متن هم وضعیت تغییر کرده
            $oldStatusText = $this->getStatusText($oldStatus);
            $newStatusText = $this->getStatusText($request->status);
            $description = "نظر «{$commentText}» را در {$commentType} «{$commentableTitle}» ویرایش کرد و وضعیت را از {$oldStatusText} به {$newStatusText} تغییر داد";
        } elseif ($oldBody != $request->body) {
            // فقط متن تغییر کرده - نوع اول
            $description = "نظر «{$commentText}» در {$commentType} «{$commentableTitle}» ویرایش کرد";
        } elseif ($oldStatus != $request->status) {
            // فقط وضعیت تغییر کرده - نوع دوم
            $oldStatusText = $this->getStatusText($oldStatus);
            $newStatusText = $this->getStatusText($request->status);
            $description = "وضعیت نظر «{$commentText}» را در {$commentType} «{$commentableTitle}» از {$oldStatusText} به {$newStatusText} تغییر داد";
        }

        if ($description) {
            activity()
                ->performedOn($comment)
                ->causedBy(auth('adminPanel')->user())
                ->withProperties([
                    'action' => 'update_comment',
                    'old_body' => $oldBody,
                    'new_body' => $request->body,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                ])
                ->log($description);
        }

        if ($request->replay) {
            $replyText = mb_substr($request->replay, 0, 50);

            $reply = $comment->commentable->comments()->create([
                'body'       => $request->replay,
                'admin_id'   => auth('adminPanel')->user()->id,
                'status'     => 'accepted',
                'comment_id' => $comment->id,
            ]);

            $description = "به نظر «{$commentText}» در {$commentType} «{$commentableTitle}» پاسخ داد: «{$replyText}»";

            activity()
                ->performedOn($comment)
                ->causedBy(auth('adminPanel')->user())
                ->withProperties([
                    'action' => 'reply_to_comment',
                    'reply_id' => $reply->id,
                    'reply_text' => $replyText,
                ])
                ->log($description);
        }

        return response($comment);
    }


    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => 'در انتظار',
            'accepted' => 'تایید شده',
            'rejected' => 'رد شده',
            'unconfirmed' => 'تایید نشده',
            'approved' => 'تایید شده',
        ];

        return $statusMap[$status] ?? $status;
    }

    public function reply(Request $request, Comment $comment)
    {
        if (!auth('adminPanel')->user()->can('posts.comments') || !auth()->user()->can('products.comments')) {
            abort(403);
        }

        $request->validate([
            'reply' => 'required|string|min:3|max:1000'
        ]);

        $commentText = mb_substr($comment->body ?? '', 0, 50);
        $replyText = mb_substr($request->reply, 0, 50);
        $commentType = $comment->commentable_type === Product::class ? 'محصول' : 'مقاله';
        $commentableTitle = $comment->commentable->title ?? $comment->commentable->name ?? 'نامشخص';
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        $reply = Comment::create([
            'body' => $request->reply,
            'commentable_id' => $comment->commentable_id,
            'commentable_type' => $comment->commentable_type,
            'admin_id' => auth()->id(),
            'comment_id' => $comment->id,
            'status' => 'accepted',
            'ip_address' => $request->ip(),
            'likes_count' => 0,
            'dislikes_count' => 0
        ]);

        $logMessage = "مدیر {$adminName} به نظر «{$commentText}» در {$commentType} «{$commentableTitle}» پاسخ داد: «{$replyText}»";

        activity()
            ->performedOn($comment)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'reply_to_comment',
                'reply_id' => $reply->id,
                'reply_text' => $replyText,
                'parent_comment' => $commentText,
            ])
            ->log($logMessage);

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    }

    public function updateReply(Request $request, Comment $reply)
    {
        if (!auth()->user()->can('posts.comments') || !auth()->user()->can('products.comments')) {
            abort(403);
        }

        if (is_null($reply->comment_id)) {
            return response()->json(['success' => false, 'message' => 'این آیتم پاسخ نیست'], 400);
        }

        $request->validate([
            'body' => 'required|string|min:3|max:1000'
        ]);

        $oldBody = $reply->body;
        $newBody = $request->body;
        $replyText = mb_substr($reply->body ?? '', 0, 50);
        $parentComment = Comment::find($reply->comment_id);
        $parentText = mb_substr($parentComment->body ?? '', 0, 50);
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        $reply->update([
            'body' => $request->body
        ]);

        // لاگ دستی برای ویرایش پاسخ
        activity()
            ->performedOn($reply)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'update_reply',
                'old_body' => mb_substr($oldBody, 0, 50),
                'new_body' => mb_substr($newBody, 0, 50),
                'parent_comment' => $parentText,
                'ip' => $request->ip()
            ])
            ->log("مدیر {$adminName} پاسخ خود به نظر «{$parentText}» را از «{$replyText}» به «" . mb_substr($newBody, 0, 50) . "» ویرایش کرد");

        return response()->json([
            'success' => true
        ]);
    }

    public function destroyReply(Comment $reply)
    {
        if (!auth()->user()->can('posts.comments') || !auth()->user()->can('products.comments')) {
            abort(403);
        }

        if (is_null($reply->comment_id)) {
            return response()->json(['success' => false, 'message' => 'این آیتم پاسخ نیست'], 400);
        }

        $replyText = mb_substr($reply->body ?? '', 0, 50);
        $parentComment = Comment::find($reply->comment_id);
        $parentText = mb_substr($parentComment->body ?? '', 0, 50);
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $commentType = $parentComment->commentable_type === Product::class ? 'محصول' : 'مقاله';

        $reply->delete();

        // لاگ دستی برای حذف پاسخ
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'delete_reply',
                'reply_text' => $replyText,
                'parent_comment' => $parentText,
                'comment_type' => $commentType
            ])
            ->log("مدیر {$adminName} پاسخ «{$replyText}» به نظر «{$parentText}» را حذف کرد");

        return response()->json([
            'success' => true
        ]);
    }

    public function updateReplyStatus(Request $request, Comment $reply)
    {
        if (!auth()->user()->can('posts.comments') || !auth()->user()->can('products.comments')) {
            abort(403);
        }

        if (is_null($reply->comment_id)) {
            return response()->json(['success' => false, 'message' => 'این آیتم پاسخ نیست'], 400);
        }

        $request->validate([
            'status' => 'required|in:pending,accepted,unconfirmed'
        ]);

        $oldStatus = $reply->status;
        $newStatus = $request->status;
        $replyText = mb_substr($reply->body ?? '', 0, 50);
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        $reply->update([
            'status' => $request->status
        ]);

        // لاگ دستی برای تغییر وضعیت پاسخ
        $statusMap = [
            'pending' => 'در انتظار',
            'accepted' => 'تایید شده',
            'unconfirmed' => 'تایید نشده'
        ];

        $oldStatusText = $statusMap[$oldStatus] ?? $oldStatus;
        $newStatusText = $statusMap[$newStatus] ?? $newStatus;

        activity()
            ->performedOn($reply)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'change_reply_status',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reply_text' => $replyText,
                'ip' => $request->ip()
            ])
            ->log("مدیر {$adminName} وضعیت پاسخ «{$replyText}» را از {$oldStatusText} به {$newStatusText} تغییر داد");

        return response()->json([
            'success' => true,
            'status' => $reply->status,
            'message' => 'وضعیت پاسخ با موفقیت تغییر کرد'
        ]);
    }
}
