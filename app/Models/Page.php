<?php

namespace App\Models;

use App\Traits\Languageable;
use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Page extends Model
{
    use sluggable, Taggable, Languageable;

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

    public function link()
    {
        return route('front.pages.show', ['page' => $this], false);
    }
}
