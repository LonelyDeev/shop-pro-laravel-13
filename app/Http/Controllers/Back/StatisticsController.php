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
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

    public function smsLog(Request $request)
    {
        $this->authorize('statistics.sms');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        // ---------- فیلترها ----------
        $filters = [
            'mobile' => trim((string) $request->query('mobile')),
            'period' => (string) $request->query('period', ''),
        ];

        $query = Sms::query();

        if ($filters['mobile'] !== '') {
            $query->where('mobile', 'like', '%' . $filters['mobile'] . '%');
        }

        // اگر ستون نوع دارید، این را هم اضافه کنید:
        // if ($request->filled('type')) $query->where('type', $request->type);

        switch ($filters['period']) {
            case 'today': $query->whereDate('created_at', today()); break;
            case 'week':  $query->where('created_at', '>=', now()->subDays(7)); break;
            case 'month': $query->where('created_at', '>=', now()->subDays(30)); break;
        }

        $sms = $query->latest()
            ->paginate(20)
            ->appends(request()->except('page'));

        // ---------- آمار (مستقل از فیلتر) ----------
        $stats = [
            'total' => Sms::count(),
            'today' => Sms::whereDate('created_at', today())->count(),
            'week'  => Sms::where('created_at', '>=', now()->subDays(7))->count(),
            'month' => Sms::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action'    => 'view_sms_log',
                'sms_count' => $sms->total(),
                'filters'   => array_filter($filters),
                'ip'        => request()->ip()
            ])
            ->log("مدیر {$adminName} لاگ ارسال پیامک‌ها را مشاهده کرد ({$sms->total()} پیامک)");

        return view('back.statistics.sms.sms-log', compact('sms', 'stats', 'filters'));
    }

    public function viewers(Request $request)
    {
        $this->authorize('statistics.viewers');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        // ---------- تعیین بازه ----------
        $period = (string) $request->query('period', 'daily');
        [$from, $to] = $this->resolveViewersPeriod($request, $period);

        // ---------- داده‌ها ----------
        $rows = Viewer::query()
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        // یکتاسازی: هر کاربر یک‌بار، مهمان‌ها بر اساس IP
        $visitors = $rows->unique(fn ($v) => $v->user_id ?: 'ip:' . $v->ip)->values();

        // ---------- آمار ----------
        $stats = [
            'unique' => $visitors->count(),
            'views'  => $rows->count(),
            'users'  => $visitors->whereNotNull('user_id')->count(),
            'guests' => $visitors->whereNull('user_id')->count(),
        ];

        // ---------- نمودار ----------
        $chart = $this->buildViewersChart($rows, $from, $to);

        // ---------- صفحه‌بندی دستی (چون unique روی کالکشن است) ----------
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $items   = $visitors->slice(($page - 1) * $perPage, $perPage)->values();

        $viewers = new LengthAwarePaginator(
            $items,
            $visitors->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('view')
            ->withProperties([
                'action'  => 'view_viewers_list',
                'period'  => $period,
                'range'   => [$from->toDateTimeString(), $to->toDateTimeString()],
                'viewers' => $stats['unique'],
                'ip'      => request()->ip()
            ])
            ->log("مدیر {$adminName} لیست بازدیدکنندگان را مشاهده کرد (بازه: {$period} — {$stats['unique']} بازدیدکننده)");

        return view('back.statistics.viewers.viewers', compact('viewers', 'stats', 'chart', 'period', 'from', 'to'));
    }

    /**
     * تبدیل بازه‌های پریود + بازه دلخواه شمسی به بازه میلادی
     */
    private function resolveViewersPeriod(Request $request, string &$period): array
    {
        $period = in_array($period, ['daily', 'weekly', 'monthly', 'yearly', 'custom']) ? $period : 'daily';

        $from = match ($period) {
            'weekly'  => now()->subDays(6)->startOfDay(),
            'monthly' => now()->subDays(29)->startOfDay(),
            'yearly'  => now()->subDays(364)->startOfDay(),
            default   => now()->startOfDay(),
        };
        $to = now();

        if ($period === 'custom') {
            $from = $this->parseJalaliDate($request->query('from_date'));
            $to   = $this->parseJalaliDate($request->query('to_date'));

            // بازه نامعتبر → برگرد به حالت روزانه
            if (! $from || ! $to || $from->gt($to)) {
                $period = 'daily';
                [$from, $to] = [now()->startOfDay(), now()];
            } else {
                $from = $from->startOfDay();
                $to   = $to->endOfDay();
            }
        }

        return [$from, $to];
    }

    /**
     * تبدیل تاریخ شمسی (خروجی persian_date_picker) به Carbon میلادی
     * پشتیبانی از Verta و Morilog Jalali + ارقام فارسی/عربی
     */
    private function parseJalaliDate(?string $value): ?Carbon
    {
        if (! $value || ! trim($value)) {
            return null;
        }

        // ارقام فارسی/عربی → انگلیسی + یکسان‌سازی جداکننده
        $value = strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '-' => '/',
        ]);

        // Verta
        if (class_exists(\Verta::class)) {
            try {
                if (method_exists(\Verta::class, 'parseJalali')) {
                    return \Verta::parseJalali($value)->datetime();
                }
                [$y, $m, $d] = array_pad(preg_split('/[\/]/', $value), 3, 1);

                return \Verta::createJalali((int) $y, (int) $m, (int) $d)->datetime();
            } catch (\Throwable $e) {
            }
        }

        // Morilog Jalali
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            try {
                return \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $value)->toCarbon();
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    /**
     * داده‌ی نمودار: روزانه (بازه تا ۳۱ روز) یا ماهانه (بازه‌های بزرگ‌تر)
     */
    private function buildViewersChart($rows, Carbon $from, Carbon $to): array
    {
        $months = [1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        $chart  = [];

        if ($from->diffInDays($to) <= 31) {
            $grouped = $rows->groupBy(fn ($v) => $v->created_at->format('Y-m-d'));

            foreach (CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay()) as $day) {
                $chart[] = [
                    'label' => (string) jdate($day)->day,
                    'title' => substr((string) jdate($day), 0, 10),
                    'total' => $grouped->get($day->format('Y-m-d'))?->count() ?? 0,
                ];
            }
        } else {
            $grouped = $rows->groupBy(fn ($v) => $v->created_at->format('Y-m'));

            foreach (CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to->copy()->startOfMonth()) as $month) {
                $chart[] = [
                    'label' => $months[(int) jdate($month)->month] ?? '',
                    'title' => $months[(int) jdate($month)->month] . ' ' . jdate($month)->year,
                    'total' => $grouped->get($month->format('Y-m'))?->count() ?? 0,
                ];
            }
        }

        return $chart;
    }

}
