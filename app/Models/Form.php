<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Taggable;
class Form extends Model
{
    use HasFactory, Taggable;

    protected $table = 'forms';

    protected $fillable = [
        'title', 'slug', 'description','meta_title','meta_description', 'published',
        'success_message', 'button_text', 'email_notify', 'settings'
    ];

    protected $casts = [
        'published' => 'boolean',
        'settings' => 'array',
    ];

    // روابط
    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function setting()
    {
        return $this->hasOne(FormSetting::class);
    }
    // اسکوپ‌ها
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // متدهای کمکی
    public function getSubmissionsCountAttribute()
    {
        return $this->submissions()->count();
    }

    public function getTodaySubmissionsCountAttribute()
    {
        return $this->submissions()->whereDate('submitted_at', today())->count();
    }
}
