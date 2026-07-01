<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProductsJob;
use App\Imports\PostsImport;
use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Models\Post;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportPostsJob;
use App\Jobs\ImportUsersJob;
use App\Traits\ImportLogger;

class ImportsController extends Controller
{
    use ImportLogger;
    public function __construct()
    {
        $this->middleware('can:imports');
    }
    public function postsExcelImport()
    {
        return view('back.imports.postsExcelImport');
    }
    public function postsExcelImport_Store(Request $request)
    {
        $this->authorize('imports.posts');
        // ======== لاگ ارسال به صف ========

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'filters.title' => 'required',
        ], [
            'filters.title.required' => 'فیلد عنوان الزامی است',
        ]);

        $userId = auth('adminPanel')->id();
        $uniqueId = 'import_posts_' . ($userId ?? 'guest');
        $lockKey = 'laravel_unique_job_' . $uniqueId;

        if (Cache::has($lockKey)) {
            return response([
                'error' => 'درخواست قبلی برای واردات مقالات در حال پردازش است. لطفاً پس از اتمام آن، مجدداً تلاش کنید.'
            ], 429);
        }

        try {
            $file = $request->file('file');

            // نام ثابت برای فایل (همیشه جایگزین می‌شود)
            $extension = $file->getClientOriginalExtension();
            $filename = 'import_posts.' . $extension;

            // ذخیره در دیسک 'imports' در پوشه posts
            $filePath = Storage::disk('storage')->putFileAs('imports/posts', $file, $filename);

            if (!$filePath) {
                throw new \Exception('خطا در ذخیره فایل');
            }

            // ======== لاگ ارسال به صف ========
            $this->logQueueDispatch('posts', $filePath, $userId);

            $jobData = [
                'filters' => $request->input('filters'),
                'update_duplicate' => $request->has('update_duplicate') && $request->input('update_duplicate') == 1,
                'user_id' => auth()->id(),
            ];

            // ارسال Job به صف
            ImportPostsJob::dispatch($filePath, $jobData);
            Cache::put($lockKey, true, 60);
            return response([
                'success' => 'فایل با موفقیت آپلود شد و در صف پردازش قرار گرفت. نتیجه به اطلاع شما خواهد رسید.',
                'queue_status' => 'pending',
                'file' => $filePath
            ]);

        } catch (\Exception $e) {
            \Log::error('خطا در آپلود فایل مقالات: ' . $e->getMessage());
            return response(['error' => 'خطا در آپلود فایل: ' . $e->getMessage()], 500);
        }
    }
    public function deletePostErrorFile(Request $request)
    {
        $this->authorize('imports.posts');

        $file = $request->input('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'نام فایل ارسال نشده است']);
        }

        $filePath = storage_path('logs/' . $file);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'فایل وجود ندارد']);
        }

        if (unlink($filePath)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'خطا در حذف فایل']);
    }

    public function productsExcelImport()
    {
        $warehouses =Warehouse::active()->get();
        return view('back.imports.productsExcelImport',compact('warehouses'));
    }
    public function productsExcelImport_Store(Request $request)
    {
        $this->authorize('imports.products');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'filters.title' => 'required',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);


        $userId = auth('adminPanel')->id();
        $uniqueId = 'import_products_' . ($userId ?? 'guest');
        $lockKey = 'laravel_unique_job_' . $uniqueId;

        if (Cache::has($lockKey)) {
            return response([
                'error' => 'درخواست قبلی برای واردات محصولات در حال پردازش است. لطفاً پس از اتمام آن، مجدداً تلاش کنید.'
            ], 429);
        }



        $file = $request->file('file');

        $extension = $file->getClientOriginalExtension();
        $filename = 'import_products.' . $extension;
        $filePath = Storage::disk('storage')->putFileAs('imports/products', $file, $filename);

        // ======== لاگ ارسال به صف ========
        $this->logQueueDispatch('products', $filePath, $userId);

        // فقط داده‌های قابل سریالایز را ارسال کن
        $jobData = [
            'filters' => $request->input('filters'),
            'warehouse_id' => $request->input('warehouse_id'),
            'update_duplicate' => $request->has('update_duplicate') && $request->input('update_duplicate') == 1,
            'user_id' => auth()->id(),
        ];

        // ارسال Job به صف
        ImportProductsJob::dispatch($filePath, $jobData);
        Cache::put($lockKey, true, 240);
        return response([
            'success' => 'فایل با موفقیت آپلود شد و در صف پردازش قرار گرفت. نتیجه به اطلاع شما خواهد رسید.',
            'queue_status' => 'pending',
            'file' => $filePath
        ]);
    }
    public function deleteProductErrorFile(Request $request)
    {
        $this->authorize('imports.products');

        $file = $request->input('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'نام فایل ارسال نشده است']);
        }

        $filePath = storage_path('logs/' . $file);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'فایل وجود ندارد']);
        }

        if (unlink($filePath)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'خطا در حذف فایل']);
    }


    public function usersExcelImport()
    {
        return view('back.imports.usersExcelImport');
    }
    public function usersExcelImport_Store(Request $request)
    {
        $this->authorize('imports.users');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'filters.email' => 'required_without_all:filters.mobile',
            'filters.mobile' => 'required_without_all:filters.email',
            "filters.password" => "required",
        ], [
            'filters.email.required_without_all' => 'یکی از فیلد های ایمیل یا موبایل الزامی است',
            'filters.mobile.required_without_all' => 'یکی از فیلد های ایمیل یا موبایل الزامی است',
            'filters.password.required' => 'فیلد رمز ورود الزامی است',
        ]);

        $userId = auth('adminPanel')->id();
        $uniqueId = 'import_users_' . ($userId ?? 'guest');
        $lockKey = 'laravel_unique_job_' . $uniqueId;

        // بررسی وجود Job مشابه در صف یا پردازش
        if (Cache::has($lockKey)) {
            return response([
                'error' => 'درخواست قبلی برای واردات کاربران در حال پردازش است. لطفاً پس از اتمام آن، مجدداً تلاش کنید.'
            ], 429);
        }

        try {
            $file = $request->file('file');

            // نام ثابت برای فایل (همیشه جایگزین می‌شود)
            $extension = $file->getClientOriginalExtension();
            $filename = 'import_users.' . $extension;

            // ذخیره در دیسک 'imports' در پوشه users
            $filePath = Storage::disk('storage')->putFileAs('imports/products', $file, $filename);

            if (!$filePath) {
                throw new \Exception('خطا در ذخیره فایل');
            }

            // لاگ ارسال به صف
            $this->logQueueDispatch('users', $filePath, $userId);

            $jobData = [
                'filters' => $request->input('filters'),
                'update_duplicate' => $request->has('update_duplicate') && $request->input('update_duplicate') == 1,
                'user_id' => $userId,
            ];

            // ارسال Job به صف
            ImportUsersJob::dispatch($filePath, $jobData);

            return response([
                'success' => 'فایل با موفقیت آپلود شد و در صف پردازش قرار گرفت. نتیجه به اطلاع شما خواهد رسید.',
                'queue_status' => 'pending',
                'file' => $filePath
            ]);

        } catch (\Exception $e) {
            \Log::error('خطا در آپلود فایل کاربران: ' . $e->getMessage());
            return response(['error' => 'خطا در آپلود فایل: ' . $e->getMessage()], 500);
        }
    }

    public function deleteUserErrorFile(Request $request)
    {
        $this->authorize('imports.users');

        $file = $request->input('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'نام فایل ارسال نشده است']);
        }

        $filePath = storage_path('logs/' . $file);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'فایل وجود ندارد']);
        }

        if (unlink($filePath)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'خطا در حذف فایل']);
    }
}
