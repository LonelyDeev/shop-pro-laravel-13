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
    /**
     * کال‌بک درگاه پرداخت → بازگشت به صفحه ایندکس + نمایش مودال نتیجه
     */
    public function callback(Request $request)
    {
        $transactionId = $request->query('transaction_id')
            ?? $request->query('Authority')
            ?? $request->query('transactionId')
            ?? $request->query('tracking_code');

        $purchaseId = $request->query('purchase_id');

        if (!$transactionId && !$purchaseId) {
            return $this->redirectToIndexWithResult('error', 'خطا', 'اطلاعات تراکنش ناقص است.');
        }

        // پیدا کردن رکورد خرید
        $purchase = PackagePurchase::query()
            ->when($transactionId, fn ($q) => $q->where('transaction_id', $transactionId))
            ->when($purchaseId, fn ($q) => $q->orWhere('id', $purchaseId))
            ->first();

        if (!$purchase) {
            return $this->redirectToIndexWithResult('error', 'خطا', 'رکورد خرید یافت نشد.');
        }

        try {
            // بررسی نهایی پرداخت از طریق API
            $verify = $this->api->verifyPayment($transactionId ?: $purchase->transaction_id);

            if (!($verify['paid'] ?? false)) {
                $purchase->markAsFailed($verify['message'] ?? 'پرداخت تأیید نشد.');

                return $this->redirectToIndexWithResult('failed', 'پرداخت ناموفق بود',
                    'پرداخت شما تأیید نشد. در صورت کسر مبلغ، حداکثر ۷۲ ساعت بازمی‌گردد.', [
                        'package_name'   => $purchase->package_name ?? $purchase->package_slug,
                        'package_slug'   => $purchase->package_slug,
                        'amount'         => $purchase->amount ?? null,
                        'transaction_id' => $transactionId,
                    ]);
            }

            // اول لایسنس بررسی شود، بعد رکورد پرداخت‌شده علامت بخورد
            $licenseKey = $verify['license_key'] ?? null;
            if (!$licenseKey) {
                throw new RuntimeException('لایسنس از API دریافت نشد.');
            }

            $purchase->markAsPaid([
                'license_key'        => $licenseKey,
                'license_expires_at' => $verify['expires_at'] ?? null,
                'gateway'            => $verify['gateway'] ?? null,
            ]);

            InstallPackageJob::dispatch(
                $purchase->package_slug,
                $licenseKey,
                $purchase->admin_id,
                $purchase->id,
                $verify['download_token'] ?? null
            );

            Log::info('Package purchase verified, install dispatched', [
                'slug'        => $purchase->package_slug,
                'purchase_id' => $purchase->id,
                'transaction' => $transactionId,
            ]);

            return $this->redirectToIndexWithResult('success', 'پرداخت با موفقیت انجام شد',
                'مبلغ پرداخت‌شده تأیید شد و نصب پکیج آغاز گردید.', [
                    'package_name'   => $purchase->package_name ?? $purchase->package_slug,
                    'package_slug'   => $purchase->package_slug,
                    'amount'         => $purchase->amount ?? null,
                    'transaction_id' => $transactionId,
                    'license_key'    => $licenseKey,
                ]);

        } catch (Throwable $e) {
            Log::error('Payment callback failed', [
                'transaction' => $transactionId,
                'error'       => $e->getMessage(),
            ]);

            return $this->redirectToIndexWithResult('error', 'خطا در تأیید پرداخت', $e->getMessage(), [
                'transaction_id' => $transactionId,
            ]);
        }
    }

    /**
     * ریدایرکت به ایندکس همراه با دیتای نتیجه پرداخت (برای مودال)
     */
    private function redirectToIndexWithResult(string $status, string $title, string $message, array $extra = [])
    {
        return redirect()
            ->route('admin.packages.index')
            ->with('payment_result', array_merge([
                'status'  => $status,   // success | failed | error
                'title'   => $title,
                'message' => $message,
            ], $extra));
    }
}
