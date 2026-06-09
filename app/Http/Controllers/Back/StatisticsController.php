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

        $views = Viewer::latest();

        if (auth()->user()->level != 'creator') {
            $views = $views->whereNull('user_id')->orWhere(function ($query) {
                $query->whereHas('user', function ($q1) {
                    $q1->where('level', '!=', 'creator');
                });
            });
        }

        $views = $views->paginate(20);

        return view('back.statistics.views.viewsList', compact('views'));
    }

    public function views()
    {
        $this->authorize('statistics.views');

        return view('back.statistics.views.index');
    }

    public function viewers()
    {
        $this->authorize('statistics.viewers');

        $viewers = Viewer::latest()->whereDate('created_at', now())->get()->unique('user_id');

        return view('back.statistics.viewers.viewers', compact('viewers'));
    }

    public function orders()
    {
        $this->authorize('statistics.orders');

        return view('back.statistics.orders.index');
    }

    public function products()
    {
        $this->authorize('statistics.product');

        return view('back.statistics.orders.products');
    }


    public function productTemplate(Request $request)
    {
        $product_name = $request->product_name;

        $request->validate([
            'from_date' => ['nullable', new CheckeJdate()],
            'to_date'   => ['nullable', new CheckeJdate()],
            'ordering'   => ['nullable', 'in:newest,oldest,most_sold,least_sold'],
        ]);
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $ordering = $request->input('ordering', 'newest');

        if ($from_date) {
            $from_date = Jalalian::fromFormat('Y-m-d', $request->from_date)->toCarbon();
        }
        if ($to_date) {
            $to_date = Jalalian::fromFormat('Y-m-d', $request->to_date)->toCarbon();
        }

        // گرفتن اطلاعات محصولات و سفارش‌ها
        $products = OrderItem::selectRaw('
            products.id AS product_id,
            products.slug AS product_slug,
            products.title AS product_title,
            products.image AS product_image,
            SUM(order_items.quantity) AS total_orders,
            SUM(CASE WHEN orders.status = "paid" THEN order_items.quantity ELSE 0 END) AS successful_orders,
            SUM(CASE WHEN orders.status != "paid" THEN order_items.quantity ELSE 0 END) AS failed_orders,
            SUM(CASE WHEN DATE(orders.created_at) = CURDATE() THEN order_items.quantity ELSE 0 END) AS today_orders,
            SUM(prices.stock) AS available_stock,
            SUM(order_items.quantity * order_items.price) AS total_order_amount,
            SUM(CASE WHEN orders.status = "paid" THEN order_items.quantity * order_items.price ELSE 0 END) AS total_profit
        ')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('prices', 'prices.product_id', '=', 'products.id') // اتصال جدول prices
            ->when($product_name, function ($query, $product_name) {
                // Decode کردن رشته جستجو
                $decodedProductName = urldecode($product_name);

                // شکستن جستجو به کلمات
                $keywords = explode(' ', $decodedProductName);

                // اعمال جستجو برای هر کلمه
                foreach ($keywords as $keyword) {
                    $query->where('products.title', 'LIKE', "%{$keyword}%");
                }

                return $query;
            })
            ->when($from_date, function ($query, $from_date) {
                return $query->whereDate('orders.created_at','>=', $from_date);
            })
            ->when($to_date, function ($query, $to_date) {
                return $query->whereDate('orders.created_at','<=', $to_date);
            })
            ->groupBy('products.id')
        ->when($ordering, function ($query, $ordering) {
            switch ($ordering) {
                case 'newest':
                    $query->orderByRaw('MAX(orders.created_at) DESC');
                    break;
                case 'oldest':
                    $query->orderByRaw('MAX(orders.created_at) asc');
                    break;
                case 'most_sold':
                    $query->orderBy('total_orders', 'desc');
                    break;
                case 'least_sold':
                    $query->orderBy('total_orders', 'asc');
                    break;
            }
        })
            ->paginate(50);

        return view('back.statistics.orders.partials.productItemTemplate', [
            'products' => $products,
        ])->render();
    }

    public function users()
    {
        $this->authorize('statistics.users');

        return view('back.statistics.users.index');
    }

    public function smsLog()
    {
        $this->authorize('statistics.sms');

        $sms = Sms::latest()->paginate(20);

        return view('back.statistics.sms.sms-log', compact('sms'));
    }
}
