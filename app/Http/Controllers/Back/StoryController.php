<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Story;
use App\Models\StoryComment;
use App\Models\StoryInteraction;
use App\Models\StoryLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class StoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:stories');
    }
    public function index(Request $request)
    {
        $this->authorize('stories.index');
        $query = Story::query()
            ->withCount(['comments as approved_comments_count' => function($q) {
                $q->where('status', 'approved');
            }])
            ->withCount(['comments as pending_comments_count' => function($q) {
                $q->where('status', 'pending');
            }]);

        // ========== فیلتر عنوان ==========
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // ========== فیلتر وضعیت انتشار ==========
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'available') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', now());
            } elseif ($request->status === 'unavailable') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
            }
        }

        // ========== فیلتر بازه زمانی ==========
        if ($request->filled('date_range') && $request->date_range !== 'all') {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month);
                    break;
            }
        }

        // ========== فیلتر حداقل بازدید ==========
        if ($request->filled('min_views')) {
            $query->where('views_count', '>=', $request->min_views);
        }

        // ========== فیلتر حداقل لایک ==========
        if ($request->filled('min_likes')) {
            $query->where('likes_count', '>=', $request->min_likes);
        }

        // ========== فیلتر نوع محتوا ==========
        if ($request->filled('content_type') && $request->content_type !== 'all') {
            $query->where('type', $request->content_type);
        }

        // ========== مرتب سازی ==========
        if ($request->filled('ordering')) {
            switch ($request->ordering) {
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'most_viewed':
                    $query->orderBy('views_count', 'desc');
                    break;
                case 'most_liked':
                    $query->orderBy('likes_count', 'desc');
                    break;
                case 'most_commented':
                    $query->orderBy('approved_comments_count', 'desc');
                    break;
                case 'most_product_click':
                    $query->orderBy('product_clicks_count', 'desc');
                    break;
                case 'most_widget_click':
                    $query->orderBy('widget_clicks_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // ========== تعداد در صفحه ==========
        $paginate = $request->get('paginate', 10);

        if ($paginate === 'all') {
            $stories = $query->get();
        } else {
            $stories = $query->paginate((int)$paginate);
        }

        // ========== آمار برای نمایش در پنل ==========
        $statistics = [
            'total' => Story::count(),
            'active' => Story::where('status', 'active')->count(),
            'inactive' => Story::where('status', 'inactive')->count(),
            'expired' => Story::whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->count(),
            'total_views' => Story::sum('views_count'),
            'total_likes' => Story::sum('likes_count'),

            // اصلاح: تعداد کامنت‌ها از جدول story_comments
            'total_comments' => \App\Models\StoryComment::count(),

            // اصلاح: تعداد کلیک محصول از جدول story_interactions
            'total_product_clicks' => \App\Models\StoryInteraction::where('type', 'product_click')->sum('count'),

            // اصلاح: تعداد کلیک ویجت از جدول story_interactions
            'total_widget_clicks' => \App\Models\StoryInteraction::where('type', 'widget_click')->sum('count'),

            // آمار اضافی خوب
            'total_unique_viewers' => \App\Models\StoryInteraction::where('type', 'view_full')
                ->distinct('user_id', 'session_id')
                ->count('user_id'),
        ];

        return view('back.stories.index', compact('stories', 'statistics'));
    }
    public function create()
    {
        $this->authorize('stories.create');
        return view('back.stories.create');
    }

    public function getProductWithId(Request $request)
    {
        $product = Product::find($request->productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'محصول پیدا نشد!'
            ]);
        } else {
            $color = $product->getPrices()->first()->get_attributes()->first();
            $product = [
                'id' => $product->id,
                'title' => $product->title,
                'price' => number_format($product->getPrices()->first()->discount_price) . ' تومان ',
                'discount' => $product->getPrices()->first()->discount ? $product->getPrices()->first()->discount . '%' : '',
                'image' => asset($product->image),
                'color' => [
                    'name' => $color->name,
                    'value' => $color->value,
                ]
            ];
            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'محصول با موفقیت اضافه شد!'
            ]);
        }
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,image',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'required_if:type,video|nullable|url',
            'image' => 'required_if:type,image|nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'expiry_date' => 'required|string',
            'widget_title' => 'nullable|string|max:255',
            'widget_link' => 'nullable',
            'product_id' => 'nullable|string|max:100|exists:products,id',
            'description' => 'nullable|string',
            'active_likes' => 'nullable|boolean',
            'active_comments' => 'nullable|boolean',
            'duration' => 'nullable|integer',
        ]);

        if ($request->expiry_date) {
            $validated['expiry_date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->expiry_date)->toCarbon();
        }


        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = uploadOptimizedImage($request->cover_image, 'stories');;
        }

        if ($request->type == "image" and $request->hasFile('image')) {
            $validated['image'] = uploadOptimizedImage($request->image, 'stories');
        }

        $validated['active_likes'] = $request->has('active_likes') ? 1 : 0;
        $validated['active_comments'] = $request->has('active_comments') ? 1 : 0;

        // مقادیر پیش‌فرض
        $validated['ordering'] = $request->ordering ?? 0;

        $validated['admin_id'] = auth('adminPanel')->user()->id;

        $validated['published_at']=now();
        Story::create($validated);

        session()->put('toast-success', 'استوری با موفقیت ایجاد شد.');
        return response("success");
    }


    public function edit($id)
    {
        $this->authorize('stories.update');

        $story = Story::findOrFail($id);
        $product = [];
        if ($story->product_id) {
            $product = Product::find($story->product_id);
            $color = $product->getPrices()->first()->get_attributes()->first();
            $product = [
                'id' => $product->id,
                'title' => $product->title,
                'price' => number_format($product->getPrices()->first()->discount_price) . ' تومان ',
                'discount' => $product->getPrices()->first()->discount ? $product->getPrices()->first()->discount . '%' : '',
                'image' => asset($product->image),
                'color' => [
                    'name' => @$color->name,
                    'value' => @$color->value,
                ]
            ];
        }

        return view('back.stories.edit', compact('story', 'product'));
    }

    /**
     * به‌روزرسانی استوری
     */
    public function update(Request $request, $id)
    {
        $story = Story::findOrFail($id);
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,image',
            'expiry_date' => 'required|string',
            'widget_title' => 'nullable|string|max:255',
            'widget_link' => 'nullable',
            'product_id' => 'nullable|string|max:100|exists:products,id',
            'description' => 'nullable|string',
            'active_likes' => 'nullable|boolean',
            'active_comments' => 'nullable|boolean',
            'duration' => 'nullable|integer',
        ];

        // اضافه کردن قوانین شرطی برای فایل‌ها
        if ($story->cover_image==null) {
            $rules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:5120';
        } else {
            $rules['cover_image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120';
        }

        if ($story->image==null and $request->type=="image") {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120';
        }

        if ($story->video==null and $request->type=="video") {
            $rules['video'] = 'required|url';  // حذف nullable چون required_if است
        } else {
            $rules['video'] = 'nullable|url';
        }

        $validated = $request->validate($rules);


        if ($request->expiry_date) {
            $validated['expiry_date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->expiry_date)->toCarbon();
        }

        if ($request->hasFile('cover_image')) {
            Storage::disk('public')->delete($story->cover_image);
            $validated['cover_image'] = uploadOptimizedImage($request->cover_image, 'stories', $story->id);;
        }

        if ($request->type == "image" and $request->hasFile('image')) {
            Storage::disk('public')->delete($story->image);
            $validated['image'] = uploadOptimizedImage($request->image, 'stories', $story->id);
        }

        $validated['active_likes'] = $request->has('active_likes') ? 1 : 0;
        $validated['active_comments'] = $request->has('active_comments') ? 1 : 0;

        // مقادیر پیش‌فرض
        $validated['ordering'] = $request->ordering ?? 0;

        $validated['admin_id'] = auth('adminPanel')->user()->id;

        $story->published_at=now();
        $story->update($validated);

        session()->put('toast-success', 'استوری با موفقیت بروزرسانی شد.');
        return response("success");
    }

    public function destroy(Story $story)
    {
        $this->authorize('stories.delete');

        if ($story->image && Storage::disk('public')->exists($story->image)) {
            Storage::disk('public')->delete($story->image);
        }
        if ($story->cover_image && Storage::disk('public')->exists($story->cover_image)) {
            Storage::disk('public')->delete($story->cover_image);
        }
        $story->interactions()->delete();
        $story->comments()->delete();
        $story->delete();

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('stories.delete');
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:stories,id',
        ]);

        foreach ($request->ids as $id) {
            $story = Story::find($id);
            $this->destroy($story);
        }

        return response('success');
    }


    public function details(Story $story)
    {
        $this->authorize('stories.details');
        // آمار بازدیدها
        $viewsCount = $story->views_count;
        $realViewsCount = $story->real_views_count;

        // لایک‌ها
        $likes = StoryLike::where('story_id', $story->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // کامنت‌ها
        $comments = StoryComment::where('story_id', $story->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // ========== لیست بازدیدکنندگان ==========
        $viewers = StoryInteraction::where('story_id', $story->id)
            ->where('type', 'view_full')
            ->orderBy('last_interacted_at', 'desc')
            ->paginate(20);

        // تعاملات کلیک
        $productClicks = StoryInteraction::where('story_id', $story->id)
            ->where('type', 'product_click')
            ->sum('count');

        $widgetClicks = StoryInteraction::where('story_id', $story->id)
            ->where('type', 'widget_click')
            ->sum('count');

        // آمار پیشرفت مشاهده
        $progressStats = [
            'progress_25' => StoryInteraction::where('story_id', $story->id)->where('type', 'progress_25')->count(),
            'progress_50' => StoryInteraction::where('story_id', $story->id)->where('type', 'progress_50')->count(),
            'progress_75' => StoryInteraction::where('story_id', $story->id)->where('type', 'progress_75')->count(),
            'progress_100' => StoryInteraction::where('story_id', $story->id)->where('type', 'progress_100')->count(),
        ];

        // جزئیات کلیک محصول
        $productClickDetails = StoryInteraction::where('story_id', $story->id)
            ->where('type', 'product_click')
            ->orderBy('count', 'desc')
            ->get();

        // جزئیات کلیک ویجت
        $widgetClickDetails = StoryInteraction::where('story_id', $story->id)
            ->where('type', 'widget_click')
            ->orderBy('count', 'desc')
            ->get();

        return view('back.stories.details', compact('story', 'viewsCount', 'realViewsCount', 'likes', 'comments', 'productClicks', 'widgetClicks', 'progressStats', 'productClickDetails', 'widgetClickDetails','viewers'));
    }
    /**
     * کپی کردن استوری
     */
    public function duplicate($id)
    {
        $original = Story::findOrFail($id);

        $newStory = $original->replicate();
        $newStory->title = $original->title . ' (کپی)';
        $newStory->views_count = 0;
        $newStory->likes_count = 0;
        $newStory->sort_order = 0;
        $newStory->created_at = now();
        $newStory->updated_at = now();
        $newStory->save();

        // کپی فایل تصویر
        if ($original->cover_image && Storage::disk('public')->exists($original->cover_image)) {
            $extension = pathinfo($original->cover_image, PATHINFO_EXTENSION);
            $newPath = 'stories/covers/copy_' . Str::random(10) . '.' . $extension;
            Storage::disk('public')->copy($original->cover_image, $newPath);
            $newStory->cover_image = $newPath;
            $newStory->save();
        }

        return redirect()->route('admin.stories.index')
            ->with('success', 'استوری با موفقیت کپی شد');
    }

    /**
     * صفحه آمار و گزارشات
     */
    public function statistics()
    {
        // آمار کلی
        $totalStories = Story::count();
        $totalViews = Story::sum('views_count');
        $totalLikes = Story::sum('likes_count');
        $totalInteractions = Story::has('interactions')->withCount('interactions')->get()->sum('interactions_count');

        // بهترین استوری‌ها
        $mostViewed = Story::orderBy('views_count', 'desc')->limit(10)->get();
        $mostLiked = Story::orderBy('likes_count', 'desc')->limit(10)->get();
        $mostInteractive = Story::withCount('interactions')
            ->orderBy('interactions_count', 'desc')
            ->limit(10)
            ->get();

        // آمار 30 روز اخیر
        $dailyStats = \DB::table('story_interactions')
            ->select(\DB::raw('DATE(interacted_at) as date'), \DB::raw('count(*) as total'))
            ->where('interacted_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // آمار بر اساس نوع تعامل
        $interactionTypes = \DB::table('story_interactions')
            ->select('type', \DB::raw('count(*) as total'))
            ->where('interacted_at', '>=', now()->subDays(30))
            ->groupBy('type')
            ->get();

        // آمار بر اساس دستگاه
        $deviceStats = \DB::table('story_interactions')
            ->select('device_type', \DB::raw('count(*) as total'))
            ->where('interacted_at', '>=', now()->subDays(30))
            ->groupBy('device_type')
            ->get();

        return view('admin.stories.statistics', compact(
            'totalStories', 'totalViews', 'totalLikes', 'totalInteractions',
            'mostViewed', 'mostLiked', 'mostInteractive',
            'dailyStats', 'interactionTypes', 'deviceStats'
        ));
    }

    /**
     * تغییر ترتیب نمایش (Drag & Drop)
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'exists:stories,id',
            'orders.*.sort_order' => 'integer'
        ]);

        foreach ($request->orders as $item) {
            Story::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * پیش‌نمایش استوری در پنل ادمین
     */
    public function preview($id)
    {
        $story = Story::findOrFail($id);
        return view('admin.stories.preview', compact('story'));
    }


    public function changeStatusComment(Request $request,StoryComment $comment)
    {
        try {

            // تغییر وضعیت به تایید شده
            $comment->update([
                'status' => $request->status
            ]);

            // پاک کردن کش آمار (اگر از کش استفاده می‌کنی)
            Cache::forget('admin_stories_statistics');

            $message=" کامنت با موفقیت تایید شد";
            if ($request->status=="rejected") {
                $message=" کامنت با موفقیت رد شد";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تایید کامنت: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * رد کردن کامنت (غیرفعال)
     */
    public function rejectComment($commentId)
    {
        try {
            $comment = StoryComment::findOrFail($commentId);

            // تغییر وضعیت به رد شده
            $comment->update([
                'status' => 'rejected'
            ]);

            Cache::forget('admin_stories_statistics');

            return response()->json([
                'success' => true,
                'message' => 'کامنت با موفقیت رد شد',
                'comment' => $comment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در رد کامنت: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف کامنت
     */
    public function destroyComment($commentId)
    {
        try {
            $comment = StoryComment::findOrFail($commentId);
            $storyId = $comment->story_id;
            $story = Story::find($storyId);

            // حذف کامنت
            $comment->delete();

            // کاهش تعداد کامنت‌های استوری (اگر کامنت تایید شده بود)
            if ($comment->status === 'approved') {
                $story->decrement('comments_count');
            }

            // حذف تعاملات مربوط به این کامنت
            StoryInteraction::where('type', 'comment')
                ->where('element_id', $commentId)
                ->delete();

            Cache::forget('admin_stories_statistics');

            return response()->json([
                'success' => true,
                'message' => 'کامنت با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کامنت: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عملیات گروهی کامنت‌ها
     */
    public function multipleOperationComments(Request $request)
    {

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:story_comments,id',
            'comment_status'=> 'required|in:deleted,approved,rejected',
        ]);

        if ($request->comment_status=="deleted") {
            $response=$this->multipleDestroyComments($request);
        }else{
            $response=$this->multipleStatusComments($request);
        }
        return response()->json([
            'success' => $response->original['success'],
            'status' => $response->original['status'],
            'message' => $response->original['message']
        ]);

    }
    public function multipleStatusComments(Request $request)
    {

        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:story_comments,id',
                'comment_status'=>'required|in:approved,rejected',
            ]);

            $count = StoryComment::whereIn('id', $request->ids)
                ->update(['status' => $request->comment_status]);

            Cache::forget('admin_stories_statistics');

            $message="{$count} کامنت با موفقیت تایید شد";
            $status="approved";
            if ($request->comment_status=="rejected") {
                $message="{$count} کامنت با موفقیت رد شد";
                $status="rejected";
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تایید گروهی کامنت‌ها'
            ], 500);
        }
    }

    /**
     * حذف گروهی کامنت‌ها
     */
    public function multipleDestroyComments(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:story_comments,id'
            ]);

            $comments = StoryComment::whereIn('id', $request->ids)->get();

            $count = 0;
            foreach ($comments as $comment) {
                $story = Story::find($comment->story_id);
                if ($comment->status === 'approved') {
                    $story->decrement('comments_count');
                }
                $comment->delete();
                $count++;

                // حذف تعاملات
                StoryInteraction::where('type', 'comment')
                    ->where('element_id', $comment->id)
                    ->delete();
            }

            Cache::forget('admin_stories_statistics');

            return response()->json([
                'success' => true,
                'status' => 'deleted',
                'message' => "{$count} کامنت با موفقیت حذف شد"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف گروهی کامنت‌ها'
            ], 500);
        }
    }

}
