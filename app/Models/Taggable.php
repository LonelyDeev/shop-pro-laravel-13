<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taggable extends Model
{
    use HasFactory;

    protected $table = 'taggables';

    protected $fillable = [
        'tag_id',
        'taggable_id',
        'taggable_type',
    ];

    public $timestamps = false;

    // رابطه با تگ
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    // رابطه پلی‌مورفیک
    public function taggable()
    {
        return $this->morphTo();
    }
}
