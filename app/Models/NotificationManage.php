<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationManage extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class,'notification_manage_users');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sellers()
    {
        return $this->belongsToMany(Seller::class,'notification_manage_users');
    }
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    public function sellerInfo()
    {
        return $this->belongsTo(SellerInfo::class,'seller_id');
    }
    public function priorityText()
    {
        switch ($this->priority) {
            case "low": {
                $priority=[
                    'title'=>'عادی',
                    'color'=>'success'
                ];
                return $priority;
            }
            case "medium": {
                $priority=[
                    'title'=>'مهم',
                    'color'=>'warning'
                ];
                return $priority;
            }
            case "high": {
                $priority=[
                    'title'=>'خیلی مهم',
                    'color'=>'danger'
                ];
                return $priority;
            }
        }
    }

    public function scopeFilter($query, $request)
    {

        if ($request->priority!="all" and $request->priority) {
            $query->where('priority', $request->priority);
        }


        if ($request->unread=="on") {
            $query->where('read', '0');
        }

        switch ($request->sort) {
            case 'priority': {
                $query->orderby('priority','asc');
                break;
            }
            default: {
                $query->latest();
            }
        }

        return $query;
    }
}
