<?php
namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use ZipArchive;
use File;

class SelfUpdater
{
    protected $apiUrl;
    protected $token;
    protected $currentVersion;
    protected $basepath;

    public function __construct()
    {
        $this->apiUrl = config('app.url') . '/api/check-update'; // آدرس پنل مدیریت
        $this->token = config('services.updater.token'); // توکن مشتری در .env فروشگاه
        $this->currentVersion = config('app.version'); // نسخه فعلی در .env یا کانفیگ
        $this->basepath = base_path();
    }

    public function checkForUpdate()
    {
        $response = Http::get($this->apiUrl, [
            'token' => $this->token,
            'version' => $this->currentVersion
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['update_available' => false, 'error' => 'Connection failed'];
    }

    public function downloadAndInstall($downloadUrl)
    {
        $tempFile = storage_path('app/temp_update.zip');

        // دانلود فایل
        $downloadResponse = Http::get($downloadUrl);
        if (!$downloadResponse->successful()) {
            throw new \Exception('Failed to download update package');
        }

        // ذخیره موقت
        file_put_contents($tempFile, $downloadResponse->body());

        // استخراج فایل ZIP
        $zip = new ZipArchive();
        if ($zip->open($tempFile) === true) {
            $zip->extractTo($this->basepath);
            $zip->close();

            // حذف فایل موقت
            unlink($tempFile);

            // پاک کردن کش کانفیگ و ویو (اختیاری اما توصیه شده)
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return true;
        } else {
            throw new \Exception('Failed to unzip the package');
        }
    }
}
