<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSetting extends Model
{
    use HasFactory;

    protected $table = 'form_settings';

    protected $fillable = [
        'form_id',
        'form_position',
        'form_width',
        'form_alignment',
        'form_class',
        'custom_css',
        'default_column_class',
        'field_settings',
    ];

    protected $casts = [
        'field_settings' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // متدهای کمکی برای گرفتن کلاس‌های CSS
    public function getFormWidthClassAttribute()
    {
        return [
            'full' => 'col-12',
            'half' => 'col-md-6 col-12',
            'third' => 'col-md-4 col-12',
        ][$this->form_width] ?? 'col-12';
    }

    public function getFormAlignmentClassAttribute()
    {
        return [
            'center' => 'mx-auto',
            'right' => 'ms-auto',
            'left' => 'me-auto',
        ][$this->form_alignment] ?? '';
    }
}
