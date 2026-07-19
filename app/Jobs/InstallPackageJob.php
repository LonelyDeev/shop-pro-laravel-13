<?php

namespace App\Jobs;

use App\Models\InstalledModule;
use App\Models\PackagePurchase;
use App\Services\PackageInstallerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class InstallPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;       // در صورت fail، retry دستی
    public int $timeout = 600;   // 10 دقیقه

    public function __construct(
        public string $slug,
        public string $licenseKey,
        public ?int $adminId = null,
        public ?int $purchaseId = null,
        public ?string $downloadToken = null
    ) {
        $this->onQueue(config('packages.queue.queue', 'default'));
        $this->onConnection(config('packages.queue.connection', 'database'));
    }

    public function handle(PackageInstallerService $installer): void
    {
        Log::info('InstallPackageJob started', ['slug' => $this->slug]);

        $installed = $installer->install(
            $this->slug,
            $this->licenseKey,
            $this->adminId,
            $this->downloadToken
        );

        // بروزرسانی رکورد خرید در صورت وجود
        if ($this->purchaseId) {
            $purchase = PackagePurchase::find($this->purchaseId);
            if ($purchase) {
                $purchase->update([
                    'license_key'         => $this->licenseKey,
                    'license_expires_at'  => $installed->license_expires_at,
                ]);
            }
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('InstallPackageJob FAILED', [
            'slug'  => $this->slug,
            'error' => $e->getMessage(),
        ]);

        if ($this->purchaseId) {
            PackagePurchase::where('id', $this->purchaseId)
                ->where('status', PackagePurchase::STATUS_PENDING)
                ->update(['status' => PackagePurchase::STATUS_FAILED]);
        }

        // علامت‌گذاری ماژول (در صورت وجود) به‌عنوان failed
        InstalledModule::where('slug', $this->slug)
            ->where('status', InstalledModule::STATUS_UPDATING)
            ->update(['status' => InstalledModule::STATUS_FAILED, 'last_error' => $e->getMessage()]);
    }
}
