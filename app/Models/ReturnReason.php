<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnReason extends Model
{
    protected $table = 'return_reasons';
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('ordering');
    }
}
