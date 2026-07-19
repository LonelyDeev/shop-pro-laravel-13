<?php

namespace App\Console\Commands;

use App\Services\LicenseVerifier;
use Illuminate\Console\Command;

class VerifyLicensesCommand extends Command
{
    protected $signature = 'packages:verify-licenses
                            {--notify : ارسال نوتیفیکیشن برای لایسنس‌های منقضی}';

    protected $description = 'بررسی اعتبار لایسنس همه ماژول‌های نصب‌شده';

    public function handle(LicenseVerifier $verifier): int
    {
        $this->info('شروع بررسی لایسنس‌ها...');

        $results = $verifier->verifyAll();

        if (empty($results)) {
            $this->info('هیچ ماژول لایسنس‌داری یافت نشد.');
            return self::SUCCESS;
        }

        $expired = [];
        foreach ($results as $slug => $res) {
            if ($res['valid']) {
                $this->line("  ✓ {$slug} - معتبر");
            } else {
                $this->error("  ✗ {$slug} - نامعتبر/منقضی");
                $expired[] = $slug;
            }
        }

        if ($this->option('notify') && !empty($expired)) {
            // در اینجا می‌توان notification ارسال کنید
            // مثلاً Notification::route('mail', config('packages.notify_email'))->notify(new LicensesExpired($expired));
            $this->warn('لایسنس‌های منقضی: ' . implode(', ', $expired));
        }

        return self::SUCCESS;
    }
}
