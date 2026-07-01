<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sms;
use App\Models\Viewer;
use App\Rules\CheckeJdate;
use App\Traits\OrderStatisticsTrait;
use App\Traits\UserStatisticsTrait;
use App\Traits\ViewStatisticsTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class StatisticsController extends Controller
{
    use OrderStatisticsTrait, UserStatisticsTrait, ViewStatisticsTrait;

    public function viewsList()
    {
        $this->authorize('statistics.viewsList');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        $views = Viewer::latest();

        if (auth('adminPanel')->user()->level != 'creator') {
            $views = $views->whereNull('user_id')->orWhere(function ($query) {
                $query->whereHas('user', function ($q1) {
                    $q1->where('level', '!=', 'creator');
                });
            });
        }

        $viewsCount = $views->count();
        $views = $views->paginate(20);

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_views_list',
                'views_count' => $viewsCount,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} لیست بازدیدهای صفحه را مشاهده کرد ({$viewsCount} بازدید)");

        return view('back.statistics.views.viewsList', compact('views'));
    }

    public function views()
    {
        $this->authorize('statistics.views');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_statistics_views',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} صفحه آمار بازدیدها را مشاهده کرد");

        return view('back.statistics.views.index');
    }

    public function viewers()
    {
        $this->authorize('statistics.viewers');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        $viewers = Viewer::latest()->whereDate('created_at', now())->get()->unique('user_id');
        $viewersCount = $viewers->count();

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_viewers_list',
                'viewers_count' => $viewersCount,
                'date' => now()->format('Y-m-d'),
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} لیست بازدیدکنندگان امروز را مشاهده کرد ({$viewersCount} بازدیدکننده)");

        return view('back.statistics.viewers.viewers', compact('viewers'));
    }

    public function orders()
    {
        $this->authorize('statistics.orders');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_statistics_orders',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} صفحه آمار سفارش‌ها را مشاهده کرد");

        return view('back.statistics.orders.index');
    }

    public function products()
    {
        $this->authorize('statistics.product');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_statistics_products',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} صفحه آمار محصولات را مشاهده کرد");

        return view('back.statistics.orders.products');
    }

    public function productTemplate(Request $request)
    {
        $product_name = $request->product_name;

        $request->validate([
            'from_date' => ['nullable', new CheckeJdate()],
            'to_date'   => ['nullable', new CheckeJdate()],
            'ordering'  => ['nullable', 'in:newest,oldest,most_sold,least_sold'],
        ]);

        $from_date = $request->input('from_date');
        $to_date   = $request->input('to_date');
        $ordering  = $request->input('ordering', 'newest');

        if ($from_date) {
            $from_date = Jalalian::fromFormat('Y-m-d', $request->from_date)->toCarbon();
        }
        if ($to_date) {
            $to_date = Jalalian::fromFormat('Y-m-d', $request->to_date)->toCarbon();
        }

        // Subquery برای موجودی — جلوگیری از چند برابر شدن ردیف‌ها در JOIN
        $stockSubquery = DB::table('prices')
            ->selectRaw('product_id, SUM(stock) AS total_stock')
            ->groupBy('product_id');

        $products = OrderItem::selectRaw('
            products.id          AS product_id,
            products.slug        AS product_slug,
            products.title       AS product_title,
            products.image       AS product_image,
            SUM(order_items.quantity) AS total_orders,
            SUM(CASE WHEN orders.status = "paid"  THEN order_items.quantity ELSE 0 END) AS successful_orders,
            SUM(CASE WHEN orders.status != "paid" THEN order_items.quantity ELSE 0 END) AS failed_orders,
            SUM(CASE WHEN DATE(orders.created_at) = CURDATE() THEN order_items.quantity ELSE 0 END) AS today_orders,
            SUM(order_items.quantity * order_items.price) AS total_order_amount,
            SUM(CASE WHEN orders.status = "paid" THEN order_items.quantity * order_items.price ELSE 0 END) AS total_profit,
            COALESCE(stock_data.total_stock, 0) AS available_stock
        ')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders',   'order_items.order_id',   '=', 'orders.id')
            // ✅ LEFT JOIN با subquery به جای JOIN مستقیم
            ->leftJoinSub($stockSubquery, 'stock_data', function ($join) {
                $join->on('stock_data.product_id', '=', 'products.id');
            })
            ->when($product_name, function ($query, $product_name) {
                $decodedProductName = urldecode($product_name);
                $keywords = explode(' ', $decodedProductName);
                foreach ($keywords as $keyword) {
                    if (trim($keyword) !== '') {
                        $query->where('products.title', 'LIKE', "%{$keyword}%");
                    }
                }
            })
            ->when($from_date, function ($query, $from_date) {
                return $query->whereDate('orders.created_at', '>=', $from_date);
            })
            ->when($to_date, function ($query, $to_date) {
                return $query->whereDate('orders.created_at', '<=', $to_date);
            })
            ->groupBy('products.id', 'products.slug', 'products.title', 'products.image', 'stock_data.total_stock')
            ->when($ordering, function ($query, $ordering) {
                switch ($ordering) {
                    case 'newest':
                        return $query->orderByRaw('MAX(orders.created_at) DESC');
                    case 'oldest':
                        return $query->orderByRaw('MAX(orders.created_at) ASC');
                    case 'most_sold':
                        return $query->orderBy('total_orders', 'desc');
                    case 'least_sold':
                        return $query->orderBy('total_orders', 'asc');
                }
            })
            ->paginate(50);

        // ثبت لاگ
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('export')
            ->withProperties([
                'action'        => 'filter_product_statistics',
                'product_name'  => $product_name,
                'from_date'     => $request->from_date,
                'to_date'       => $request->to_date,
                'ordering'      => $ordering,
                'results_count' => $products->total(),
                'ip'            => request()->ip(),
            ])
            ->log("مدیر {$adminName} آمار فروش محصولات را با فیلترهای مشخص شده مشاهده کرد");

        return view('back.statistics.orders.partials.productItemTemplate', [
            'products' => $products,
        ])->render();
    }

    public function users()
    {
        $this->authorize('statistics.users');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_statistics_users',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} صفحه آمار کاربران را مشاهده کرد");

        return view('back.statistics.users.index');
    }

    public function smsLog()
    {
        $this->authorize('statistics.sms');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        $sms = Sms::latest()->paginate(20);
        $smsCount = $sms->total();

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_sms_log',
                'sms_count' => $smsCount,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} لاگ ارسال پیامک‌ها را مشاهده کرد ({$smsCount} پیامک)");

        return view('back.statistics.sms.sms-log', compact('sms'));
    }
}
