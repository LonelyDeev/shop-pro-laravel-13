<?php

namespace App\Jobs;

use App\Imports\UsersImport;
use App\Traits\ImportLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ImportUsersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ImportLogger;

    public $tries = 10;
    public $backoff = [30, 60, 120, 300, 600];
    public $uniqueFor = 3600;

    protected $filePath;
    protected $jobData;

    public function __construct($filePath, $jobData)
    {
        $this->filePath = $filePath;
        $this->jobData = $jobData;
    }

    public function uniqueId()
    {
        return 'import_users_' . ($this->jobData['user_id'] ?? 'guest');
    }

    public function handle()
    {
        try {
            $fullPath = Storage::disk('storage')->path($this->filePath);
            if (!file_exists($fullPath)) {
                throw new \Exception("فایل وجود ندارد: {$fullPath}");
            }

            $request = new Request();
            $request->merge([
                'filters' => $this->jobData['filters'],
                'update_duplicate' => $this->jobData['update_duplicate'],
            ]);

            $import = new UsersImport($request);
            Excel::import($import, $fullPath);

            $report = $import->getReport();

            if ($report['fail_count'] > 0) {
                $this->storeErrorReport($report);
            }

            if (Storage::disk('storage')->exists($this->filePath)) {
                Storage::disk('storage')->delete($this->filePath);
            }

            $this->logImportSuccess('users', $report, $this->filePath, $this->jobData['user_id'] ?? null);

            Log::info('واردات کاربران با موفقیت انجام شد', [
                'user_id' => $this->jobData['user_id'] ?? null,
                'file' => $this->filePath,
                'report' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('خطا در Job واردات کاربران: ' . $e->getMessage(), [
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

        foreach (glob($logPath . '/users_import_errors_*.json') as $file) {
            @unlink($file);
        }

        \Log::channel('import_errors')->error('خطاهای واردات کاربران', $report);

        $errorFile = storage_path('logs/users_import_errors_' . date('Y-m-d_H-i-s') . '.json');
        file_put_contents($errorFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function failed(\Throwable $exception)
    {
        Log::error('واردات کاربران پس از ۱۰ بار تلاش ناموفق بود', [
            'user_id' => $this->jobData['user_id'] ?? null,
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);

        $this->logImportFailed('users', $this->filePath, $this->jobData['user_id'] ?? null, $exception->getMessage());

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
