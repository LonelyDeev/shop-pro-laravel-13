<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $table = 'form_fields';

    protected $fillable = [
        'form_id', 'label', 'name', 'type', 'required',
        'placeholder', 'options', 'default_value', 'rules_validation',
        'order', 'class', 'help_text', 'settings', 'display_order',
        'column_class',
        'show_label',
        'label_class',
        'wrapper_class',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'settings' => 'array',
        'show_label' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // دریافت گزینه‌ها برای نمایش
    public function getOptionsArrayAttribute()
    {
        if (is_array($this->options)) {
            return $this->options;
        }
        return json_decode($this->options, true) ?? [];
    }

    public function getColumnClassAttribute($value)
    {
        return $value ?? 'col-md-6';
    }

    // دریافت قوانین اعتبارسنجی
    public function getValidationRulesAttribute()
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        }
        if ($this->rules_validation) {
            $rules[] = $this->rules_validation;
        }

        if ($this->type === 'email') {
            $rules[] = 'email';
        }

        if ($this->type === 'url') {
            $rules[] = 'url';
        }

        return implode('|', $rules);
    }

    // اضافه کردن این متد
    public function getValidationRulesArrayAttribute()
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        }

        if (isset($this->attributes['rules_validation']) && !empty($this->attributes['rules_validation'])) {
            $customRules = explode('|', $this->attributes['rules_validation']);
            $rules = array_merge($rules, $customRules);
        }

        if ($this->type === 'email') {
            $rules[] = 'email';
        }

        if ($this->type === 'url') {
            $rules[] = 'url';
        }

        return $rules;
    }
}
