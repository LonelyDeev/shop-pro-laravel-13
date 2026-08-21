<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationManageUser extends Model
{
    protected $table = 'notification_manage_users';

    protected $guarded = [];

    protected $casts = ['read' => 'boolean'];

    public function notification()
    {
        return $this->belongsTo(NotificationManage::class, 'notification_manage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
