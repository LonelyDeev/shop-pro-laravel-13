<?php

namespace App\Jobs;

use App\Services\PackageInstallerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdatePackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(
        public string $slug,
        public ?int $adminId = null
    ) {
        $this->onQueue(config('packages.queue.queue', 'default'));
        $this->onConnection(config('packages.queue.connection', 'database'));
    }

    public function handle(PackageInstallerService $installer): void
    {
        Log::info('UpdatePackageJob started', ['slug' => $this->slug]);
        $installer->update($this->slug, $this->adminId);

    }

    public function failed(Throwable $e): void
    {
        Log::error('UpdatePackageJob FAILED', [
            'slug'  => $this->slug,
            'error' => $e->getMessage(),
        ]);
    }
}
