<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldValue extends Model
{
    protected $guarded = ['id'];

    public function fild()
    {
        return $this->belongsTo(Fild::class, 'field_id');
    }
}
