<?php

namespace App\Traits;

use App\Models\Tag;
use App\Observers\TaggableObserver;

trait Taggable
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getGetTagsAttribute()
    {
        return implode(',', $this->tags()->pluck('name')->toArray());
    }

    // به جای bootTaggable از initialize استفاده کن
    public function initializeTaggable()
    {
        // هیچ observe ای اینجا نگذار
        // فقط اگر تنظیمات خاصی داری، اینجا بنویس
    }
}
