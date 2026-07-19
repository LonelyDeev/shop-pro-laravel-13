<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\InstallPackageJob;
use App\Models\PackagePurchase;
use App\Services\PackageApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PackagePaymentController extends Controller
{
    public function __construct(private PackageApiService $api) {}

    /* ===================================================================
     *  Callback بعد از پرداخت
     *  پروژه مدیریت به این URL برمی‌گردد با پارامتر transaction_id
     * =================================================================== */
    public function callback(Request $request)
    {
        $transactionId = $request->query('transaction_id')
            ?? $request->query('Authority')
            ?? $request->query('tracking_code');

        if (!$transactionId) {
            return redirect()
                ->route('admin.packages.index')
                ->with('error', 'اطلاعات تراکنش ناقص است.');
        }

        // پیدا کردن رکورد خرید
        $purchase = PackagePurchase::where('transaction_id', $transactionId)
            ->orWhere('id', $request->query('purchase_id'))
            ->first();

        if (!$purchase) {
            return redirect()
                ->route('admin.packages.index')
                ->with('error', 'رکورد خرید یافت نشد.');
        }

        try {
            // بررسی نهایی پرداخت از طریق API
            $verify = $this->api->verifyPayment($transactionId);

            if (!($verify['paid'] ?? false)) {
                $purchase->markAsFailed($verify['message'] ?? 'پرداخت تأیید نشد.');

                return redirect()
                    ->route('admin.packages.show', $purchase->package_slug)
                    ->with('error', 'پرداخت شما تأیید نشد. در صورت کسر مبلغ، حداکثر ۷۲ ساعت بازمی‌گردد.');
            }

            // موفقیت‌آمیز
            $purchase->markAsPaid([
                'license_key'        => $verify['license_key'] ?? null,
                'license_expires_at' => $verify['expires_at'] ?? null,
                'gateway'            => $verify['gateway'] ?? null,
            ]);

            $licenseKey = $verify['license_key'] ?? null;
            if (!$licenseKey) {
                throw new RuntimeException('لایسنس از API دریافت نشد.');
            }

            // ارسال به queue برای نصب
            InstallPackageJob::dispatch(
                $purchase->package_slug,
                $licenseKey,
                $purchase->admin_id,
                $purchase->id,
                $verify['download_token'] ?? null
            );

            Log::info('Package purchase verified, install dispatched', [
                'slug'         => $purchase->package_slug,
                'purchase_id'  => $purchase->id,
                'transaction'  => $transactionId,
            ]);

            return redirect()
                ->route('admin.packages.show', $purchase->package_slug)
                ->with('success', 'پرداخت با موفقیت انجام شد. نصب پکیج آغاز شد.');

        } catch (RuntimeException $e) {
            Log::error('Payment callback failed', [
                'transaction' => $transactionId,
                'error'       => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.packages.show', $purchase->package_slug)
                ->with('error', 'خطا در تأیید پرداخت: ' . $e->getMessage());
        }
    }
}
