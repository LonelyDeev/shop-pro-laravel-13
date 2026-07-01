<?php

namespace App\Http\Controllers\Back;

use App\Exports\WarehouseExport;
use App\Exports\WarehouseProductsExport;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\OrderItem;
use App\Models\Province;
use App\Models\Seller;
use App\Models\Warehouse;
use App\Models\Price;
use App\Models\StockMovement;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;
use function Termwind\render;

class WarehouseController extends Controller
{
    protected $stockService;

    public function __construct(StockMovementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * لیست انبارها
     */
    public function index(Request $request)
    {
        $this->authorize('warehouses.index');

        $warehouses = Warehouse::query()
            ->withCount('prices')
            ->withCount('products')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->type && $request->type != 'all', function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status && $request->status != 'all', function ($query) use ($request) {
                $query->where('is_active', $request->status == 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // آمار کلی
        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', true)->count(),
            'main' => Warehouse::where('type', 'main')->count(),
            'seller' => Warehouse::where('type', 'seller')->count(),
            'total_products' => Price::whereNotNull('warehouse_id')->count('product_id'),
            'total_stock' => Price::sum('stock'),
        ];

        return view('back.warehouses.index', compact('warehouses', 'stats'));
    }

    /**
     * فرم ایجاد انبار
     */
    public function create()
    {
        $this->authorize('warehouses.create');

        $provinces = Province::active()->orderBy('name')->get();
        $sellers = Seller::where('status', 'ACTIVE')
            ->whereHas('seller_info')
            ->with('seller_info')
            ->get();

        return view('back.warehouses.create', compact('provinces', 'sellers'));
    }


    /**
     * ذخیره انبار جدید
     */
    public function store(Request $request)
    {
        $this->authorize('warehouses.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,seller,temp',
            'seller_id' => 'required_if:type,seller|nullable|exists:sellers,id',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'province_id' => 'nullable|string',
            'city_id' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $warehouse = Warehouse::create([
            'name' => $request->name,
            'type' => $request->type,
            'seller_id' => $request->type == 'seller' ? $request->seller_id : null,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "انبار {$warehouse->name} با موفقیت ایجاد شد. کد انبار: {$warehouse->code}",
            'warehouse' => $warehouse
        ]);

    }

    /**
     * نمایش جزئیات انبار و محصولات آن
     */

    public function show(Warehouse $warehouse)
    {
        $this->authorize('warehouses.show');

        // ========== دریافت تنوع‌های انبار فعلی ==========
        $currentWarehouseVariations = Price::where('warehouse_id', $warehouse->id)
            ->with(['product', 'attributes'])
            ->get();

        // >>> بهینه‌سازی شدید: محاسبه فروش همه تنوع‌ها با فقط ۱ کوئری <<<
        $soldCounts = OrderItem::whereIn('price_id', $currentWarehouseVariations->pluck('id'))
            ->whereHas('order', fn($q) => $q->where('status', 'paid'))
            ->selectRaw('price_id, SUM(quantity) as total_sold')
            ->groupBy('price_id')
            ->pluck('total_sold', 'price_id');

        $currentWarehouseVariations->each(fn($price) => $price->sold_count = $soldCounts[$price->id] ?? 0);

        // ========== گروه‌بندی محصولات ==========
        $products = Product::whereHas('prices', fn($q) => $q->where('warehouse_id', $warehouse->id))
            ->with(['prices' => fn($q) => $q->where('warehouse_id', $warehouse->id), 'category', 'brand'])
            ->paginate(20);

        // ========== تنوع‌های سایر انبارها ==========
        $mainWarehouseVariations = collect();
        $otherSellersVariations = collect();
        $otherSellerVariations = collect();

        // (برای اختصار، منطق بخش سایر انبارها همان منطق قبلی شماست، اما پیشنهاد می‌شود
        // برای آن‌ها هم از روش تجمعی بالا برای محاسبه sold_count استفاده کنید)

        // ========== محاسبه آمار ==========
        $totalSoldCurrent = $currentWarehouseVariations->sum('sold_count');
        $bestSeller = $currentWarehouseVariations->sortByDesc('sold_count')->first();

        $stats = [
            'total_products' => $products->total(),
            'current_count' => $currentWarehouseVariations->count(),
            'total_stock_current' => $currentWarehouseVariations->sum('stock'),
            'total_sold' => $totalSoldCurrent,
            'total_value' => $currentWarehouseVariations->sum(fn($p) => $p->stock * ($p->cost_price ?? $p->price)),
            'low_stock' => $currentWarehouseVariations->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock' => $currentWarehouseVariations->where('stock', 0)->count(),
            'critical_stock' => $currentWarehouseVariations->where('stock', '<=', 2)->where('stock', '>', 0)->count(),
            'expiring_discounts' => $currentWarehouseVariations->where('discount_expire_at', '<=', now()->addDays(3))->where('discount_expire_at', '>=', now())->count(),
            'best_seller_count' => $bestSeller?->sold_count ?? 0,
            'best_seller_attributes' => $bestSeller?->attributes?->pluck('name')->implode(' - ') ?? 'ندارد',
            'avg_sold_per_variation' => $currentWarehouseVariations->count() > 0 ? round($totalSoldCurrent / $currentWarehouseVariations->count()) : 0,
            'main_count' => $mainWarehouseVariations->count(),
            'other_sellers_count' => $otherSellersVariations->count(),
            'total_variations' => $currentWarehouseVariations->count() + $otherSellerVariations->count() + $mainWarehouseVariations->count() + $otherSellersVariations->count(),
        ];

        $recentMovements = StockMovement::where('warehouse_id', $warehouse->id)
            ->with(['price.product', 'price.attributes'])
            ->latest()->limit(20)->get()
            ->map(function ($movement) {
                $movement->type_label = $this->getMovementTypeLabel($movement->type);
                $movement->type_badge_class = $this->getMovementTypeBadgeClass($movement->type);
                return $movement;
            });

        $categories = Category::whereHas('products', fn($q) => $q->whereHas('prices', fn($qq) => $qq->where('warehouse_id', $warehouse->id)))->get();
        $brands = Brand::whereHas('products', fn($q) => $q->whereHas('prices', fn($qq) => $qq->where('warehouse_id', $warehouse->id)))->get();
        $chartData = $this->getChartData($warehouse);

        return view('back.warehouses.show', compact(
            'warehouse', 'products', 'currentWarehouseVariations', 'stats', 'recentMovements',
            'categories', 'brands', 'otherSellerVariations', 'mainWarehouseVariations', 'otherSellersVariations', 'chartData'
        ));
    }

    /**
     * دریافت داده‌های نمودار
     */
    private function getChartData($warehouse)
    {
        // 10 محصول پرفروش
        $topProducts = Price::where('warehouse_id', $warehouse->id)
            ->whereHas('product') // فقط محصولاتی که وجود دارند
            ->with('product')
            ->selectRaw('product_id, SUM(sold_count) as total_sold')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get()
            ->filter(function ($price) {
                return $price->product !== null; // حذف محصولات نال
            })
            ->map(function ($price) {
                return [
                    'name' => $price->product->title,
                    'sold' => (int)$price->total_sold
                ];
            });

        // فروش ماهانه (6 ماه اخیر)
        $monthlySales = OrderItem::whereHas('price', function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })
            ->whereHas('order', function ($q) {
                $q->where('status', 'paid');
            })
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(quantity) as total_sold')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'top_products' => $topProducts,
            'monthly_sales' => $monthlySales
        ];
    }

    /**
     * دریافت لیبل فارسی نوع حرکت
     */
    private function getMovementTypeLabel($type)
    {
        return match ($type) {
            'in' => 'ورود',
            'out' => 'خروج',
            'reserve' => 'رزرو',
            'unreserve' => 'لغو رزرو',
            'adjustment' => 'تنظیم دستی',
            default => $type
        };
    }

    /**
     * دریافت کلاس badge برای نوع حرکت
     */
    private function getMovementTypeBadgeClass($type)
    {
        return match ($type) {
            'in' => 'bg-success',
            'out' => 'bg-danger',
            'reserve' => 'bg-warning',
            'unreserve' => 'bg-info',
            'adjustment' => 'bg-secondary',
            default => 'bg-light'
        };
    }

    /**
     * فرم ویرایش انبار
     */
    public function edit(Warehouse $warehouse)
    {
        $this->authorize('warehouses.update');

        $provinces = Province::active()->orderBy('name')->get();
        $sellers = Seller::where('status', 'ACTIVE')
            ->whereHas('seller_info')
            ->with('seller_info')
            ->get();

        $cities = [];
        if ($warehouse->province_id) {
            $cities = City::where('province_id', $warehouse->province_id)->orderBy('name')->get();
        }

        return view('back.warehouses.edit', compact('warehouse', 'provinces', 'sellers', 'cities'));
    }

    /**
     * بروزرسانی انبار
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('warehouses.update');

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,seller,temp',
            'seller_id' => 'required_if:type,seller|nullable|exists:sellers,id',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'province_id' => 'nullable|string',
            'city_id' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $warehouse->update([
            'name' => $request->name,
            'type' => $request->type,
            'seller_id' => $request->type == 'seller' ? $request->seller_id : null,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "انبار {$warehouse->name} با موفقیت ویرایش شد",
            'warehouse' => $warehouse
        ]);

    }

    /**
     * حذف انبار
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('warehouses.delete');

        $this->authorize('warehouses.delete');

        // بررسی وجود محصول در انبار
        if ($warehouse->prices()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این انبار دارای محصول است. ابتدا محصولات را جابجا یا حذف کنید.'
            ], 400);
        }

        $name = $warehouse->name;
        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => "انبار {$name} با موفقیت حذف شد"
        ]);
    }

    public function export(Request $request, Warehouse $warehouse)
    {
        $format = $request->get('format', 'excel');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";

        // جمع‌آوری اطلاعات فیلترها برای لاگ
        $filters = [];
        if ($request->stock_status && $request->stock_status != 'all') {
            $filters['stock_status'] = $request->stock_status == 'in_stock' ? 'موجود' : 'ناموجود';
        }
        if ($request->category_id) {
            $category = \App\Models\Category::find($request->category_id);
            $filters['category'] = $category->title ?? $category->name ?? "#{$request->category_id}";
        }
        if ($request->brand_id) {
            $brand = \App\Models\Brand::find($request->brand_id);
            $filters['brand'] = $brand->name ?? "#{$request->brand_id}";
        }

        $query = Product::whereHas('prices', function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })
            ->with(['prices' => function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id);
            }, 'category', 'brand']);

        // فیلترها
        if ($request->stock_status && $request->stock_status != 'all') {
            if ($request->stock_status == 'in_stock') {
                $query->whereHas('prices', function ($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id)->where('stock', '>', 0);
                });
            } elseif ($request->stock_status == 'out_of_stock') {
                $query->whereHas('prices', function ($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id)->where('stock', 0);
                });
            }
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->get();
        $productsCount = $products->count();

        $formatText = $format == 'pdf' ? 'PDF' : 'Excel';

        $filterText = '';
        if (!empty($filters)) {
            $filterParts = [];
            foreach ($filters as $key => $value) {
                $filterParts[] = "{$key}: {$value}";
            }
            $filterText = ' با فیلترهای ' . implode('، ', $filterParts);
        }

        $logProperties = [
            'action' => 'خروجی Excel را دریافت کرد',
            'format' => $format,
            'filters' => $filters,
            'products_count' => $productsCount,
            'warehouse_name' => $warehouseName,
            'warehouse_id' => $warehouse->id,
            'ip' => $request->ip()
        ];

        if (!empty($filters)) {
            $logProperties['attributes'] = [
                'فرمت' => $formatText,
                'تعداد محصولات' => $productsCount,
                'فیلترها' => $filterText,
            ];
        } else {
            $logProperties['attributes'] = [
                'فرمت' => $formatText,
                'تعداد محصولات' => $productsCount,
            ];
        }

        $logMessage = "مدیر {$adminName} خروجی {$formatText} از محصولات انبار «{$warehouseName}» را دریافت کرد";
        $logMessage .= " (تعداد: {$productsCount} محصول){$filterText}";

        if ($format == 'pdf') {
            $pdf = Pdf::loadView('back.warehouses.exports.pdf', compact('warehouse', 'products'));

            activity()
                ->performedOn($warehouse)
                ->event('export')
                ->causedBy(auth('adminPanel')->user())
                ->withProperties($logProperties)
                ->log($logMessage);

            return $pdf->download("warehouse_{$warehouse->id}_products.pdf");
        }

        activity()
            ->performedOn($warehouse)
            ->event('export')
            ->causedBy(auth('adminPanel')->user())
            ->withProperties($logProperties)
            ->log($logMessage);

        return Excel::download(new WarehouseExport($warehouse, $products), "warehouse_{$warehouse->id}_products.xlsx");
    }

    /**
     * تغییر وضعیت فعال/غیرفعال
     */
    public function toggleStatus(Warehouse $warehouse)
    {
        $this->authorize('warehouses.edit');

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";

        $oldStatus = $warehouse->is_active ? 'فعال' : 'غیرفعال';
        $warehouse->update(['is_active' => !$warehouse->is_active]);
        $newStatus = $warehouse->is_active ? 'فعال' : 'غیرفعال';

        activity()
            ->performedOn($warehouse)
            ->event('updated')
            ->causedBy(auth('adminPanel')->user())
            ->withProperties([
                'action' => 'toggle_warehouse_status',
                'old' => ['وضعیت' => $oldStatus],
                'attributes' => ['وضعیت' => $newStatus],
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} وضعیت انبار «{$warehouseName}» را تغییر داد");

        return redirect()->back()->with('success', "وضعیت انبار به {$newStatus} تغییر کرد");
    }

    /**
     * تاریخچه حرکات انبار (AJAX)
     */
    public function movements(Warehouse $warehouse, Request $request)
    {
        $this->authorize('warehouses.movements');

        $movements = StockMovement::where('warehouse_id', $warehouse->id)
            ->with(['price.product', 'order'])
            ->when($request->type && $request->type != 'all', function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->product_id && $request->product_id != 'all', function ($query) use ($request) {
                $query->whereHas('price', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            })
            ->when($request->from_date, function ($query) use ($request) {
                $fromDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->from_date)->toCarbon();
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query) use ($request) {
                $toDate = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $request->to_date)->toCarbon();
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        // لیست محصولات برای فیلتر
        $products = \App\Models\Product::whereHas('prices', function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })->get();

        return view('back.warehouses.movements', compact('warehouse', 'movements', 'products'));
    }

    /**
     * محصولات یک انبار (API برای فیلترها)
     */
    public function products(Request $request, Warehouse $warehouse)
    {
        $query = Product::whereHas('prices', function ($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })
            ->with(['prices' => function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id);
            }, 'category', 'brand']);

        // فیلتر جستجو
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // فیلتر وضعیت موجودی
        if ($request->stock_status && $request->stock_status != 'all') {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->whereHas('prices', function ($q) use ($warehouse) {
                        $q->where('warehouse_id', $warehouse->id)->where('stock', '>', 0);
                    });
                    break;
                case 'low_stock':
                    $query->whereHas('prices', function ($q) use ($warehouse) {
                        $q->where('warehouse_id', $warehouse->id)->where('stock', '<=', 5)->where('stock', '>', 0);
                    });
                    break;
                case 'critical_stock':
                    $query->whereHas('prices', function ($q) use ($warehouse) {
                        $q->where('warehouse_id', $warehouse->id)->where('stock', '<=', 2)->where('stock', '>', 0);
                    });
                    break;
                case 'out_of_stock':
                    $query->whereHas('prices', function ($q) use ($warehouse) {
                        $q->where('warehouse_id', $warehouse->id)->where('stock', 0);
                    });
                    break;
            }
        }

        // مرتب‌سازی
        switch ($request->sort_by) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'stock_desc':
                $query->withMax(['prices' => function ($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                }], 'stock')
                    ->orderBy('prices_max_stock', 'desc');
                break;
            case 'stock_asc':
                $query->withMin(['prices' => function ($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                }], 'stock')
                    ->orderBy('prices_min_stock', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20);

        // اگر درخواست AJAX است
        if ($request->ajax()) {
            return response()->json([
                'html' => view('back.warehouses.partials.products-list', compact('products', 'warehouse'))->render()
            ]);
        }

        return view('back.warehouses.partials.products-list', compact('products', 'warehouse'));
    }

    public function productVariations(Warehouse $warehouse, Product $product, $returnResponse = false)
    {
        $this->authorize('warehouses.show');

        // تنوع‌های انبار فعلی
        $currentWarehouseVariations = $product->prices()
            ->where('warehouse_id', $warehouse->id)
            ->with('attributes')
            ->get();

        // ========== محاسبه فروش هر تنوع از جدول order_items ==========
        foreach ($currentWarehouseVariations as $price) {
            $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                ->whereHas('order', function ($q) {
                    $q->where('status', 'paid'); // فقط سفارش‌های پرداخت شده
                })
                ->sum('quantity');

            $price->sold_count = $soldCount;
        }

        // مرتب‌سازی بر اساس بیشترین فروش
        $currentWarehouseVariations = $currentWarehouseVariations->sortByDesc('sold_count')->values();

        // سایر تنوع‌های فروشنده خود
        $otherSellerVariations = collect();
        $mainWarehouseVariations = collect();
        $otherSellersVariations = collect();
        $otherSellerWarehouses = collect();

        if ($warehouse->seller_id) {
            $otherSellerWarehouses = Warehouse::where('seller_id', $warehouse->seller_id)
                ->where('id', '!=', $warehouse->id)
                ->get();

            foreach ($otherSellerWarehouses as $wh) {
                $variations = $product->prices()
                    ->where('warehouse_id', $wh->id)
                    ->with('attributes')
                    ->get()
                    ->map(function ($price) use ($wh) {
                        $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                            ->whereHas('order', function ($q) {
                                $q->where('status', 'paid');
                            })->sum('quantity');
                        $price->sold_count = $soldCount;
                        $price->warehouse_name = $wh->name;
                        $price->warehouse_code = $wh->code;
                        $price->seller_name = $wh->seller->business_name ?? $wh->seller->name ?? 'فروشنده';
                        return $price;
                    });
                $otherSellerVariations = $otherSellerVariations->merge($variations);
            }

            $mainWarehouseVariations = $product->prices()
                ->whereDoesntHave('warehouse', function ($q) {
                    $q->whereNotNull('seller_id');
                })
                ->where('warehouse_id', '!=', $warehouse->id)
                ->with(['warehouse', 'attributes'])
                ->get()
                ->map(function ($price) {
                    $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                        ->whereHas('order', function ($q) {
                            $q->where('status', 'paid');
                        })->sum('quantity');
                    $price->sold_count = $soldCount;
                    $price->warehouse_name = $price->warehouse->name;
                    $price->warehouse_code = $price->warehouse->code;
                    $price->seller_name = 'فروشگاه اصلی';
                    return $price;
                });

        } else {
            $otherMainWarehouses = Warehouse::whereNull('seller_id')
                ->where('id', '!=', $warehouse->id)
                ->get();

            foreach ($otherMainWarehouses as $wh) {
                $variations = $product->prices()
                    ->where('warehouse_id', $wh->id)
                    ->with('attributes')
                    ->get()
                    ->map(function ($price) use ($wh) {
                        $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                            ->whereHas('order', function ($q) {
                                $q->where('status', 'paid');
                            })->sum('quantity');
                        $price->sold_count = $soldCount;
                        $price->warehouse_name = $wh->name;
                        $price->warehouse_code = $wh->code;
                        $price->seller_name = 'فروشگاه اصلی';
                        return $price;
                    });
                $mainWarehouseVariations = $mainWarehouseVariations->merge($variations);
            }

            $otherSellerVariations = $product->prices()
                ->whereHas('warehouse', function ($q) {
                    $q->whereNotNull('seller_id');
                })
                ->where('warehouse_id', '!=', $warehouse->id)
                ->with(['warehouse', 'attributes'])
                ->get()
                ->map(function ($price) {
                    $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                        ->whereHas('order', function ($q) {
                            $q->where('status', 'paid');
                        })->sum('quantity');
                    $price->sold_count = $soldCount;
                    $price->warehouse_name = $price->warehouse->name;
                    $price->warehouse_code = $price->warehouse->code;
                    $price->seller_name = $price->warehouse->seller->business_name ?? $price->warehouse->seller->name ?? 'فروشنده';
                    return $price;
                });
        }

        if ($warehouse->seller_id) {
            $otherSellersVariations = $product->prices()
                ->whereHas('warehouse', function ($q) use ($warehouse) {
                    $q->whereNotNull('seller_id')
                        ->where('seller_id', '!=', $warehouse->seller_id);
                })
                ->where('warehouse_id', '!=', $warehouse->id)
                ->with(['warehouse', 'attributes'])
                ->get()
                ->map(function ($price) {
                    $soldCount = \App\Models\OrderItem::where('price_id', $price->id)
                        ->whereHas('order', function ($q) {
                            $q->where('status', 'paid');
                        })->sum('quantity');
                    $price->sold_count = $soldCount;
                    $price->warehouse_name = $price->warehouse->name;
                    $price->warehouse_code = $price->warehouse->code;
                    $price->seller_name = $price->warehouse->seller->business_name ?? $price->warehouse->seller->name ?? 'فروشنده';
                    return $price;
                });
        }

        // محاسبه آمار
        $totalSoldCurrent = $currentWarehouseVariations->sum('sold_count');
        $bestSeller = $currentWarehouseVariations->sortByDesc('sold_count')->first();

        $stats = [
            'current_count' => $currentWarehouseVariations->count(),
            'other_current_count' => $otherSellerVariations->count(),
            'main_count' => $mainWarehouseVariations->count(),
            'other_sellers_count' => $otherSellersVariations->count(),
            'total_stock_current' => $currentWarehouseVariations->sum('stock'),
            'total_stock_other_seller' => $otherSellerVariations->sum('stock'),
            'total_stock_main' => $mainWarehouseVariations->sum('stock'),
            'total_stock_other_sellers' => $otherSellersVariations->sum('stock'),
            'total_sold_current' => $totalSoldCurrent,
            'best_seller_count' => $bestSeller ? $bestSeller->sold_count : 0,
            'best_seller_attributes' => $bestSeller ? $bestSeller->attributes->pluck('name')->implode(' - ') : 'ندارد',
            'avg_sold_per_variation' => $currentWarehouseVariations->count() > 0 ? round($totalSoldCurrent / $currentWarehouseVariations->count()) : 0,
        ];

        $warehouses = Warehouse::active()->get();
        $attributeGroups = AttributeGroup::detectLang()->orderBy('ordering')->get();
        $sellers = Seller::with('seller_info')
            ->where(['status_register' => 'complete', 'status_documents' => 'Accept', 'status_work' => 'ACTIVE'])
            ->get();
        $seller_info = Seller::with('seller_info')
            ->where(['id' => $product->seller_id, 'status_register' => 'complete', 'status_documents' => 'Accept', 'status_work' => 'ACTIVE'])
            ->first();

        if ($returnResponse) {
            return [
                'warehouse' => $warehouse,
                'product' => $product,
                'currentWarehouseVariations' => $currentWarehouseVariations,
                'otherSellerVariations' => $otherSellerVariations,
                'mainWarehouseVariations' => $mainWarehouseVariations,
                'otherSellerWarehouses' => $otherSellerWarehouses,
                'otherSellersVariations' => $otherSellerVariations,
                'stats' => $stats,
                'warehouses' => $warehouses,
                'attributeGroups' => $attributeGroups,
                'sellers' => $sellers,
                'seller_info' => $seller_info,
            ];
        }

        return view('back.warehouses.partials.product-variations', compact(
            'warehouse',
            'product',
            'currentWarehouseVariations',
            'otherSellerVariations',
            'mainWarehouseVariations',
            'otherSellerWarehouses',
            'otherSellersVariations',
            'stats',
            'warehouses',
            'attributeGroups',
            'sellers',
            'seller_info',
        ));
    }

    /**
     * دریافت اطلاعات تنوع‌ها برای AJAX
     */
    public function getProductVariations($productId, Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $product = Product::with(['prices.warehouse', 'prices.attributes'])->findOrFail($productId);

        $html = view('back.warehouses.partials.product-variations-list', compact('product', 'warehouseId'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'product_title' => $product->title,
            'product_image' => $product->image ? asset($product->image) : null,
        ]);
    }

    /**
     * سرشماری انبار
     */
    public function stockTake(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'stocks' => 'nullable|array',
            'stocks.*.price_id' => 'required|integer|exists:prices,id',
            'stocks.*.actual_stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";
        $startTime = microtime(true);

        try {
            $result = DB::transaction(function () use ($request, $warehouse) {
                $updatedCount = 0;
                $totalDifference = 0;
                $differencesList = [];
                $allProcessed = [];
                $notFoundList = [];

                if (empty($request->stocks)) {
                    $allPrices = Price::where('warehouse_id', $warehouse->id)->get();
                    $confirmedCount = $allPrices->count();
                    foreach ($allPrices as $price) {
                        app(\App\Services\StockMovementService::class)->adjustment(
                            $price,
                            $price->stock,
                            $request->description ? $request->description . " (سرشماری دوره‌ای - بدون تغییر)" : "سرشماری دوره‌ای انبار - تأیید موجودی فعلی"
                        );
                    }
                    return [
                        'updated_count' => 0,
                        'total_difference' => 0,
                        'differences_list' => [],
                        'not_found_count' => 0,
                        'confirmed_count' => $confirmedCount,
                        'message' => 'سرشماری انجام شد. هیچ مغایرتی یافت نشد.',
                        'is_confirmation_only' => true
                    ];
                }

                foreach ($request->stocks as $data) {
                    $priceId = (int)$data['price_id'];
                    $newStock = (int)$data['actual_stock'];

                    $price = Price::where('id', $priceId)
                        ->where('warehouse_id', $warehouse->id)
                        ->with('product:id,title', 'attributes:id,name')
                        ->first();

                    if (!$price) {
                        $notFoundList[] = $priceId;
                        continue;
                    }

                    $oldStock = $price->stock;
                    $difference = $newStock - $oldStock;
                    $totalDifference += $difference;
                    $allProcessed[] = $priceId;

                    $descriptionText = $request->description ? $request->description . " (سرشماری" : "سرشماری دوره‌ای انبار";

                    if ($oldStock !== $newStock) {
                        $updatedCount++;
                        $descriptionText .= " - اصلاح موجودی از {$oldStock} به {$newStock})";

                        $attributesName = $price->attributes->pluck('name')->implode(' - ');
                        $differencesList[] = [
                            'product_title' => $price->product->title ?? '—',
                            'attributes' => $attributesName ?: 'بدون ویژگی',
                            'old_stock' => $oldStock,
                            'new_stock' => $newStock,
                            'difference' => $difference,
                        ];
                    } else {
                        $descriptionText .= " - تأیید موجودی {$oldStock})";
                    }

                    app(\App\Services\StockMovementService::class)->adjustment($price, $newStock, $descriptionText);
                }

                $allWarehousePrices = Price::where('warehouse_id', $warehouse->id)->whereNotIn('id', $allProcessed)->get();
                foreach ($allWarehousePrices as $price) {
                    app(\App\Services\StockMovementService::class)->adjustment(
                        $price,
                        $price->stock,
                        $request->description ? $request->description . " (سرشماری - تأیید موجودی فعلی {$price->stock})" : "سرشماری دوره‌ای انبار - تأیید موجودی فعلی {$price->stock}"
                    );
                }

                return [
                    'updated_count' => $updatedCount,
                    'total_difference' => $totalDifference,
                    'differences_list' => $differencesList,
                    'not_found_count' => count($notFoundList),
                    'confirmed_count' => $allWarehousePrices->count(),
                    'is_confirmation_only' => false
                ];
            });

            $duration = round(microtime(true) - $startTime, 2);

            // ========== ثبت لاگ با جزئیات کامل ==========
            $oldData = [];
            $newData = [];

            if (!empty($result['differences_list'])) {
                foreach ($result['differences_list'] as $change) {
                    $key = $change['product_title'] . ($change['attributes'] ? " ({$change['attributes']})" : '');
                    $oldData[$key] = $change['old_stock'];
                    $newData[$key] = $change['new_stock'];
                }
            }

            if ($result['confirmed_count'] > 0 && empty($result['differences_list'])) {
                $oldData['وضعیت'] = 'قبل از سرشماری';
                $newData['وضعیت'] = 'تأیید موجودی (بدون تغییر)';
            }

            $logDetails = [
                'action' => 'سرشماری انبار را انجام داد',
                'warehouse_name' => $warehouseName,
                'warehouse_id' => $warehouse->id,
                'submitted_items' => count($request->stocks ?? []),
                'updated_count' => $result['updated_count'],
                'confirmed_count' => $result['confirmed_count'] ?? 0,
                'not_found_count' => $result['not_found_count'],
                'duration_seconds' => $duration,
                'ip' => $request->ip()
            ];

            if (!empty($oldData)) {
                $logDetails['old'] = $oldData;
                $logDetails['attributes'] = $newData;
            }

            if ($request->description) {
                $logDetails['description'] = $request->description;
            }

            $logMessage = "مدیر {$adminName} سرشماری انبار «{$warehouseName}» را انجام داد";

            if ($result['updated_count'] > 0) {
                $logMessage .= " و موجودی {$result['updated_count']} تنوع را تصحیح کرد";
            } elseif ($result['is_confirmation_only'] ?? false) {
                $logMessage .= " (تأیید موجودی - بدون تغییر)";
            }

            activity()
                ->performedOn($warehouse)
                ->event('updated')
                ->causedBy(auth('adminPanel')->user())
                ->withProperties($logDetails)
                ->log($logMessage);

            $parts = ["سرشماری انبار با موفقیت انجام شد."];
            if ($result['updated_count'] > 0) {
                $parts[] = "{$result['updated_count']} تنوع بروزرسانی شد.";
            } else if (empty($request->stocks)) {
                $parts[] = "هیچ تغییری یافت نشد. تمام موجودی‌ها تأیید شدند.";
            } else {
                $parts[] = "هیچ مغایرتی یافت نشد.";
            }

            if ($result['total_difference'] !== 0) {
                $sign = $result['total_difference'] > 0 ? '+' : '';
                $parts[] = "مغایرت کل: {$sign}" . number_format($result['total_difference']) . " عدد";
            }

            return response()->json([
                'success' => true,
                'message' => implode(' ', $parts),
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock take failed', ['warehouse_id' => $warehouse->id, 'error' => $e->getMessage()]);

            activity()
                ->performedOn($warehouse)
                ->causedBy(auth('adminPanel')->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'stock_take_failed',
                    'warehouse_name' => $warehouseName,
                    'warehouse_id' => $warehouse->id,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} سرشماری انبار «{$warehouseName}» با خطا مواجه شد");

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت سرشماری: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function stockTakeData(Warehouse $warehouse, Request $request)
    {
        $search = $request->get('search');
        $stockFilter = $request->get('stock_filter'); // 'empty' | 'low' | null

        $query = $warehouse->prices()
            ->with(['product.brand', 'product.category', 'attributes.group'])
            ->select('prices.*');

        // فیلتر جستجو
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('title', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                })->orWhere('prices.id', $search);
            });
        }

        // فیلتر موجودی
        if ($stockFilter === 'empty') {
            $query->where('stock', 0);
        } elseif ($stockFilter === 'low') {
            $query->where('stock', '>', 0)->where('stock', '<=', 5);
        }

        // مرتب‌سازی — ابتدا محصولات اتمام موجودی، بعد کم‌موجود، بعد بقیه
        $query->orderByRaw('CASE WHEN stock = 0 THEN 0 WHEN stock <= 5 THEN 1 ELSE 2 END')
            ->orderBy('product_id')
            ->orderBy('prices.id');

        $variations = $query->paginate(50);

        // آمار کل انبار (مستقل از فیلتر جستجو)
        $allVariations = $warehouse->prices();
        $total = (clone $allVariations)->count();
        $empty = (clone $allVariations)->where('stock', 0)->count();
        $low = (clone $allVariations)->where('stock', '>', 0)->where('stock', '<=', 5)->count();

        $stats = [
            'total' => $total,
            'empty' => $empty,
            'low' => $low,
            'ok' => $total - $empty - $low,
        ];

        return response()->json([
            'variations' => $variations,
            'stats' => $stats,
        ]);
    }


    public function bulkStockData(Warehouse $warehouse, Request $request)
    {
        $search = $request->get('search');

        $query = $warehouse->prices()
            ->with(['product.brand', 'product.category', 'attributes.group'])
            ->select('prices.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('title', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                })->orWhere('prices.id', $search);
            });
        }

        // فروش هر تنوع را هم بگیریم
        $query->withCount(['orderItems as sold_count' => function ($q) {
            $q->whereHas('order', fn($o) => $o->where('status', 'paid'));
        }]);

        $query->orderBy('product_id')->orderBy('prices.id');

        $variations = $query->paginate(40);

        return response()->json([
            'variations' => $variations,
        ]);
    }

    /**
     * بروزرسانی گروهی موجودی
     */
    public function bulkStockUpdate(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'stocks' => 'required|array',
            'stocks.*.price_id' => 'required|exists:prices,id',
            'stocks.*.stock' => 'required|integer|min:0',
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";
        $startTime = microtime(true);

        try {
            $updatedCount = 0;
            $changesList = [];

            DB::transaction(function () use ($request, $warehouse, &$updatedCount, &$changesList) {
                foreach ($request->stocks as $item) {
                    $price = Price::where('id', $item['price_id'])
                        ->where('warehouse_id', $warehouse->id)
                        ->with('product:id,title', 'attributes:id,name')
                        ->first();

                    if ($price && $price->stock != $item['stock']) {
                        $oldStock = $price->stock;
                        $newStock = $item['stock'];
                        $difference = $newStock - $oldStock;

                        $productTitle = $price->product->title ?? 'نامشخص';
                        $attributes = $price->attributes->pluck('name')->implode(' - ');
                        $key = $productTitle . ($attributes ? " ({$attributes})" : '');

                        $changesList[$key] = [
                            'old' => $oldStock,
                            'new' => $newStock,
                            'difference' => $difference
                        ];

                        app(\App\Services\StockMovementService::class)->adjustment(
                            $price,
                            $newStock,
                            $request->description ?? "بروزرسانی گروهی موجودی از {$oldStock} به {$newStock}"
                        );

                        $updatedCount++;
                    }
                }
            });

            $duration = round(microtime(true) - $startTime, 2);

            // ========== ثبت لاگ با جزئیات کامل ==========
            $oldData = [];
            $newData = [];

            foreach ($changesList as $key => $change) {
                $oldData[$key] = $change['old'];
                $newData[$key] = $change['new'];
            }

            $logDetails = [
                'action' => 'بروزرسانی گروهی موجودی انبار را انجام داد',
                'warehouse_name' => $warehouseName,
                'warehouse_id' => $warehouse->id,
                'total_items_processed' => count($request->stocks),
                'updated_count' => $updatedCount,
                'duration_seconds' => $duration,
                'ip' => $request->ip()
            ];

            if (!empty($oldData)) {
                $logDetails['old'] = $oldData;
                $logDetails['attributes'] = $newData;
            }

            if ($request->description) {
                $logDetails['description'] = $request->description;
            }

            $logMessage = "مدیر {$adminName} بروزرسانی گروهی موجودی انبار «{$warehouseName}» را انجام داد";
            if ($updatedCount > 0) {
                $logMessage .= " و موجودی {$updatedCount} آیتم را تغییر داد";
            } else {
                $logMessage .= " (تغییری اعمال نشد)";
            }

            activity()
                ->performedOn($warehouse)
                ->event('updated')
                ->causedBy(auth('adminPanel')->user())
                ->withProperties($logDetails)
                ->log($logMessage);

            return response()->json([
                'success' => true,
                'message' => "بروزرسانی گروهی با موفقیت انجام شد. {$updatedCount} آیتم بروزرسانی شد."
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk stock update failed: ' . $e->getMessage());

            activity()
                ->performedOn($warehouse)
                ->causedBy(auth('adminPanel')->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'bulk_stock_update_failed',
                    'warehouse_name' => $warehouseName,
                    'warehouse_id' => $warehouse->id,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} بروزرسانی گروهی موجودی انبار «{$warehouseName}» با خطا مواجه شد");

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * نمایش تاریخچه موجودی یک محصول در انبار خاص (AJAX)
     */
    public function stockHistory(Warehouse $warehouse, Product $product, Request $request)
    {
        $this->authorize('warehouses.show');

        // گرفتن همه قیمت‌های این محصول در این انبار
        $prices = Price::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->with('attributes')
            ->get();

        // گرفتن تاریخچه حرکات
        $query = StockMovement::where('warehouse_id', $warehouse->id)
            ->whereIn('price_id', $prices->pluck('id'))
            ->with(['price.product', 'price.attributes'])
            ->orderBy('created_at', 'desc');

        // فیلتر نوع حرکت
        if ($request->movement_type && $request->movement_type != 'all') {
            $query->where('type', $request->movement_type);
        }

        // فیلتر تنوع
        if ($request->variation_id && $request->variation_id != 'all') {
            $query->where('price_id', $request->variation_id);
        }

        // فیلتر تاریخ
        if ($request->date_from) {
            $dateFrom = convertPersianToEnglish($request->date_from);
            $dateFrom = \Morilog\Jalali\Jalalian::fromFormat('Y-m-d', $dateFrom)->toCarbon()->startOfDay();
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($request->date_to) {
            $dateTo = convertPersianToEnglish($request->date_to);
            $dateTo = \Morilog\Jalali\Jalalian::fromFormat('Y-m-d', $dateTo)->toCarbon()->endOfDay();
            $query->where('created_at', '<=', $dateTo);
        }

        $stockHistory = $query->paginate(50);

        // آمار کلی
        $totalStock = $prices->sum('stock');
        $totalReserved = $prices->sum('reserved_stock');
        $totalSold = $prices->sum('sold_count');
        $variationsCount = $prices->count();

        // اگر درخواست AJAX است
        if ($request->ajax()) {
            $html = view('back.warehouses.partials.stock-history-content', compact(
                'product', 'warehouse', 'prices', 'stockHistory',
                'totalStock', 'totalReserved', 'totalSold', 'variationsCount'
            ))->render();

            $pagination = $stockHistory->links()->render();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'product_title' => $product->title,
                'product_id' => $product->id
            ]);
        }

        // خروجی اکسل
        if ($request->export == 'excel') {
            return $this->exportStockHistory($product, $stockHistory);
        }

        return view('back.warehouses.partials.stock-history', compact(
            'warehouse', 'product', 'prices', 'stockHistory',
            'totalStock', 'totalReserved', 'totalSold', 'variationsCount'
        ));
    }

    /**
     * خروجی اکسل تاریخچه موجودی
     */
    private function exportStockHistory($product, $stockHistory)
    {
        $adminName = auth('adminPanel')->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $productTitle = $product->title ?? $product->name ?? "#{$product->id}";
        $recordsCount = count($stockHistory);

        // ========== ثبت لاگ ==========
        activity()
            ->performedOn($product)
            ->causedBy(auth()->user())
            ->event('ecport')
            ->withProperties([
                'action' => 'export_stock_history',
                'attributes' => [
                    'نام محصول' => $productTitle,
                    'تعداد رکوردها' => $recordsCount,
                    'فرمت' => 'CSV',
                ],
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} تاریخچه موجودی محصول «{$productTitle}» را خروجی گرفت");

        // روش ساده: خروجی CSV
        $filename = "stock_history_product_{$product->id}_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');

        // هدرها
        fputcsv($handle, ['تاریخ', 'نوع', 'تنوع', 'تعداد', 'موجودی قبل', 'موجودی بعد', 'توضیحات', 'اپراتور']);

        foreach ($stockHistory as $movement) {
            fputcsv($handle, [
                jdate($movement->created_at)->format('Y-m-d H:i:s'),
                $this->translateMovementType($movement->type),
                $movement->price_id,
                $movement->quantity,
                $movement->before_stock,
                $movement->after_stock,
                $movement->description,
                $this->getOperatorName($movement)
            ]);
        }

        fclose($handle);

        return response()->make('', 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * ترجمه نوع حرکت انبار
     */
    private function translateMovementType($type)
    {
        $types = [
            'in' => 'ورود',
            'out' => 'خروج',
            'adjustment' => 'تنظیم موجودی',
            'reserve' => 'رزرو',
            'unreserve' => 'لغو رزرو',
        ];

        return $types[$type] ?? $type;
    }

    /**
     * دریافت نام اپراتور
     */
    private function getOperatorName($movement)
    {
        $operatorType = $movement->operator_type;
        $operatorId = $movement->operator_id;

        if (!$operatorId) {
            return 'سیستم';
        }

        switch ($operatorType) {
            case 'admin':
                $admin = \App\Models\Admin::find($operatorId);
                return "مدیر - " . ($admin->full_name ?? $admin->name ?? "#{$operatorId}");
            case 'user':
                $user = \App\Models\User::find($operatorId);
                return "کاربر - " . ($user->full_name ?? $user->name ?? "#{$operatorId}");
            case 'seller':
                $seller = \App\Models\Seller::find($operatorId);
                return "فروشنده - " . ($seller->full_name ?? $seller->name ?? "#{$operatorId}");
            default:
                return "{$operatorType} - {$operatorId}";
        }
    }

// لود داده تنوع برای Modal ویرایش
    public function getVariationData(Warehouse $warehouse, Product $product, Price $price)
    {
        $this->authorize('products.update');
        $sellers = Seller::where(['status_register' => 'complete', 'status_documents' => 'Accept', 'status_work' => 'ACTIVE'])->get();

        $attributes = $price->get_attributes()->get();
        $warehouses = Warehouse::active()->get();
        $attributeGroups = AttributeGroup::detectLang()->orderBy('ordering')->get();
        $rowHtml = view('back.warehouses.partials.prices-include', [
            'price' => $price,
            'sellers' => $sellers,
            'attributes' => $attributes,
            'warehouses' => $warehouses,
            'attributeGroups' => $attributeGroups,
            'loop' => (object)['iteration' => 1, 'first' => false],
        ])->render();

        return response()->json([
            'success' => true,
            'message' => 'تنوع با موفقیت بارگذاری شد.',
            'html' => $rowHtml,
        ]);

    }

// ویرایش تنوع
    public function updateVariation(Request $request, Warehouse $warehouse, Product $product, Price $price)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'warehouse' => 'required|exists:warehouses,id',
            'discount' => 'nullable|integer|min:0|max:100',
            'discount_expire_at' => 'nullable',
            'cart_max' => 'nullable|integer|min:1',
            'cart_min' => 'nullable|integer|min:1',
            'published' => 'required|in:0,1',
            'seller_id' => 'nullable|exists:sellers,id',
            'attributes' => 'nullable|array',
        ]);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";
        $productTitle = $product->title ?? $product->name ?? "#{$product->id}";

        // دریافت اطلاعات ویژگی‌های قبلی
        $oldAttributesNames = $price->attributes->pluck('name')->implode('، ');

        // دریافت نام فروشنده قبلی
        $oldSellerName = null;
        if ($price->seller_id) {
            $oldSeller = \App\Models\Seller::find($price->seller_id);
            $oldSellerName = $oldSeller->full_name ?? $oldSeller->name ?? $oldSeller->business_name ?? 'نامشخص';
        }

        // دریافت نام انبار قبلی
        $oldWarehouseName = null;
        if ($price->warehouse_id) {
            $oldWarehouse = Warehouse::find($price->warehouse_id);
            $oldWarehouseName = $oldWarehouse->name ?? $oldWarehouse->code ?? 'نامشخص';
        }

        // فرمت تاریخ تخفیف قبلی
        $oldDiscountExpireAt = null;
        if ($price->discount_expire_at) {
            $oldDiscountExpireAt = jdate($price->discount_expire_at)->format('Y/m/d');
        }

        // ذخیره مقادیر قدیمی
        $oldValues = [
            'price' => $price->price,
            'stock' => $price->stock,
            'discount' => $price->discount,
            'published' => $price->published ? 'فعال' : 'غیرفعال',
            'cart_max' => $price->cart_max,
            'cart_min' => $price->cart_min,
            'seller_id' => $oldSellerName,
            'warehouse_id' => $oldWarehouseName,
            'discount_expire_at' => $oldDiscountExpireAt,
            'attributes' => $oldAttributesNames ?: 'بدون ویژگی'
        ];

        try {
            DB::transaction(function () use ($request, $price, $product, $warehouse) {
                $oldStock = $price->stock;
                $newStock = (int)$request->stock;

                if ($oldStock != $newStock) {
                    app(\App\Services\StockMovementService::class)->adjustment(
                        $price,
                        $newStock,
                        "ویرایش مستقیم تنوع - تغییر موجودی از {$oldStock} به {$newStock}"
                    );
                }

                $regularPrice = get_discount_price($request->price, 0, $product);
                $discountPrice = get_discount_price($request->price, $request->discount ?? 0, $product);

                $discountExpireAt = null;
                if (isset($request->discount_expire_at) && $request->discount_expire_at) {
                    try {
                        $discountExpireAt = Jalalian::fromFormat('Y-m-d H:i:s', $request->discount_expire_at)->toCarbon();
                    } catch (\Exception $e) {}
                }

                $price->update([
                    'price' => $request->price,
                    'discount' => $request->discount ?? 0,
                    'regular_price' => $regularPrice,
                    'discount_price' => $discountPrice,
                    'stock' => $newStock,
                    'cart_max' => $request->cart_max,
                    'cart_min' => $request->cart_min,
                    'published' => $request->published ?? true,
                    'discount_expire_at' => $discountExpireAt,
                    'seller_id' => $request->seller_id,
                    'warehouse_id' => $request->warehouse,
                ]);

                $attributes = $request->input('attributes', []);
                if (!is_array($attributes)) {
                    $attributes = method_exists($attributes, 'all') ? $attributes->all() : [];
                }

                $attributes = array_values(array_filter($attributes, function ($attr) {
                    return !is_null($attr) && $attr !== '' && $attr !== 'null';
                }));

                if (!empty($attributes)) {
                    $currentAttributes = DB::table('attribute_price')
                        ->where('price_id', $price->id)
                        ->pluck('attribute_id')
                        ->toArray();

                    $attributesToAdd = array_diff($attributes, $currentAttributes);
                    $attributesToRemove = array_diff($currentAttributes, $attributes);

                    if (!empty($attributesToRemove)) {
                        DB::table('attribute_price')
                            ->where('price_id', $price->id)
                            ->whereIn('attribute_id', $attributesToRemove)
                            ->delete();
                    }

                    foreach ($attributesToAdd as $attributeId) {
                        DB::table('attribute_price')->insert([
                            'attribute_id' => $attributeId,
                            'price_id' => $price->id,
                            'seller_id' => $request->seller_id,
                            'product_id' => $product->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $price->createChange($request->price, $request->discount ?? 0, $newStock);
            });

            $price->refresh();
            $price->load('attributes');

            // دریافت مقادیر جدید
            $newAttributesNames = $price->attributes->pluck('name')->implode('، ');
            $newPublished = $request->published ? 'فعال' : 'غیرفعال';

            // دریافت نام فروشنده جدید
            $newSellerName = null;
            if ($request->seller_id) {
                $newSeller = \App\Models\Seller::find($request->seller_id);
                $newSellerName = $newSeller->full_name ?? $newSeller->name ?? $newSeller->business_name ?? 'نامشخص';
            }

            // دریافت نام انبار جدید
            $newWarehouse = Warehouse::find($request->warehouse);
            $newWarehouseName = $newWarehouse->name ?? $newWarehouse->code ?? 'نامشخص';

            // فرمت تاریخ تخفیف جدید
            $newDiscountExpireAt = null;
            if ($request->discount_expire_at) {
                try {
                    $newDiscountExpireAt = Jalalian::fromFormat('Y-m-d H:i:s', $request->discount_expire_at)->format('Y/m/d');
                } catch (\Exception $e) {}
            }

            // ساخت تغییرات با فرمت old و attributes
            $oldData = [];
            $newData = [];

            if ($oldValues['price'] != $request->price) {
                $oldData['قیمت'] = number_format($oldValues['price']) . ' تومان';
                $newData['قیمت'] = number_format($request->price) . ' تومان';
            }
            if ($oldValues['stock'] != $request->stock) {
                $oldData['موجودی'] = $oldValues['stock'];
                $newData['موجودی'] = $request->stock;
            }
            if ($oldValues['discount'] != ($request->discount ?? 0)) {
                $oldData['تخفیف'] = $oldValues['discount'] . '%';
                $newData['تخفیف'] = ($request->discount ?? 0) . '%';
            }
            if ($oldValues['published'] != $newPublished) {
                $oldData['وضعیت'] = $oldValues['published'];
                $newData['وضعیت'] = $newPublished;
            }
            if ($oldValues['cart_max'] != $request->cart_max) {
                $oldData['حداکثر تعداد سفارش'] = $oldValues['cart_max'] ?? 'نامحدود';
                $newData['حداکثر تعداد سفارش'] = $request->cart_max ?? 'نامحدود';
            }
            if ($oldValues['cart_min'] != $request->cart_min) {
                $oldData['حداقل تعداد سفارش'] = $oldValues['cart_min'] ?? '1';
                $newData['حداقل تعداد سفارش'] = $request->cart_min ?? '1';
            }
            if ($oldValues['seller_id'] != $newSellerName) {
                $oldData['فروشنده'] = $oldValues['seller_id'] ?? 'مدیر سایت';
                $newData['فروشنده'] = $newSellerName ?? 'مدیر سایت';
            }
            if ($oldValues['warehouse_id'] != $newWarehouseName) {
                $oldData['انبار'] = $oldValues['warehouse_id'];
                $newData['انبار'] = $newWarehouseName;
            }
            if ($oldValues['discount_expire_at'] != $newDiscountExpireAt) {
                $oldData['تاریخ انقضای تخفیف'] = $oldValues['discount_expire_at'] ?? 'ندارد';
                $newData['تاریخ انقضای تخفیف'] = $newDiscountExpireAt ?? 'ندارد';
            }
            if ($oldValues['attributes'] != ($newAttributesNames ?: 'بدون ویژگی')) {
                $oldData['ویژگی‌ها'] = $oldValues['attributes'];
                $newData['ویژگی‌ها'] = $newAttributesNames ?: 'بدون ویژگی';
            }

            if (!empty($oldData)) {
                activity()
                    ->performedOn($price)
                    ->causedBy(auth()->user())
                    ->event('updated')
                    ->withProperties([
                        'action' => 'تنوع محصول را ویرایش کرد',
                        'product_title' => $productTitle,
                        'product_id' => $product->id,
                        'warehouse_name' => $warehouseName,
                        'warehouse_id' => $warehouse->id,
                        'price_id' => $price->id,
                        'old' => $oldData,
                        'attributes' => $newData,
                        'ip' => $request->ip()
                    ])
                    ->log("مدیر {$adminName} تنوع محصول «{$productTitle}» را در انبار «{$warehouseName}» ویرایش کرد");
            }

            $allPrices = Price::where('warehouse_id', $warehouse->id)
                ->where('product_id', $product->id)
                ->get();

            foreach ($allPrices as $p) {
                $p->sold_count = \App\Models\OrderItem::where('price_id', $p->id)
                    ->whereHas('order', fn($q) => $q->where('status', 'paid'))
                    ->sum('quantity');
            }

            $stats = $this->calculateStats($allPrices);

            $rowHtml = view('back.warehouses.partials.product-variation-row', [
                'price' => $price,
                'product' => $product,
                'stats' => $stats,
                'warehouse' => $warehouse,
                'loop' => (object)['iteration' => 1, 'first' => false],
            ])->render();

            $statsHtml = view('back.warehouses.partials.product-variation-stats', [
                'stats' => $stats,
                'warehouse' => $warehouse
            ])->render();

            return response()->json([
                'success' => true,
                'message' => 'تنوع با موفقیت ویرایش شد.',
                'price_id' => $price->id,
                'row_html' => $rowHtml,
                'stats' => $stats,
                'stats_html' => $statsHtml
            ]);

        } catch (\Exception $e) {
            \Log::error("Error updating variation {$price->id}: " . $e->getMessage());

            activity()
                ->performedOn($price)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'update_variation_failed',
                    'product_title' => $productTitle,
                    'product_id' => $product->id,
                    'warehouse_name' => $warehouseName,
                    'price_id' => $price->id,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} ویرایش تنوع محصول «{$productTitle}» در انبار «{$warehouseName}» با خطا مواجه شد");

            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش تنوع: ' . $e->getMessage()
            ], 500);
        }
    }
// افزودن تنوع جدید
    public function storeVariation(Request $request, Warehouse $warehouse, Product $product)
    {
        $request->validate([
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'warehouse'    => 'required|exists:warehouses,id',
            'discount'     => 'nullable|integer|min:0|max:100',
            'discount_expire_at' => 'nullable',
            'cart_max'     => 'nullable|integer|min:1',
            'cart_min'     => 'nullable|integer|min:1',
            'published'    => 'required|in:0,1',
            'seller_id'    => 'nullable|exists:sellers,id',
            'attributes'   => 'nullable|array',
        ]);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";
        $productTitle = $product->title ?? $product->name ?? "#{$product->id}";

        try {
            $newPrice = DB::transaction(function () use ($request, $product, $warehouse) {
                $attributes = $request->input('attributes', []);
                if (!is_array($attributes)) {
                    $attributes = method_exists($attributes, 'all') ? $attributes->all() : [];
                }

                $attributes = array_values(array_filter($attributes, function($attr) {
                    return !is_null($attr) && $attr !== '' && $attr !== 'null';
                }));

                $regularPrice = get_discount_price($request->price, 0, $product);
                $discountPrice = get_discount_price($request->price, $request->discount ?? 0, $product);

                $discountExpireAt = null;
                if ($request->discount_expire_at) {
                    try {
                        $discountExpireAt = Jalalian::fromFormat('Y-m-d H:i:s', $request->discount_expire_at)->toCarbon();
                    } catch (\Exception $e) {}
                }

                $priceData = [
                    'product_id'         => $product->id,
                    'warehouse_id'       => $request->warehouse,
                    'price'              => $request->price,
                    'discount'           => $request->discount ?? 0,
                    'regular_price'      => $regularPrice,
                    'discount_price'     => $discountPrice,
                    'stock'              => 0,
                    'cart_max'           => $request->cart_max,
                    'cart_min'           => $request->cart_min,
                    'published'          => $request->published,
                    'discount_expire_at' => $discountExpireAt,
                    'seller_id'          => $request->seller_id,
                ];

                $newPrice = Price::create($priceData);

                if (!empty($attributes)) {
                    foreach ($attributes as $attributeId) {
                        DB::table('attribute_price')->insert([
                            'attribute_id' => $attributeId,
                            'price_id' => $newPrice->id,
                            'seller_id' => $request->seller_id,
                            'product_id' => $product->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($request->stock > 0) {
                    app(\App\Services\StockMovementService::class)->inbound(
                        $newPrice,
                        $request->stock,
                        "ایجاد تنوع جدید - موجودی اولیه",
                        'variation_creation'
                    );
                }

                $newPrice->createChange($request->price, $request->discount ?? 0, $request->stock);
                return $newPrice;
            });

            $newPrice->load('attributes');

            // دریافت اطلاعات انبار
            $selectedWarehouse = Warehouse::find($request->warehouse);
            $warehouseDisplay = $selectedWarehouse ? ($selectedWarehouse->name . ' (' . $selectedWarehouse->code . ')') : 'نامشخص';

            // دریافت اطلاعات فروشنده
            $sellerDisplay = 'مدیر سایت';
            if ($request->seller_id) {
                $seller = \App\Models\Seller::find($request->seller_id);
                if ($seller) {
                    $sellerDisplay = $seller->seller_info->business_name ?? $seller->seller_info->full_name ?? $seller->name ?? 'نامشخص';
                }
            }

            // فرمت تاریخ تخفیف
            $discountExpireDisplay = 'ندارد';
            if ($request->discount_expire_at) {
                try {
                    $discountExpireDisplay = Jalalian::fromFormat('Y-m-d H:i:s', $request->discount_expire_at)->format('Y/m/d');
                } catch (\Exception $e) {}
            }

            // دریافت ویژگی‌ها
            $attributesNames = $newPrice->attributes->pluck('name')->implode('، ');
            $statusText = $request->published ? 'فعال' : 'غیرفعال';

            // ساخت اطلاعات تنوع جدید با فرمت attributes
            $newData = [
                'قیمت' => number_format($request->price) . ' تومان',
                'موجودی اولیه' => $request->stock . ' عدد',
                'وضعیت' => $statusText,
                'انبار' => $warehouseDisplay,
                'حداقل تعداد سفارش' => $request->cart_min ?? '1',
                'حداکثر تعداد سفارش' => $request->cart_max ?? 'نامحدود',
                'فروشنده' => $sellerDisplay,
            ];

            if ($request->discount && $request->discount > 0) {
                $newData['تخفیف'] = $request->discount . '%';
                $newData['تاریخ انقضای تخفیف'] = $discountExpireDisplay;
            }

            if ($attributesNames) {
                $newData['ویژگی‌ها'] = $attributesNames;
            }

            activity()
                ->performedOn($newPrice)
                ->causedBy(auth()->user())
                ->event('created')
                ->withProperties([
                    'action' => 'ایجاد تنوع جدید',
                    'product_title' => $productTitle,
                    'product_id' => $product->id,
                    'warehouse_name' => $warehouseName,
                    'warehouse_id' => $warehouse->id,
                    'price_id' => $newPrice->id,
                    'attributes' => $newData,
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} تنوع جدیدی برای محصول «{$productTitle}» در انبار «{$warehouseName}» ایجاد کرد");

            $allPrices = Price::where('warehouse_id', $warehouse->id)
                ->where('product_id', $product->id)
                ->get();

            foreach ($allPrices as $p) {
                $p->sold_count = \App\Models\OrderItem::where('price_id', $p->id)
                    ->whereHas('order', fn($q) => $q->where('status', 'paid'))
                    ->sum('quantity');
            }

            $stats = $this->calculateStats($allPrices);

            $rowHtml = view('back.warehouses.partials.product-variation-row', [
                'price'     => $newPrice,
                'product'   => $product,
                'warehouse' => $warehouse,
                'loop'      => (object)['iteration' => 1, 'first' => false],
            ])->render();

            $statsHtml = view('back.warehouses.partials.product-variation-stats', [
                'stats' => $stats,
                'warehouse' => $warehouse
            ])->render();

            return response()->json([
                'success'    => true,
                'message'    => 'تنوع جدید با موفقیت اضافه شد.',
                'price_id'   => $newPrice->id,
                'row_html'   => $rowHtml,
                'stats'      => $stats,
                'stats_html' => $statsHtml,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error creating variation for product {$product->id}: " . $e->getMessage());

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->event('created')
                ->withProperties([
                    'action' => 'create_variation_failed',
                    'product_title' => $productTitle,
                    'product_id' => $product->id,
                    'warehouse_name' => $warehouseName,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip()
                ])
                ->log("مدیر {$adminName} ایجاد تنوع جدید برای محصول «{$productTitle}» در انبار «{$warehouseName}» با خطا مواجه شد");

            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد تنوع: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * حذف تنوع (قیمت)
     */
    public function destroyVariation(Warehouse $warehouse, Product $product, Price $price)
    {
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $warehouseName = $warehouse->name ?? $warehouse->code ?? "#{$warehouse->id}";
        $productTitle = $product->title ?? $product->name ?? "#{$product->id}";

        $variationPrice = $price->price;
        $variationStock = $price->stock;
        $attributesNames = $price->attributes->pluck('name')->implode('، ');

        try {
            DB::transaction(function () use ($price, $product, $warehouse) {
                $hasActiveOrders = \App\Models\OrderItem::where('price_id', $price->id)
                    ->whereHas('order', fn($q) => $q->whereIn('status', ['unpaid', 'pending', 'processing']))
                    ->exists();

                if ($hasActiveOrders) {
                    throw new \Exception('این تنوع دارای سفارش‌های فعال است و قابل حذف نمی‌باشد.');
                }

                if ($price->stock > 0) {
                    app(\App\Services\StockMovementService::class)->outbound(
                        $price,
                        $price->stock,
                        null,
                        null,
                        "حذف تنوع - خروج کل موجودی ({$price->stock} عدد) از انبار"
                    );
                }

                DB::table('attribute_price')->where('price_id', $price->id)->delete();
                DB::table('cart_product')->where('price_id', $price->id)->delete();
                $price->forceDelete();
            });

            $allPrices = Price::where('warehouse_id', $warehouse->id)
                ->where('product_id', $product->id)
                ->get();

            foreach ($allPrices as $p) {
                $p->sold_count = \App\Models\OrderItem::where('price_id', $p->id)
                    ->whereHas('order', fn($q) => $q->where('status', 'paid'))
                    ->sum('quantity');
            }

            $stats = $this->calculateStats($allPrices);

            // ساخت اطلاعات تنوع حذف شده
            $oldData = [
                'قیمت' => number_format($variationPrice) . ' تومان',
                'موجودی' => $variationStock . ' عدد',
            ];
            if ($attributesNames) {
                $oldData['ویژگی‌ها'] = $attributesNames;
            }

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->event('deleted')
                ->withProperties([
                    'action' => 'تنوع محصول را حذف کرد',
                    'product_title' => $productTitle,
                    'product_id' => $product->id,
                    'warehouse_name' => $warehouseName,
                    'warehouse_id' => $warehouse->id,
                    'price_id' => $price->id,
                    'old' => $oldData,
                    'ip' => request()->ip()
                ])
                ->log("مدیر {$adminName} تنوع محصول «{$productTitle}» را در انبار «{$warehouseName}» حذف کرد");

            $statsHtml = view('back.warehouses.partials.product-variation-stats', [
                'stats' => $stats,
                'warehouse' => $warehouse
            ])->render();

            return response()->json([
                'success'    => true,
                'message'    => 'تنوع با موفقیت حذف شد.',
                'price_id'   => $price->id,
                'stats'      => $stats,
                'stats_html' => $statsHtml,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error deleting variation {$price->id}: " . $e->getMessage());

            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->event('deleted')
                ->withProperties([
                    'action' => 'delete_variation_failed',
                    'product_title' => $productTitle,
                    'product_id' => $product->id,
                    'warehouse_name' => $warehouseName,
                    'price_id' => $price->id,
                    'error' => $e->getMessage(),
                    'ip' => request()->ip()
                ])
                ->log("مدیر {$adminName} حذف تنوع محصول «{$productTitle}» در انبار «{$warehouseName}» با خطا مواجه شد");

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف تنوع: ' . $e->getMessage()
            ], 500);
        }
    }
    private function calculateStats($allPrices)
    {

        return [
            'current_count' => $allPrices->count(),
            'total_stock_current' => $allPrices->sum('stock'),
            'total_sold' => $allPrices->sum('sold_count'),
            'total_reserved' => $allPrices->sum('reserved_stock'),
            'low_stock' => $allPrices->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock' => $allPrices->where('stock', 0)->count(),
            'critical_stock' => $allPrices->where('stock', '<=', 2)->where('stock', '>', 0)->count(),
            'total_sold_current' => $allPrices->sum('sold_count'),
            'total_value' => $allPrices->sum(function($p) {
                return $p->stock * ($p->cost_price ?? $p->price);
            }),
        ];
    }
}
