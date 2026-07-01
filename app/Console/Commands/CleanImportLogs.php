<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('clean:import-logs')]
#[Description('Command description')]
class CleanImportLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('logs');
        $patterns = [
            'products_import_errors_*.json',
            'posts_import_errors_*.json',
            'users_import_errors_*.json'
        ];

        $now = time();
        $deletedCount = 0;

        foreach ($patterns as $pattern) {
            $files = glob($path . '/' . $pattern);

            foreach ($files as $file) {
                if ($now - filemtime($file) > 60 * 60 * 24 * 7) { // 7 روز
                    if (@unlink($file)) {
                        $deletedCount++;
                        $this->info("Deleted: " . basename($file));
                    }
                }
            }
        }

        $this->info("Total deleted files: {$deletedCount}");
    }
}
