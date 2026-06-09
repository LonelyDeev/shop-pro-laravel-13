<?php

namespace App\Models;

use App\Traits\HasLikes;
use App\Traits\Languageable;
use App\Traits\Taggable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Post extends Model
{
    use sluggable, Taggable, Languageable, HasLikes;

    protected $guarded = ['id'];

    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'slug',
            ],
        ];
    }


    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function Categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getShortContentAttribute()
    {
        $content = strip_tags($this->content);

        return Str::words($content, 15);
    }


    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }


    public function mainComments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('comment_id')
            ->where('status', 'accepted');
    }

    public function commentsWithReplies()
    {
        return $this->mainComments()
            ->with(['replies' => function($q) {
                $q->where('status', 'accepted')->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc');
    }

    public function link()
    {
        return route('front.articles.show', $this);
    }

    public function isVideo()
    {
        return $this->post_type === 'video';
    }

    public function isPodcast()
    {
        return $this->post_type === 'podcast';
    }

    public function isText()
    {
        return $this->post_type === 'text';
    }

    public function getMediaUrlAttribute()
    {
        if ($this->isVideo()) {
            return $this->video_url;
        }
        if ($this->isPodcast()) {
            return $this->podcast_url;
        }
        return null;
    }

    public function scopeEditorPick($query)
    {
        return $query->where('is_editor_pick', true);
    }

    public function scopePublished($query)
    {
        $query->where('published', true)->where(function ($q) {
            $q->where('publish_date', null)->orWhere('publish_date', '<=', Carbon::now());
        });

        return $query;
    }

    public function isPublished()
    {
        return ($this->published && (!$this->publish_date || $this->publish_date <= Carbon::now()));
    }

    public function isShowable()
    {
        if ($this->isPublished()) {
            return true;
        }

        if (auth()->check() && auth()->user()->can('posts')) {
            return true;
        }

        return false;
    }

    public function scopeApiFilter($query)
    {
        $request = request();

        if ($category_id = $request->category_id) {
            $category = Category::findOrFail($category_id);

            if ($category) {
                $query->whereIn('category_id', $category->allChildCategories());
            }
        }

        if ($request->search && is_string($request->search)) {
            $query->where(function ($query2) use ($request) {
                $query2->where('title', 'like', '%' . $request->search . '%');
            });
        }

        $sort_type = in_array($request->sort_type, ['asc', 'desc']) ? $request->sort_type : 'asc';

        switch ($request->sort_field) {
            case "view": {
                    $query->orderBy('view', $sort_type);
                    break;
                }
            default: {
                    $query->latest();
                }
        }

        return $query;
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'dislike');
    }

    public function isLikedByUser($userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;

        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function acceptedComments()
    {
        return $this->comments()->where('status', 'accepted');
    }
}
