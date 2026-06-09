<?php

namespace App\Http\Controllers\Back;

use App\Models\NotificationManage;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:notifications');
    }

    public function index()
    {
        $notifications = NotificationManage::where('private','all')->orderby('id','desc')->paginate(20);

        return view('back.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users=User::all();
        $sellers=Seller::all();
        return view('back.notifications.create',compact('sellers','users'));
    }

    public function store(Request $request)
    {
         $this->validate($request, [
            'message'         => 'required',
        ]);

        if (!$request->sellers and !$request->sellers){
             $this->validate($request, [
                'users'         => 'required',
            ],[
               'users.required' =>'فیلد های فروشندگان و کاربران نمی تواند همزمان خالی باشد.'
            ]);
        }
        $notification=new NotificationManage();

        if ($request->sellers){
            $sellers=Seller::all()->pluck('id');
            $sellers=$request->seller_id ? $request->seller_id : $sellers;

            $notification->allSellers=$request->seller_id ? 0 : 1;
        }else{
            $sellers=null;
            $notification->allSellers=0;
        }

        if ($request->users){
            $users=User::all()->pluck('id');
            $users=$request->user_id ? $request->user_id : $users;

            $notification->allUsers=$request->user_id ? 0 : 1;
        }else{
            $users=null;
            $notification->allUsers=0;
        }




        $notification->admin_id=Auth::guard('adminPanel')->id();
        $notification->title=$request->title;
        $notification->message=$request->message;
        $notification->priority=$request->priority;
        $notification->popup=$request->popup ? true : false;
        $notification->save();

        $notification->users()->attach($users);
        $notification->sellers()->attach($sellers);

        session()->put('toast-success','اعلان با موفقیت ایجاد شد.');
        return response("success");
    }

    public function show(NotificationManage $notification)
    {
        $users=User::all();
        $sellers=Seller::all();
        $showUsers=$notification->users()->paginate(50);
        $showSellers=$notification->sellers()->paginate(50);

        return view('back.notifications.show', compact('notification','sellers','users','showUsers','showSellers'));
    }

    public function update(NotificationManage $notification, Request $request)
    {
        $this->validate($request, [
            'message'         => 'required',
        ]);

        if (!$request->sellers and !$request->users){
            $this->validate($request, [
                'users'         => 'required',
            ],[
                'users.required' =>'فیلد های فروشندگان و کاربران نمی تواند همزمان خالی باشد.'
            ]);
        }
        if ($request->sellers){
            $sellers=Seller::all()->pluck('id');
            $sellers=$request->seller_id ? $request->seller_id : $sellers;

            $notification->allSellers=$request->seller_id ? 0 : 1;
        }else{
            $sellers=null;
            $notification->allSellers=0;
        }

        if ($request->users){
            $users=User::all()->pluck('id');
            $users=$request->user_id ? $request->user_id : $users;

            $notification->allUsers=$request->user_id ? 0 : 1;
        }else{
            $users=null;
            $notification->allUsers=0;
        }



        $notification->admin_id=Auth::guard('adminPanel')->id();
        $notification->title=$request->title;
        $notification->message=$request->message;
        $notification->priority=$request->priority;
        $notification->popup=$request->popup ? true : false;
        $notification->save();


        $notification->users()->sync($users);
        $notification->sellers()->sync($sellers);



        session()->put('toast-success','اعلان با موفقیت ویرایش شد.');
        return response("success");
    }

    public function destroy(NotificationManage $notification)
    {
        $notification->delete();

        return response('success');
    }

}
