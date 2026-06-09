<?php

namespace App\Traits;

use App\Models\Search;
use Illuminate\Http\Request;

trait LogsSearch
{
    protected function logProductSearch(Request $request, $products, $categories, $brand, $brand_categories)
    {
       $keyword= $request->q ? $request->q : $request->s;
       $keyword = $keyword ? $keyword : $categories->title;
        return Search::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'keyword' => $keyword,
            'search_type' => 'products',
            'products_count' => $products->count(),
            'categories_count' => $categories->count(),
            'brands_count' => $brand ? 1 : 0,
            'has_brand' => $brand ? true : false,
            'is_ajax' => $request->ajax(),
            'searched_at' => now(),
            'filters' => null,
            'result_ids' => [
                'products' => $products->pluck('id')->toArray(),
                'categories' => $categories->pluck('id')->toArray(),
                'brand' => $brand ? $brand->id : null,
                'brand_categories' => $brand_categories ? $brand_categories->pluck('id')->toArray() : [],
            ],
        ]);
    }

    protected function logPostSearch(Request $request, $posts)
    {
        return Search::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'keyword' => $request->key,
            'search_type' => 'posts',
            'posts_count' => $posts->total(), // تعداد کل پست‌ها
            'is_ajax' => false,
            'searched_at' => now(),
            'filters' => null,
            'result_ids' => [
                'posts' => $posts->pluck('id')->toArray(),
            ],
        ]);
    }
}
