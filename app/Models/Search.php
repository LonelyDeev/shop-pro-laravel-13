<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use HasFactory;

    protected $table = 'searches';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'keyword',
        'search_type',
        'filters',
        'products_count',
        'categories_count',
        'brands_count',
        'posts_count',
        'result_ids',
        'has_brand',
        'is_ajax',
        'searched_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'result_ids' => 'array',
        'searched_at' => 'datetime',
        'has_brand' => 'boolean',
        'is_ajax' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeProductSearches($query)
    {
        return $query->where('search_type', 'products');
    }

    public function scopePostSearches($query)
    {
        return $query->where('search_type', 'posts');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('searched_at', today());
    }

    public function scopeKeyword($query, $keyword)
    {
        return $query->where('keyword', 'LIKE', "%{$keyword}%");
    }

    // آمارگیری
    public static function popularProductsSearches($limit = 10)
    {
        return self::where('search_type', 'products')
            ->select('keyword', \DB::raw('count(*) as total'))
            ->groupBy('keyword')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function popularPostsSearches($limit = 10)
    {
        return self::where('search_type', 'posts')
            ->select('keyword', \DB::raw('count(*) as total'))
            ->groupBy('keyword')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function searchesWithoutResult()
    {
        return self::where(function($query) {
            $query->where('products_count', 0)
                ->where('categories_count', 0)
                ->where('brands_count', 0)
                ->where('posts_count', 0);
        })->get();
    }
}
