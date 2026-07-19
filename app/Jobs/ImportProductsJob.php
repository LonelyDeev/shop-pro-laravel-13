<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use App\Services\StockMovementService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Traits\ImportLogger;

class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ImportLogger;


    public $tries = 10;
    public $backoff = [30, 60, 120, 300, 600];

    protected $filePath;
    protected $jobData; // فقط داده‌های قابل سریالایز

    public function __construct($filePath, $jobData)
    {
        $this->filePath = $filePath;
        $this->jobData = $jobData;
    }

    public function uniqueId()
    {
        return 'import_products_' . ($this->jobData['user_id'] ?? 'guest');
    }
    public function handle()
    {
        try {

            // مسیر کامل فایل
            $fullPath = Storage::disk('storage')->path($this->filePath);
            if (!file_exists($fullPath)) {
                throw new \Exception("فایل وجود ندارد: {$fullPath}");
            }


            $request = new Request();
            $request->merge([
                'filters' => $this->jobData['filters'],
                'warehouse_id' => $this->jobData['warehouse_id'],
                'update_duplicate' => $this->jobData['update_duplicate'],
            ]);

            $import = new ProductsImport($request, app(StockMovementService::class));


            Excel::import($import, $fullPath);

            $report = $import->getReport();

            // ذخیره گزارش خطاها
            if ($report['fail_count'] > 0) {
                $this->storeErrorReport($report);
            }

            // ======== حذف فایل پس از پردازش موفق ========
            if (Storage::disk('storage')->exists($this->filePath)) {
                Storage::disk('storage')->delete($this->filePath);
                Log::info('فایل اکسل پس از پردازش حذف شد', [
                    'file' => $this->filePath
                ]);
            }

            $this->logImportSuccess('products', $report, $this->filePath, $this->jobData['user_id'] ?? null);

            Log::info('واردات محصولات با موفقیت انجام شد', [
                'user_id' => $this->jobData['user_id'] ?? null,
                'file' => $this->filePath,
                'report' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('خطا در Job واردات محصولات: ' . $e->getMessage(), [
                'file' => $this->filePath,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function storeErrorReport($report)
    {
        $logPath = storage_path('logs');
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }

        // حذف فایل‌های قبلی
        foreach (glob($logPath . '/products_import_errors_*.json') as $file) {
            @unlink($file);
        }

        \Log::channel('import_errors')->error('خطاهای واردات محصولات', $report);

        $errorFile = storage_path('logs/products_import_errors_' . date('Y-m-d_H-i-s') . '.json');
        file_put_contents($errorFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function failed(\Throwable $exception)
    {
        Log::error('واردات محصولات پس از ۱۰ بار تلاش ناموفق بود', [
            'user_id' => $this->jobData['user_id'] ?? null,
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);

        // ======== لاگ شکست Job ========
        $this->logImportFailed('products', $this->filePath, $this->jobData['user_id'] ?? null, $exception->getMessage());

        $errorReport = [
            'success_count' => 0,
            'fail_count' => 1,
            'total_count' => 1,
            'failed_rows' => [
                [
                    'row' => 0,
                    'title' => 'نامشخص',
                    'error' => 'خطا در پردازش فایل: ' . $exception->getMessage(),
                    'data' => []
                ]
            ],
            'errors' => [
                [
                    'row' => 0,
                    'title' => 'نامشخص',
                    'error' => 'خطا در پردازش فایل: ' . $exception->getMessage(),
                    'data' => []
                ]
            ],
            'duplicates' => [],
            'update_duplicate' => false
        ];

        $this->storeErrorReport($errorReport);
    }
}
