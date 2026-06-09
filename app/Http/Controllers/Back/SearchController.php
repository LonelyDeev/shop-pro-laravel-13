<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Search;
use App\Traits\LogsSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:searches');
    }
    public function index(Request $request)
    {
        // گروه‌بندی جستجوهای تکراری
        $searches = Search::select(
            'keyword',
            'search_type',
            DB::raw('COUNT(*) as search_count'),
            DB::raw('MAX(searched_at) as last_searched'),
            DB::raw('MIN(searched_at) as first_searched'),
            DB::raw('AVG(CASE
                    WHEN search_type = "products" THEN products_count
                    WHEN search_type = "posts" THEN posts_count
                    ELSE 0 END) as avg_results')
        )
            ->when($request->search_type, function($query) use ($request) {
                $query->where('search_type', $request->search_type);
            })
            ->when($request->keyword, function($query) use ($request) {
                $query->where('keyword', 'like', "%{$request->keyword}%");
            })
            ->groupBy('keyword', 'search_type')
            ->orderBy('search_count', 'desc')
            ->paginate(20);

        return view('back.searches.index', compact('searches'));
    }

    public function details(Request $request)
    {
        $keyword=$request->keyword;
        $type=$request->type;
        // دریافت لیست کاربرانی که این کلمه را جستجو کرده‌اند
        $searches = Search::with('user')
            ->where('keyword', $keyword)
            ->where('search_type', $type)
            ->orderBy('searched_at', 'desc')
            ->get();

        $total_searches=$searches->count();
        // رندر کردن ویو جزئیات
        $html = view('back.searches.details', compact('searches','keyword','type','total_searches'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    // متد destroy برای حذف بر اساس keyword و search_type
    public function destroy($keyword, $type = null)
    {
        // اگر پارامتر اول از نوع Search بود (برای استفاده در Route Model Binding)
        if ($keyword instanceof Search) {
            $search = $keyword;
            $search->delete(); // ✅ این درست کار می‌کند
            return back()->with('success', 'رکورد جستجو حذف شد');
        }

        // در غیر این صورت حذف بر اساس keyword و type
        $query = Search::where('keyword', $keyword);

        if ($type) {
            $query->where('search_type', $type);
        }

        $searches = $query->get();
        $deletedCount = 0;

        foreach ($searches as $search) {
            $search->delete(); // این رویدادها را فعال می‌کند
            $deletedCount++;
        }

        return back()->with('success', "$deletedCount رکورد جستجو حذف شد");
    }

    public function multipleDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $selectedItems = $request->ids;
        $deletedCount = 0;

        foreach ($selectedItems as $item) {
            list($keyword, $type) = explode('__', $item);

            $search = Search::where('keyword', $keyword)
                ->when($type, function($q) use ($type) {
                    return $q->where('search_type', $type);
                })
                ->first();

            if ($search) {
                $search->delete(); // رویدادها فعال می‌شوند
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$deletedCount رکورد جستجو حذف شد"
        ]);
    }
}
