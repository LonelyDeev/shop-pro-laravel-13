<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = ['id'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function product()
    {
        return Product::find($this->commentable_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function refreshLikesCount()
    {
        $this->update([
            'likes_count'    => $this->likes()->where('type', 'like')->count(),
            'dislikes_count' => $this->likes()->where('type', 'dislike')->count(),
        ]);
    }


    public function scopeFilter($query, $request)
    {

        if ($request->status and $request->status!="all") {
            if ($request->status=="answer_accepted"){
                $query->where('status', 'accepted')->where('comment_id','!=',null);
            }elseif ($request->status=="noanswer"){
                $query->where('status', $request->status)->where('comment_id',null);
            }else{
                $query->where('status', $request->status);
            }
        }

        if ($request->status=="answer_accepted"){
            $query->where('comment_id','!=',null);
        }

        switch ($request->ordering) {
            case 'oldest': {
                    $query->oldest();
                    break;
                }
            default: {
                    $query->latest();
                }
        }

        return $query;
    }

    public function UserName()
    {
        if ($this->user) {
            return $this->user->fullname;
        }

        return $this->name;
    }
    public function AdminName()
    {
        if ($this->user) {
            return $this->admin->fullname;
        }

        return $this->name;
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // در مدل Comment
    public function isOwnedByCurrentUser(): bool
    {
        if (auth()->check()) {
            return $this->user_id === auth()->id();
        }

        // برای کاربران مهمان، بررسی session_id
        return $this->session_id === session()->getId();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function shouldShowWithOpacity(): bool
    {
        return $this->isPending() && $this->isOwnedByCurrentUser();
    }

    public function shouldHideFromOthers(): bool
    {
        return $this->isPending() && !$this->isOwnedByCurrentUser();
    }
    public function scopeVisibleForUser($query)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return $query; // ادمین همه رو می‌بینه
        }

        $userId = auth()->id();
        $sessionId = session()->getId();
        $pendingCommentsInSession = session()->get('pending_comments', []);

        return $query->where(function($q) use ($userId, $sessionId, $pendingCommentsInSession) {
            // کامنت‌های تایید شده
            $q->where('status', 'approved')
                // کامنت‌های pending متعلق به کاربر فعلی (لاگین شده)
                ->orWhere(function($q2) use ($userId) {
                    $q2->where('status', 'pending')
                        ->where('user_id', $userId);
                })
                // کامنت‌های pending متعلق به سشن فعلی (مهمان)
                ->orWhere(function($q2) use ($sessionId) {
                    $q2->where('status', 'pending')
                        ->where('session_id', $sessionId);
                });

            // اگر کاربر مهمان است و کامنت‌های pending در سشن دارد
            if (!auth()->check() && !empty($pendingCommentsInSession)) {
                $q->orWhereIn('id', $pendingCommentsInSession);
            }
        });
    }


    public function replies()
    {
        return $this->hasMany(Comment::class, 'comment_id')
            ->orderBy('created_at', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}
