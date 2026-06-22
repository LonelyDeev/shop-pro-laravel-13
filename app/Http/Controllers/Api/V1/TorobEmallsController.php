<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TorobEmallsController extends Controller
{
    public function torob(Request $request)
    {
        $per_page = $request->per_page ?: 100;
        $page = $request->page ?: 1;

        if ($per_page > 200) {
            $per_page = 200;
        }

        // ایجاد کلید کش بر اساس پارامترها
        $cacheKey = 'torob_products_' . md5(json_encode($request->all()));

        // اگر کش وجود داشت، برگردان
        if (config('app.env') !== 'local' && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $products = Product::with(['category', 'prices' => function($q) {
            $q->with('attributes.group')
                ->where('stock', '>', 0)
                ->orderByRaw('COALESCE(discount_price, price) ASC');
        }])
            ->whereHas('prices', function($q) {
                $q->where('stock', '>', 0);
            })
            ->where('published', true)
            ->when($request->category_id, function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            })
            ->when($request->brand_id, function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            })
            ->when($request->search, function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%");
            })
            ->latest()
            ->paginate($per_page, ['*'], 'page', $page);

        $data = $products->map(function($product) {
            $cheapestPrice = $product->prices->first();

            if (!$cheapestPrice) {
                return null;
            }

            $finalPrice = $cheapestPrice->discount_price && $cheapestPrice->discount_price < $cheapestPrice->price
                ? (int) $cheapestPrice->discount_price
                : (int) $cheapestPrice->price;

            $oldPrice = $cheapestPrice->discount_price && $cheapestPrice->discount_price < $cheapestPrice->price
                ? (int) ($cheapestPrice->regular_price ?? $cheapestPrice->price)
                : null;

            $color = '';
            $guarantee = 'اصالت و سلامت فیزیکی کالا';

            foreach ($cheapestPrice->attributes as $attribute) {
                if ($attribute->group && $attribute->group->type == 'color') {
                    $color = $attribute->name;
                }
                if ($attribute->group && in_array($attribute->group->name, ['گارانتی', 'warranty', 'guarantee', 'ضمانت'])) {
                    $guarantee = $attribute->name;
                }
            }

            return [
                'product_id' => $product->product_id ?? "p-{$product->id}",
                'title' => $product->title,
                'image' => $product->image ? asset($product->image) : null,
                'page_url' => route('front.products.show', $product->slug ?? $product->id),
                'price' => $finalPrice,
                'old_price' => $oldPrice,
                'availability' => $product->prices->where('stock', '>', 0)->count() > 0 ? 'instock' : 'outofstock',
                'color' => $color,
                'guarantee' => $guarantee,
                'original' => true,
                'used' => false,
            ];
        })->filter()->values();

        $path = route('api.torob.products');
        $lastPage = $products->lastPage();
        $currentPage = $products->currentPage();

        $links = [];
        $links[] = [
            'url' => $currentPage > 1 ? $path . '?page=' . ($currentPage - 1) . '&per_page=' . $per_page : null,
            'label' => '&laquo; قبلی',
            'page' => $currentPage > 1 ? $currentPage - 1 : null,
            'active' => false,
        ];

        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => $path . '?page=' . $i . '&per_page=' . $per_page,
                'label' => (string) $i,
                'page' => $i,
                'active' => $i == $currentPage,
            ];
        }

        $links[] = [
            'url' => $currentPage < $lastPage ? $path . '?page=' . ($currentPage + 1) . '&per_page=' . $per_page : null,
            'label' => 'بعدی &raquo;',
            'page' => $currentPage < $lastPage ? $currentPage + 1 : null,
            'active' => false,
        ];

        $response = response()->json([
            'current_page' => $currentPage,
            'data' => $data,
            'first_page_url' => $path . '?page=1&per_page=' . $per_page,
            'from' => $products->firstItem() ?? 0,
            'last_page' => $lastPage,
            'last_page_url' => $path . '?page=' . $lastPage . '&per_page=' . $per_page,
            'links' => $links,
            'next_page_url' => $currentPage < $lastPage ? $path . '?page=' . ($currentPage + 1) . '&per_page=' . $per_page : null,
            'path' => $path,
            'per_page' => (int) $per_page,
            'prev_page_url' => $currentPage > 1 ? $path . '?page=' . ($currentPage - 1) . '&per_page=' . $per_page : null,
            'to' => $products->lastItem() ?? 0,
            'total' => $products->total(),
        ]);

        // ذخیره در کش برای 5 دقیقه
        if (config('app.env') !== 'local') {
            Cache::put($cacheKey, $response, 300);
        }

        return $response;
    }

    public function emalls(Request $request)
    {
        $per_page = $request->item_per_page ?: 20;
        $page = $request->page ?: 1;

        if ($per_page > 100) {
            $per_page = 100;
        }

        // ایجاد کلید کش بر اساس پارامترها
        $cacheKey = 'emalls_products_' . md5(json_encode([
                'per_page' => $per_page,
                'page' => $page,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'search' => $request->search,
            ]));

        // بررسی کش
        if (Cache::has($cacheKey) && config('app.env') !== 'local') {
            return Cache::get($cacheKey);
        }

        $products = Product::with([
            'category:id,title',
            'prices' => function($q) {
                $q->select('prices.id', 'prices.product_id', 'prices.price', 'prices.discount', 'prices.discount_price', 'prices.regular_price', 'prices.stock')
                    ->with(['attributes' => function($q) {
                        $q->select('attributes.id', 'attributes.name', 'attributes.attribute_group_id')
                            ->with('group:id,name,type');
                    }])
                    ->where('prices.stock', '>', 0)
                    ->orderByRaw('COALESCE(prices.discount_price, prices.price) ASC');
            }
        ])
            ->select('products.id', 'products.title', 'products.product_id', 'products.category_id', 'products.image', 'products.slug', 'products.published')
            ->whereHas('prices', function($q) {
                $q->where('prices.stock', '>', 0);
            })
            ->where('products.published', true)
            ->when($request->category_id, function($q) use ($request) {
                $q->where('products.category_id', $request->category_id);
            })
            ->when($request->brand_id, function($q) use ($request) {
                $q->where('products.brand_id', $request->brand_id);
            })
            ->when($request->search, function($q) use ($request) {
                $q->where('products.title', 'LIKE', "%{$request->search}%");
            })
            ->latest('products.created_at')
            ->paginate($per_page, ['*'], 'page', $page);

        // پیش‌بارگذاری گروه‌های ویژگی
        $colorGroupId = Cache::remember('color_group_id', 3600, function() {
            return AttributeGroup::where('type', 'color')->value('id');
        });

        $guaranteeGroupIds = Cache::remember('guarantee_group_ids', 3600, function() {
            return AttributeGroup::whereIn('name', ['گارانتی', 'warranty', 'guarantee', 'ضمانت'])->pluck('id')->toArray();
        });

        $formattedProducts = $products->map(function($product) use ($colorGroupId, $guaranteeGroupIds) {
            $cheapestPrice = $product->prices->first();

            if (!$cheapestPrice) {
                return null;
            }

            // قیمت نهایی
            $finalPrice = $cheapestPrice->discount_price && $cheapestPrice->discount_price < $cheapestPrice->price
                ? (int) $cheapestPrice->discount_price
                : (int) $cheapestPrice->price;

            // قیمت قدیمی (برای نمایش تخفیف)
            $oldPrice = $cheapestPrice->discount_price && $cheapestPrice->discount_price < $cheapestPrice->price
                ? (int) ($cheapestPrice->regular_price ?? $cheapestPrice->price)
                : null;

            $color = '';
            $guarantee = 'اصالت و سلامت فیزیکی کالا';

            foreach ($cheapestPrice->attributes as $attribute) {
                if ($attribute->group) {
                    if ($attribute->group->type == 'color' || $attribute->attribute_group_id == $colorGroupId) {
                        $color = $attribute->name;
                    }
                    if (in_array($attribute->group->name, ['گارانتی', 'warranty', 'guarantee', 'ضمانت']) ||
                        in_array($attribute->attribute_group_id, $guaranteeGroupIds)) {
                        $guarantee = $attribute->name;
                    }
                }
            }

            return [
                'title' => $product->title,
                'id' => $product->product_id ?? "AK-{$product->id}",
                'price' => $finalPrice,
                'old_price' => $oldPrice,
                'category' => $product->category->title ?? 'بدون دسته‌بندی',
                'image' => $product->image ? asset($product->image) : null,
                'color' => $color,
                'guarantee' => $guarantee,
                'is_available' => $product->prices->where('stock', '>', 0)->count() > 0,
                'url' => $product->slug
                    ? route('front.products.show', $product->slug)
                    : route('front.products.show', $product->id),
            ];
        })->filter()->values();

        $response = response()->json([
            'success' => true,
            'products' => $formattedProducts,
            'total_items' => $products->total(),
            'pages_count' => $products->lastPage(),
            'item_per_page' => (int) $per_page,
            'page_num' => $products->currentPage(),
        ]);

        // ذخیره در کش برای 5 دقیقه
        if (config('app.env') !== 'local') {
            Cache::put($cacheKey, $response, 300);
        }

        return $response;
    }
}
