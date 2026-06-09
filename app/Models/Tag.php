<?php

namespace App\Models;

use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Tag extends Model
{
    use sluggable, Languageable;

    protected $guarded = ['id'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }
    // اصلاح رابطه پلی‌مورفیک - معکوس
    public function taggables()
    {
        return $this->hasMany(Taggable::class, 'tag_id');
    }

    // رابطه پلی‌مورفیک برای دریافت مدل‌های مرتبط
    public function taggable()
    {
        return $this->morphTo();
    }

    // دریافت تعداد استفاده در یک مدل خاص
    public function getUsageCount($type = null)
    {
        $query = $this->taggables();

        if ($type) {
            $query->where('taggable_type', $type);
        }

        return $query->count();
    }

    // دریافت تمام مدل‌های استفاده شده
    public function getUsageDetails()
    {
        $details = [];
        $grouped = $this->taggables()->get()->groupBy('taggable_type');

        foreach ($grouped as $type => $items) {
            $modelName = class_basename($type);
            $details[] = [
                'model' => $modelName,
                'count' => $items->count(),
                'items' => $items->pluck('taggable_id')->toArray(),
            ];
        }

        return $details;
    }

    // اسکوپ‌ها
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('slug', 'like', "%{$keyword}%");
    }

    // اکشن‌ها
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'taggable');
    }

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'taggable');
    }
}
