<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\Tag;
use App\Models\User;
use App\Models\Viewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    public function index()
    {
        $users_count = Cache::rememberForever('admin.users_count', function () {
            return User::where('level', '!=', 'creator')->count();
        });

        $products_count = Cache::rememberForever('admin.products_count', function () {
            return Product::count();
        });

        $orders_count = Cache::rememberForever('admin.orders_count', function () {
            return Order::count();
        });

        $total_sell = Cache::rememberForever('admin.total_sell', function () {
            return Order::where('status', 'paid')->sum('price');
        });

        $orders=Order::orderby('id','desc')->get()->take(10);
        $reviews = Review::filter()->get()->take(10);
        $view_products=Product::published()->where('view','!=',0)->orderBy('view', 'desc')->take(20)->get();
        $sale_products=Product::published()->where('sell','!=',0)->orderBySale('desc')->take(20)->get();
        $questions=Comment::whereNull('comment_id')->latest()->where(['status'=>'accepted','commentable_type'=>'App\Models\Product'])->take(20)->get();

        $active_users=[];
        foreach (User::take(20)->get() as $active_user_id){
            $users=Viewer::where('user_id','!=',null)->where('user_id',$active_user_id->id)->orderby('id','desc')->first();
            if ($users){
                array_push($active_users,$users);
            }
        }
        $active_sellers=[];
        foreach (Seller::take(20)->get() as $active_seller_id){
            $sellers=Viewer::where('seller_id','!=',null)->where('seller_id',$active_seller_id->id)->orderby('id','desc')->first();
            if ($sellers){
                array_push($active_sellers,$sellers);
            }
        }

        return view('back.index', compact(
            'users_count',
            'products_count',
            'orders_count',
            'total_sell',
            'orders',
            'reviews',
            'view_products',
            'sale_products',
            'questions',
            'active_users',
            'active_sellers',
        ));
    }

    public function get_tags(Request $request)
    {
        $tags = Tag::detectLang()->where('name', 'like', '%' . $request->term . '%')
            ->latest()
            ->take(5)
            ->pluck('name')
            ->toArray();

        return response()->json($tags);
    }

    public function getLabels(Request $request)
    {
        $labels = Label::detectLang()->where('title', 'like', '%' . $request->term . '%')
            ->latest()
            ->take(5)
            ->pluck('title')
            ->toArray();

        return response()->json($labels);
    }

    public function login()
    {
        return view('back.auth.login');
    }

    public function notifications()
    {
        $notifications = auth('adminPanel')->user()->notifications()->paginate(15);

        auth('adminPanel')->user()->unreadNotifications->markAsRead();

        return view('back.notifications.panel', compact('notifications'));
    }

    public function fileManager()
    {
        $this->authorize('file-manager');

        return view('back.file-manager');
    }

    public function fileManagerIframe()
    {
        $this->authorize('file-manager');

        return view('back.file-manager-iframe');
    }
    public function cache_clear()
    {
        session()->put('toast-success','کش با موفقیت پاک شد');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:cache');
        Artisan::call('view:clear');

        return redirect()->back();
    }
}
