<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\ReturnReason;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ReturnRequest::class, 'returnRequest');
    }

    /**
     * لیست مرجوعی‌ها
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'orderItem.product', 'user', 'reason', 'images']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('id', $search);
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $returns = $query->latest()->paginate(20);
        $stats = [
            'total'              => ReturnRequest::count(),
            'pending'            => ReturnRequest::where('status', 'pending')->count(),
            'approved'           => ReturnRequest::where('status', 'approved')->count(),
            'shipped_by_customer'=> ReturnRequest::where('status', 'shipped_by_customer')->count(),
            'received'           => ReturnRequest::where('status', 'received')->count(),
            'reshipped'          => ReturnRequest::where('status', 'reshipped')->count(),
            'completed'          => ReturnRequest::where('status', 'completed')->count(),
            'rejected'           => ReturnRequest::where('status', 'rejected')->count(),
            'cash_count'         => ReturnRequest::where('payment_type', 'cash')->count(),
            'credit_count'       => ReturnRequest::where('payment_type', 'credit')->count(),
            'installment_count'  => ReturnRequest::where('payment_type', 'installment')->count(),
        ];

        return view('back.returns.index', compact('returns', 'stats'));
    }

    /**
     * نمایش جزئیات
     */
    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order', 'orderItem.product', 'user', 'reason', 'images']);
        return view('back.returns.show', compact('returnRequest'));
    }

    /**
     * ۱. تایید اولیه (پس از بررسی → منتظر ارسال محصول)
     */
    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        if (!$returnRequest->canBeApproved()) {
            return redirect()->back()->with('error', 'این درخواست قابل تایید نیست.');
        }

        $returnRequest->update([
            'status'       => ReturnRequest::STATUS_APPROVED,
            'admin_id'     => auth()->id(),
            'admin_notes'  => $request->admin_notes,
            'approved_at'  => now(),
        ]);
        $returnRequest->orderItem->update(['return_status' => 'approved']);

        return redirect()->back()->with('success', 'تایید اولیه شد. از مشتری بخواهید محصول را ارسال کند.');
    }

    /**
     * ۲. ثبت ارسال محصول توسط مشتری
     */
    public function markCustomerShipped(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        if (!$returnRequest->isApproved()) {
            return redirect()->back()->with('error', 'این درخواست در وضعیت مناسب نیست.');
        }

        $returnRequest->update([
            'status'                => ReturnRequest::STATUS_SHIPPED_BY_CUSTOMER,
            'customer_shipped_at'   => now(),
            'admin_notes'           => $request->admin_notes,
        ]);
        $returnRequest->orderItem->update(['return_status' => 'shipped_by_customer']);

        return redirect()->back()->with('success', 'ثبت شد. محصول توسط مشتری ارسال شده است.');
    }

    /**
     * ۳. محصول دریافت شد — در حال بررسی نهایی
     */
    public function markReceived(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        if (!$returnRequest->canBeReceived()) {
            return redirect()->back()->with('error', 'این درخواست قابل دریافت نیست.');
        }

        $returnRequest->update([
            'status'       => ReturnRequest::STATUS_RECEIVED,
            'admin_notes'  => $request->admin_notes,
            'received_at'  => now(),
        ]);
        $returnRequest->orderItem->update(['return_status' => 'received']);

        return redirect()->back()->with('success', 'محصول دریافت شد. در حال بررسی نهایی.');
    }

    /**
     * ۴الف. محصول مشکلی نداشت → دوباره ارسال شود
     */
    public function reship(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'inspection_result' => 'nullable|string|max:500',
            'admin_notes'       => 'nullable|string|max:500',
        ]);

        if (!$returnRequest->canReship()) {
            return redirect()->back()->with('error', 'این درخواست قابل این عملیات نیست.');
        }

        $returnRequest->update([
            'status'            => ReturnRequest::STATUS_RESHIPPED,
            'inspection_result' => $request->inspection_result ?? 'محصول مشکلی نداشت',
            'admin_notes'       => $request->admin_notes,
            'reshipped_at'      => now(),
            'reship_product'    => true,
        ]);
        $returnRequest->orderItem->update(['return_status' => 'reshipped']);

        return redirect()->back()->with('success', 'محصول دوباره به مشتری ارسال خواهد شد.');
    }

    /**
     * ۴ب. محصول مشکل داشت → تایید نهایی + بازگشت وجه به کیف پول + بازگشت اعتبار
     *
     * این متد هسته اصلی رفع باگ هست:
     * - برای سفارشات نقدی: کل مبلغ آیتم به کیف پول برمی‌گرده
     * - برای سفارشات اعتباری: مبلغ پرداخت‌شده (قسط اول + اقساط پرداخت‌شده) × سهم آیتم به کیف پول
     *   و اعتبار استفاده‌شده برای آیتم به حساب اعتباری کاربر برمی‌گرده
     * - برای سفارشات اقساطی: مبلغ پرداخت‌شده (پیش‌پرداخت + اقساط پرداخت‌شده) × سهم آیتم به کیف پول
     *
     * نکته: هزینه ارسال هرگز جزو مرجوعی محاسبه نمی‌شه!
     */
    public function complete(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'inspection_result' => 'nullable|string|max:500',
            'admin_notes'        => 'nullable|string|max:500',
            'refund_to_wallet'   => 'nullable|boolean',
        ]);

        if (!$returnRequest->canComplete()) {
            return redirect()->back()->with('error', 'این درخواست قابل تکمیل نیست.');
        }

        // ===== محاسبه مجدد breakdown (برای اطمینان از مقادیر به‌روز) =====
        // این کار برای اینه که اگه کاربر بین ثبت درخواست و تکمیل، قسط جدیدی پرداخت کرده،
        // محاسبات به‌روز باشه. اگه می‌خواید فقط مقادیر زمان ثبت استفاده بشه، این خط رو کامنت کنید.
        $breakdown = $returnRequest->calculateRefundBreakdown();

        $refundAmount        = $breakdown['refund_amount'];
        $walletRefundAmount  = $breakdown['wallet_refund_amount'];
        $creditRestoreAmount = $breakdown['credit_restore_amount'];
        $refundToWallet      = $request->boolean('refund_to_wallet', true);

        try {
            DB::transaction(function () use (
                $returnRequest, $request, $refundAmount,
                $walletRefundAmount, $creditRestoreAmount, $refundToWallet
            ) {
                // ===== ۱. بازگشت وجه به کیف پول کاربر =====
                if ($refundToWallet && $walletRefundAmount > 0 && !$returnRequest->paid_to_wallet) {
                    $user = $returnRequest->user;
                    $wallet = $user ? $user->getWallet() : null;

                    if ($wallet) {
                        $wallet->increment('balance', $walletRefundAmount);
                        WalletHistory::create([
                            'wallet_id'   => $wallet->id,
                            'type'        => 'deposit',
                            'amount'      => $walletRefundAmount,
                            'source'      => 'admin',
                            'status'      => 'success',
                            'order_id'    => $returnRequest->order_id,
                            'description' => sprintf(
                                "بازگشت وجه مرجوعی #%d — سفارش #%d — محصول: %s — مبلغ: %s ت",
                                $returnRequest->id,
                                $returnRequest->order_id,
                                $returnRequest->orderItem?->title ?? '—',
                                number_format($walletRefundAmount)
                            ),
                        ]);
                    } else {
                        Log::warning('ReturnRequest: Wallet not found for user', [
                            'user_id' => $returnRequest->user_id,
                        ]);
                    }
                }

                // ===== ۲. بازگشت اعتبار به حساب اعتباری کاربر (فقط سفارشات اعتباری) =====
                if ($creditRestoreAmount > 0 && !$returnRequest->credit_restored) {
                    $this->restoreCredit($returnRequest, $creditRestoreAmount);
                }

                // ===== ۳. لغو طرح اعتباری/اقساطی =====
                // وقتی مرجوعی تکمیل می‌شه و سفارش اعتباری یا اقساطی هست،
                // طرح باید لغو بشه و اقساط پرداخت‌نشده باطل بشن
                // (این کار جلوگیری می‌کنه از اینکه کاربر اقساط باقی‌مانده رو برای محصول مرجوع‌شده پرداخت کنه)
                $returnRequest->cancelPaymentPlan();

                // ===== ۴. آپدیت درخواست مرجوعی =====
                $returnRequest->update([
                    'status'                => ReturnRequest::STATUS_COMPLETED,
                    'admin_id'              => auth()->id(),
                    'admin_notes'           => $request->admin_notes,
                    'inspection_result'     => $request->inspection_result ?? 'محصول مشکل داشت',
                    'completed_at'          => now(),
                    'refund_to_wallet'      => $refundToWallet,
                    'refund_amount'         => $refundAmount,
                    'wallet_refund_amount'  => $walletRefundAmount,
                    'credit_restore_amount' => $creditRestoreAmount,
                    'paid_to_wallet'        => $refundToWallet && $walletRefundAmount > 0,
                    'credit_restored'       => $creditRestoreAmount > 0,
                ]);

                // ===== ۵. آپدیت آیتم سفارش =====
                $returnRequest->orderItem->update([
                    'return_status'    => 'completed',
                    'refunded'         => true,
                    'refunded_at'      => now(),
                    'refunded_amount'  => $refundAmount,
                ]);
            });

            // پیام موفقیت با جزئیات
            $successMsg = "مرجوعی تایید نهایی شد.";
            if ($walletRefundAmount > 0) {
                $successMsg .= " " . number_format($walletRefundAmount) . " تومان به کیف پول کاربر واریز شد.";
            }
            if ($creditRestoreAmount > 0) {
                $successMsg .= " " . number_format($creditRestoreAmount) . " تومان به اعتبار کاربر بازگشت داده شد.";
            }
            if ($walletRefundAmount === 0 && $creditRestoreAmount === 0) {
                $successMsg .= " هیچ مبلغی برای بازگشت وجود نداشت.";
            }
            // اگه طرح اعتباری/اقساطی لغو شده
            if ($returnRequest->isCreditPayment() || $returnRequest->isInstallmentPayment()) {
                $successMsg .= " طرح " . $returnRequest->paymentTypeLabel() . " لغو شد و اقساط باقی‌مانده باطل شدند.";
            }

            return redirect()->back()->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error completing refund', [
                'return_request_id' => $returnRequest->id,
                'error'             => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'خطا در تکمیل مرجوعی: ' . $e->getMessage());
        }
    }

    /**
     * بازگشت اعتبار به حساب اعتباری کاربر (CreditPay)
     */
    private function restoreCredit(ReturnRequest $returnRequest, int $amount): void
    {
        if (!function_exists('module_is_active') || !module_is_active('CreditPay')) {
            Log::warning('ReturnRequest: CreditPay module not active, cannot restore credit', [
                'return_request_id' => $returnRequest->id,
            ]);
            return;
        }

        try {
            $creditOrder = \Modules\CreditPay\Models\CreditOrder::where('order_id', $returnRequest->order_id)->first();
            if (!$creditOrder) {
                Log::warning('ReturnRequest: CreditOrder not found for credit restore', [
                    'order_id' => $returnRequest->order_id,
                ]);
                return;
            }

            $account = $creditOrder->account;
            if (!$account) {
                Log::warning('ReturnRequest: CreditAccount not found', [
                    'credit_order_id' => $creditOrder->id,
                ]);
                return;
            }

            // کاهش used_credit و افزایش available_credit
            $account->decrement('used_credit', $amount);
            $account->increment('available_credit', $amount);
            $account->save();

            // ثبت تراکنش
            \Modules\CreditPay\Models\CreditTransaction::create([
                'account_id'    => $account->id,
                'type'          => 'refund_increase',
                'amount'        => $amount,
                'balance_after' => $account->fresh()->available_credit,
                'description'   => sprintf(
                    'بازگشت اعتبار مرجوعی #%d — سفارش #%d — محصول: %s',
                    $returnRequest->id,
                    $returnRequest->order_id,
                    $returnRequest->orderItem?->title ?? '—'
                ),
                'order_id'      => $returnRequest->order_id,
                'reference_type'=> ReturnRequest::class,
                'reference_id'  => $returnRequest->id,
                'created_by'    => auth()->id(),
            ]);

            Log::info('ReturnRequest: Credit restored successfully', [
                'return_request_id' => $returnRequest->id,
                'account_id'         => $account->id,
                'amount'             => $amount,
            ]);

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error restoring credit', [
                'return_request_id' => $returnRequest->id,
                'error'             => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * ۵. رد درخواست
     */
    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        if (in_array($returnRequest->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'این درخواست قابل رد نیست.');
        }

        $returnRequest->update([
            'status'           => ReturnRequest::STATUS_REJECTED,
            'admin_id'         => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
        ]);
        $returnRequest->orderItem->update(['return_status' => 'rejected']);

        return redirect()->back()->with('success', 'درخواست مرجوعی رد شد.');
    }

    /**
     * مدیریت دلایل مرجوعی
     */
    public function reasonsIndex()
    {
        $reasons = ReturnReason::latest()->paginate(20);
        return view('back.returns.reasons', compact('reasons'));
    }

    public function reasonsStore(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);
        ReturnReason::create([
            'title'       => $request->title,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'ordering'    => ReturnReason::max('ordering') + 1,
        ]);
        return redirect()->back()->with('success', 'دلیل مرجوعی ایجاد شد.');
    }

    public function reasonsDestroy(ReturnReason $reason)
    {
        $reason->delete();
        return redirect()->back()->with('success', 'دلیل مرجوعی حذف شد.');
    }

    public function reasonsToggle(ReturnReason $reason)
    {
        $reason->update(['is_active' => !$reason->is_active]);
        return response()->json(['success' => true]);
    }
}
