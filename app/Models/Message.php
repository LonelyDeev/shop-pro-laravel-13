<?php

namespace App\Models;

use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    use HasFactory,Languageable;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function items()
    {
        return $this->hasMany(MessageUser::class);

    }
}
