<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class ReturnRequest extends Model
{
    protected $table = 'return_requests';
    protected $guarded = ['id'];

    protected $casts = [
        'refund_to_wallet'        => 'boolean',
        'reship_product'          => 'boolean',
        'paid_to_wallet'          => 'boolean',
        'credit_restored'         => 'boolean',
        'approved_at'             => 'datetime',
        'customer_shipped_at'     => 'datetime',
        'received_at'             => 'datetime',
        'reshipped_at'            => 'datetime',
        'completed_at'            => 'datetime',
        'rejected_at'             => 'datetime',
        'cancelled_at'            => 'datetime',
    ];

    // ===== ۹ وضعیت مرجوعی =====
    public const STATUS_PENDING              = 'pending';
    public const STATUS_APPROVED              = 'approved';
    public const STATUS_SHIPPED_BY_CUSTOMER  = 'shipped_by_customer';
    public const STATUS_RECEIVED              = 'received';
    public const STATUS_RESHIPPED             = 'reshipped';
    public const STATUS_COMPLETED             = 'completed';
    public const STATUS_REJECTED              = 'rejected';
    public const STATUS_CANCELLED             = 'cancelled';
    public const STATUS_FAILED                = 'failed';

    // ===== نوع پرداخت =====
    public const PAYMENT_CASH        = 'cash';
    public const PAYMENT_CREDIT      = 'credit';
    public const PAYMENT_INSTALLMENT = 'installment';

    public static function statusLabels(): array
    {
        return [
            'pending'              => ['label' => 'در حال بررسی اولیه',      'color' => '#f59e0b', 'bg' => '#fffbeb',  'icon' => 'fa-clock'],
            'approved'              => ['label' => 'تایید اولیه — منتظر ارسال محصول', 'color' => '#3b82f6', 'bg' => '#dbeafe',  'icon' => 'fa-check-circle'],
            'shipped_by_customer'  => ['label' => 'محصول توسط مشتری ارسال شد', 'color' => '#8b5cf6', 'bg' => '#ede9fe',  'icon' => 'fa-truck'],
            'received'              => ['label' => 'محصول دریافت شد — در حال بررسی', 'color' => '#06b6d4', 'bg' => '#cffafe',  'icon' => 'fa-box-open'],
            'reshipped'             => ['label' => 'محصول دوباره ارسال شد',    'color' => '#6366f1', 'bg' => '#e0e7ff',  'icon' => 'fa-truck-fast'],
            'completed'             => ['label' => 'تایید نهایی — وجه برگشت',  'color' => '#10b981', 'bg' => '#d1fae5',  'icon' => 'fa-check-double'],
            'rejected'              => ['label' => 'رد شده',                    'color' => '#ef4444', 'bg' => '#fee2e2',  'icon' => 'fa-times-circle'],
            'cancelled'             => ['label' => 'لغو توسط کاربر',            'color' => '#6b7280', 'bg' => '#f3f4f6',  'icon' => 'fa-ban'],
            'failed'                => ['label' => 'بررسی ناموفق',              'color' => '#dc2626', 'bg' => '#fef2f2',  'icon' => 'fa-exclamation-triangle'],
        ];
    }

    public static function paymentTypeLabels(): array
    {
        return [
            self::PAYMENT_CASH        => ['label' => 'نقدی',     'color' => '#1e40af', 'bg' => '#dbeafe', 'icon' => 'fa-money-bill-wave'],
            self::PAYMENT_CREDIT      => ['label' => 'اعتباری', 'color' => '#5b21b6', 'bg' => '#ede9fe', 'icon' => 'fa-credit-card'],
            self::PAYMENT_INSTALLMENT => ['label' => 'اقساطی',   'color' => '#92400e', 'bg' => '#fef3c7', 'icon' => 'fa-calendar-check'],
        ];
    }

    public function paymentTypeLabel(): string
    {
        $labels = self::paymentTypeLabels();
        return $labels[$this->payment_type]['label'] ?? $this->payment_type;
    }

    public function paymentTypeColor(): string
    {
        $labels = self::paymentTypeLabels();
        return $labels[$this->payment_type]['color'] ?? '#6b7280';
    }

    public function paymentTypeBg(): string
    {
        $labels = self::paymentTypeLabels();
        return $labels[$this->payment_type]['bg'] ?? '#f3f4f6';
    }

    public function paymentTypeIcon(): string
    {
        $labels = self::paymentTypeLabels();
        return $labels[$this->payment_type]['icon'] ?? 'fa-circle';
    }

    // ===== روابط =====
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(OrderItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function reason(): BelongsTo { return $this->belongsTo(ReturnReason::class, 'return_reason_id'); }
    public function images(): HasMany { return $this->hasMany(ReturnImage::class); }

    // ===== متدهای وضعیت =====
    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isShippedByCustomer(): bool { return $this->status === self::STATUS_SHIPPED_BY_CUSTOMER; }
    public function isReceived(): bool { return $this->status === self::STATUS_RECEIVED; }
    public function isReshipped(): bool { return $this->status === self::STATUS_RESHIPPED; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }
    public function isFailed(): bool { return $this->status === self::STATUS_FAILED; }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function canBeApproved(): bool { return $this->isPending(); }
    public function canBeReceived(): bool { return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_SHIPPED_BY_CUSTOMER]); }
    public function canComplete(): bool { return $this->isReceived(); }
    public function canReship(): bool { return $this->isReceived(); }

    public function isCashPayment(): bool { return $this->payment_type === self::PAYMENT_CASH; }
    public function isCreditPayment(): bool { return $this->payment_type === self::PAYMENT_CREDIT; }
    public function isInstallmentPayment(): bool { return $this->payment_type === self::PAYMENT_INSTALLMENT; }

    /**
     * آیا این درخواست بازگشت اعتبار داره؟
     */
    public function hasCreditRestore(): bool
    {
        return $this->isCreditPayment() && $this->credit_restore_amount > 0;
    }

    /**
     * آیا این درخواست بازگشت وجه به کیف پول داره؟
     */
    public function hasWalletRefund(): bool
    {
        return $this->wallet_refund_amount > 0;
    }

    // ====================================================================
    //  محاسبه مبلغ برگشتی - هسته اصلی رفع باگ
    // ====================================================================
    //
    //  نکته مهم: هزینه ارسال هرگز جزو مرجوعی محاسبه نمی‌شه!
    //
    //  برای سفارشات نقدی:
    //    - کل مبلغ آیتم (با تخفیف) به کیف پول برمی‌گرده
    //
    //  برای سفارشات اعتباری (CreditPay):
    //    - مبلغ پرداخت‌شده توسط کاربر (قسط اول + اقساط پرداخت‌شده) × سهم آیتم به کیف پول برمی‌گرده
    //    - اعتبار استفاده‌شده برای این آیتم به حساب اعتباری کاربر برمی‌گرده
    //
    //  برای سفارشات اقساطی (InstallmentPayment):
    //    - مبلغ پرداخت‌شده توسط کاربر (پیش‌پرداخت + اقساط پرداخت‌شده) × سهم آیتم به کیف پول برمی‌گرده
    // ====================================================================

    /**
     * محاسبه مبلغ برگشتی بر اساس نوع پرداخت سفارش
     *
     * @return array{payment_type, refund_amount, wallet_refund_amount, credit_restore_amount}
     */
    public function calculateRefundBreakdown(): array
    {
        $item = $this->orderItem;
        $order = $this->order;
        if (!$item || !$order) {
            return [
                'payment_type'          => self::PAYMENT_CASH,
                'refund_amount'         => 0,
                'wallet_refund_amount'  => 0,
                'credit_restore_amount' => 0,
            ];
        }

        $itemPaidAmount = (int) ($item->price * $item->quantity);

        // تشخیص نوع پرداخت
        $paymentType = self::detectPaymentType($order);

        switch ($paymentType) {
            case self::PAYMENT_CREDIT:
                return $this->calculateCreditRefund($item, $order, $itemPaidAmount);

            case self::PAYMENT_INSTALLMENT:
                return $this->calculateInstallmentRefund($item, $order, $itemPaidAmount);

            case self::PAYMENT_CASH:
            default:
                return [
                    'payment_type'          => self::PAYMENT_CASH,
                    'refund_amount'         => $itemPaidAmount,
                    'wallet_refund_amount'  => $itemPaidAmount,
                    'credit_restore_amount' => 0,
                ];
        }
    }

    /**
     * محاسبه مرجوعی برای سفارش اعتباری (CreditPay)
     */
    private function calculateCreditRefund(OrderItem $item, Order $order, int $itemPaidAmount): array
    {
        if (!function_exists('module_is_active') || !module_is_active('CreditPay')) {
            // ماژول غیرفعال، به‌صورت نقدی محاسبه کن
            return [
                'payment_type'          => self::PAYMENT_CASH,
                'refund_amount'         => $itemPaidAmount,
                'wallet_refund_amount'  => $itemPaidAmount,
                'credit_restore_amount' => 0,
            ];
        }

        try {
            $creditOrder = \Modules\CreditPay\Models\CreditOrder::where('order_id', $order->id)
                ->with('installments')
                ->first();

            if (!$creditOrder) {
                // اگه CreditOrder پیدا نشد، fallback به نقدی
                Log::warning('ReturnRequest: CreditOrder not found, fallback to cash refund', [
                    'order_id' => $order->id,
                ]);
                return [
                    'payment_type'          => self::PAYMENT_CASH,
                    'refund_amount'         => $itemPaidAmount,
                    'wallet_refund_amount'  => $itemPaidAmount,
                    'credit_restore_amount' => 0,
                ];
            }

            // محاسبه سهم آیتم از کل اعتباری
            $creditEligibleTotal = self::calculateCreditEligibleTotal($order);
            if ($creditEligibleTotal <= 0) {
                $creditEligibleTotal = (int) $creditOrder->original_amount;
            }
            if ($creditEligibleTotal <= 0) {
                return [
                    'payment_type'          => self::PAYMENT_CASH,
                    'refund_amount'         => $itemPaidAmount,
                    'wallet_refund_amount'  => $itemPaidAmount,
                    'credit_restore_amount' => 0,
                ];
            }

            // سهم این آیتم از کل اعتباری
            $itemShare = $itemPaidAmount / $creditEligibleTotal;

            // اعتبار استفاده‌شده برای این آیتم
            $creditUsedForItem = (int) round($creditOrder->credit_used * $itemShare);

            // مبلغ پرداخت‌شده توسط کاربر (به‌جز هزینه ارسال):
            // قسط اول (اگه پرداخت شده) + اقساط پرداخت‌شده (به‌جز قسط اول)
            $firstInstallmentPaid = $creditOrder->first_installment_paid
                ? (int) $creditOrder->first_installment_amount
                : 0;

            $paidInstallmentsAmount = (int) $creditOrder->installments()
                ->where('status', 'paid')
                ->where('installment_number', '!=', 1)
                ->sum('amount');

            $totalPaidByUser = $firstInstallmentPaid + $paidInstallmentsAmount;

            // سهم این آیتم از مبلغ پرداخت‌شده
            $walletRefundAmount = (int) round($totalPaidByUser * $itemShare);

            // اعتبار قابل بازگشت
            $creditRestoreAmount = $creditUsedForItem;

            // مبلغ کل بازگشتی
            $totalRefundAmount = $walletRefundAmount + $creditRestoreAmount;

            Log::info('ReturnRequest: Credit refund calculated', [
                'order_id'              => $order->id,
                'order_item_id'         => $item->id,
                'item_paid_amount'      => $itemPaidAmount,
                'credit_eligible_total' => $creditEligibleTotal,
                'item_share'            => round($itemShare * 100, 2) . '%',
                'credit_used_for_item'  => $creditUsedForItem,
                'first_installment_paid'=> $firstInstallmentPaid,
                'paid_installments'     => $paidInstallmentsAmount,
                'total_paid_by_user'    => $totalPaidByUser,
                'wallet_refund'         => $walletRefundAmount,
                'credit_restore'         => $creditRestoreAmount,
                'total_refund'          => $totalRefundAmount,
            ]);

            return [
                'payment_type'          => self::PAYMENT_CREDIT,
                'refund_amount'         => $totalRefundAmount,
                'wallet_refund_amount'  => $walletRefundAmount,
                'credit_restore_amount' => $creditRestoreAmount,
            ];

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error calculating credit refund', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return [
                'payment_type'          => self::PAYMENT_CASH,
                'refund_amount'         => $itemPaidAmount,
                'wallet_refund_amount'  => $itemPaidAmount,
                'credit_restore_amount' => 0,
            ];
        }
    }

    /**
     * محاسبه مرجوعی برای سفارش اقساطی (InstallmentPayment)
     */
    private function calculateInstallmentRefund(OrderItem $item, Order $order, int $itemPaidAmount): array
    {
        if (!function_exists('module_is_active') || !module_is_active('InstallmentPayment')) {
            return [
                'payment_type'          => self::PAYMENT_CASH,
                'refund_amount'         => $itemPaidAmount,
                'wallet_refund_amount'  => $itemPaidAmount,
                'credit_restore_amount' => 0,
            ];
        }

        try {
            $installmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $order->id)
                ->with('payments')
                ->first();

            if (!$installmentPlan) {
                Log::warning('ReturnRequest: InstallmentPlan not found, fallback to cash refund', [
                    'order_id' => $order->id,
                ]);
                return [
                    'payment_type'          => self::PAYMENT_CASH,
                    'refund_amount'         => $itemPaidAmount,
                    'wallet_refund_amount'  => $itemPaidAmount,
                    'credit_restore_amount' => 0,
                ];
            }

            // مبلغ کل طرح اقساط (بدون هزینه ارسال)
            $installmentTotalAmount = (int) $installmentPlan->total_amount;
            if ($installmentTotalAmount <= 0) {
                return [
                    'payment_type'          => self::PAYMENT_CASH,
                    'refund_amount'         => $itemPaidAmount,
                    'wallet_refund_amount'  => $itemPaidAmount,
                    'credit_restore_amount' => 0,
                ];
            }

            // سهم آیتم از کل طرح
            $itemShare = $itemPaidAmount / $installmentTotalAmount;

            // مبلغ پرداخت‌شده توسط کاربر:
            // پیش‌پرداخت + اقساط پرداخت‌شده
            $downPayment = (int) $installmentPlan->down_payment;

            $paidInstallmentsAmount = (int) $installmentPlan->payments()
                ->where('status', 'paid')
                ->sum('amount');

            $totalPaidByUser = $downPayment + $paidInstallmentsAmount;

            // سهم این آیتم از مبلغ پرداخت‌شده
            $walletRefundAmount = (int) round($totalPaidByUser * $itemShare);

            // برای اقساطی، بازگشت اعتبار نداریم (مثل اعتباری)
            $creditRestoreAmount = 0;

            Log::info('ReturnRequest: Installment refund calculated', [
                'order_id'              => $order->id,
                'order_item_id'         => $item->id,
                'item_paid_amount'      => $itemPaidAmount,
                'installment_total'     => $installmentTotalAmount,
                'item_share'            => round($itemShare * 100, 2) . '%',
                'down_payment'          => $downPayment,
                'paid_installments'     => $paidInstallmentsAmount,
                'total_paid_by_user'    => $totalPaidByUser,
                'wallet_refund'         => $walletRefundAmount,
            ]);

            return [
                'payment_type'          => self::PAYMENT_INSTALLMENT,
                'refund_amount'         => $walletRefundAmount,
                'wallet_refund_amount'  => $walletRefundAmount,
                'credit_restore_amount' => 0,
            ];

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error calculating installment refund', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return [
                'payment_type'          => self::PAYMENT_CASH,
                'refund_amount'         => $itemPaidAmount,
                'wallet_refund_amount'  => $itemPaidAmount,
                'credit_restore_amount' => 0,
            ];
        }
    }

    /**
     * تشخیص نوع پرداخت سفارش
     */
    public static function detectPaymentType(Order $order): string
    {
        // بررسی اعتباری
        if (function_exists('module_is_active') && module_is_active('CreditPay')) {
            $hasCreditOrder = \Modules\CreditPay\Models\CreditOrder::where('order_id', $order->id)->exists();
            if ($hasCreditOrder) {
                return self::PAYMENT_CREDIT;
            }
        }

        // بررسی اقساطی
        if (function_exists('module_is_active') && module_is_active('InstallmentPayment')) {
            $hasInstallmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $order->id)->exists();
            if ($hasInstallmentPlan) {
                return self::PAYMENT_INSTALLMENT;
            }
        }

        return self::PAYMENT_CASH;
    }

    /**
     * محاسبه مبلغ قابل خرید اعتباری برای یک سفارش
     */
    private static function calculateCreditEligibleTotal(Order $order): int
    {
        $total = 0;
        foreach ($order->items as $item) {
            // آیتم‌های فروشنده در خرید اعتباری نیستن
            if (!empty($item->seller_id)) {
                continue;
            }
            $total += (int) ($item->price * $item->quantity);
        }
        return $total;
    }

    /**
     * محاسبه مبلغ برگشتی (همراه با breakdown)
     * برای backward compatibility با کدهای قدیمی
     */
    public function calculateRefundAmount(): int
    {
        $breakdown = $this->calculateRefundBreakdown();
        return $breakdown['refund_amount'];
    }

    /**
     * آیا هنوز در مهلت مرجوعی است؟
     */
    public static function isWithinReturnPeriod($orderItemId): bool
    {
        $item = OrderItem::with('order')->find($orderItemId);
        if (!$item || !$item->order || $item->order->status !== 'paid') return false;
        if ($item->shipping_status !== 'delivered') return false;

        $days = (int) option('return_days_limit', 7);

        $deliveredAt = $item->delivery_date ?? $item->order->paid_at;
        if (!$deliveredAt) return false;

        $deliveredAt = Carbon::parse($deliveredAt);
        $expiryDate = $deliveredAt->copy()->addDays($days);

        return now()->lessThanOrEqualTo($expiryDate);
    }

    /**
     * ساخت درخواست با مقادیر دقیق از order_item
     * این متد به‌صورت خودکار نوع پرداخت رو تشخیص می‌ده و مبالغ رو محاسبه می‌کنه
     */
    public static function createFromOrderItem(OrderItem $item, int $reasonId, ?string $description): self
    {
        $itemPrice = (int) $item->price;
        $quantity = (int) $item->quantity;
        $totalAmount = $itemPrice * $quantity;
        $discountAmount = ($item->real_price - $item->price) * $quantity;

        // محاسبه breakdown بر اساس نوع پرداخت
        $order = $item->order;
        $paymentType = self::detectPaymentType($order);

        // ساخت رکورد اولیه
        $returnRequest = self::create([
            'order_id'            => $item->order_id,
            'order_item_id'       => $item->id,
            'user_id'             => $item->order->user_id,
            'product_id'          => $item->product_id,
            'seller_id'           => $item->seller_id,
            'return_reason_id'    => $reasonId,
            'description'         => $description,
            'status'              => self::STATUS_PENDING,
            'item_price'         => $itemPrice,
            'quantity'            => $quantity,
            'total_item_amount'  => $totalAmount,
            'discount_amount'    => $discountAmount,
            // مقادیر اولیه - در ادامه آپدیت می‌شن
            'refund_amount'      => $totalAmount,
            'payment_type'       => $paymentType,
            'wallet_refund_amount' => 0,
            'credit_restore_amount' => 0,
            'paid_to_wallet'     => false,
            'credit_restored'    => false,
        ]);

        // محاسبه breakdown دقیق
        $breakdown = $returnRequest->calculateRefundBreakdown();

        $returnRequest->update([
            'payment_type'          => $breakdown['payment_type'],
            'refund_amount'         => $breakdown['refund_amount'],
            'wallet_refund_amount' => $breakdown['wallet_refund_amount'],
            'credit_restore_amount' => $breakdown['credit_restore_amount'],
        ]);

        return $returnRequest->fresh();
    }

    // ====================================================================
    //  لغو سفارش اعتباری/اقساطی
    // ====================================================================
    //
    //  وقتی مرجوعی تکمیل می‌شه (status = completed)، اگه سفارش اعتباری یا اقساطی
    //  باشه، باید طرح اعتباری/اقساطی هم لغو بشه:
    //    - CreditOrder به وضعیت refunded
    //    - InstallmentPlan به وضعیت cancelled
    //    - اقساط پرداخت‌نشده باطل می‌شن (وضعیت defaulted)
    //
    //  این کار جلوگیری می‌کنه از اینکه کاربر اقساط باقی‌مانده رو پرداخت کنه
    //  برای محصولی که مرجوع شده.
    // ====================================================================

    /**
     * لغو سفارش اعتباری (CreditPay)
     *
     * @return bool
     */
    public function cancelCreditOrder(): bool
    {
        if (!$this->isCreditPayment()) {
            return false;
        }

        if (!function_exists('module_is_active') || !module_is_active('CreditPay')) {
            Log::warning('ReturnRequest: CreditPay module not active, cannot cancel credit order', [
                'return_request_id' => $this->id,
            ]);
            return false;
        }

        try {
            $creditOrder = \Modules\CreditPay\Models\CreditOrder::where('order_id', $this->order_id)->first();
            if (!$creditOrder) {
                Log::warning('ReturnRequest: CreditOrder not found for cancellation', [
                    'order_id' => $this->order_id,
                ]);
                return false;
            }

            // اگه قبلاً لغو شده، چیزی نکن
            if (in_array($creditOrder->status, [
                \Modules\CreditPay\Models\CreditOrder::STATUS_CANCELLED,
                \Modules\CreditPay\Models\CreditOrder::STATUS_REFUNDED,
            ])) {
                Log::info('ReturnRequest: CreditOrder already cancelled/refunded', [
                    'credit_order_id' => $creditOrder->id,
                    'status'          => $creditOrder->status,
                ]);
                return true;
            }

            $reason = sprintf(
                'مرجوعی #%d - سفارش #%d - محصول: %s',
                $this->id,
                $this->order_id,
                $this->orderItem?->title ?? '—'
            );

            // ۱. باطل کردن همه‌ی اقساط پرداخت‌نشده
            $creditOrder->installments()
                ->whereIn('status', [
                    \Modules\CreditPay\Models\CreditInstallment::STATUS_PENDING,
                    \Modules\CreditPay\Models\CreditInstallment::STATUS_OVERDUE,
                ])
                ->update([
                    'status' => \Modules\CreditPay\Models\CreditInstallment::STATUS_DEFAULTED,
                    'notes'  => 'باطل شده به دلیل مرجوعی: ' . $reason,
                ]);

            // ۲. به‌روزرسانی وضعیت CreditOrder به refunded
            $creditOrder->update([
                'status' => \Modules\CreditPay\Models\CreditOrder::STATUS_REFUNDED,
            ]);

            Log::info('ReturnRequest: CreditOrder cancelled successfully', [
                'return_request_id' => $this->id,
                'credit_order_id'   => $creditOrder->id,
                'order_id'          => $this->order_id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error cancelling credit order', [
                'return_request_id' => $this->id,
                'order_id'          => $this->order_id,
                'error'             => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * لغو طرح اقساطی (InstallmentPayment)
     *
     * @return bool
     */
    public function cancelInstallmentPlan(): bool
    {
        if (!$this->isInstallmentPayment()) {
            return false;
        }

        if (!function_exists('module_is_active') || !module_is_active('InstallmentPayment')) {
            Log::warning('ReturnRequest: InstallmentPayment module not active, cannot cancel plan', [
                'return_request_id' => $this->id,
            ]);
            return false;
        }

        try {
            $installmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $this->order_id)->first();
            if (!$installmentPlan) {
                Log::warning('ReturnRequest: InstallmentPlan not found for cancellation', [
                    'order_id' => $this->order_id,
                ]);
                return false;
            }

            // اگه قبلاً لغو شده، چیزی نکن
            if ($installmentPlan->status === \Modules\InstallmentPayment\Models\InstallmentPlan::STATUS_CANCELLED) {
                Log::info('ReturnRequest: InstallmentPlan already cancelled', [
                    'installment_plan_id' => $installmentPlan->id,
                ]);
                return true;
            }

            $reason = sprintf(
                'مرجوعی #%d - سفارش #%d - محصول: %s',
                $this->id,
                $this->order_id,
                $this->orderItem?->title ?? '—'
            );

            // ۱. باطل کردن همه‌ی اقساط پرداخت‌نشده
            $installmentPlan->payments()
                ->whereIn('status', [
                    \Modules\InstallmentPayment\Models\InstallmentPayment::STATUS_PENDING,
                    \Modules\InstallmentPayment\Models\InstallmentPayment::STATUS_OVERDUE,
                ])
                ->update([
                    'status' => \Modules\InstallmentPayment\Models\InstallmentPayment::STATUS_DEFAULTED,
                    'notes'  => 'باطل شده به دلیل مرجوعی: ' . $reason,
                ]);

            // ۲. به‌روزرسانی وضعیت InstallmentPlan به cancelled
            $installmentPlan->update([
                'status'  => \Modules\InstallmentPayment\Models\InstallmentPlan::STATUS_CANCELLED,
                'end_date' => now(),
                'notes'   => 'لغو به دلیل مرجوعی: ' . $reason,
            ]);

            Log::info('ReturnRequest: InstallmentPlan cancelled successfully', [
                'return_request_id'  => $this->id,
                'installment_plan_id' => $installmentPlan->id,
                'order_id'           => $this->order_id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('ReturnRequest: Error cancelling installment plan', [
                'return_request_id' => $this->id,
                'order_id'          => $this->order_id,
                'error'             => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * لغو سفارش اعتباری/اقساطی (با تشخیص خودکار نوع)
     *
     * @return bool
     */
    public function cancelPaymentPlan(): bool
    {
        if ($this->isCreditPayment()) {
            return $this->cancelCreditOrder();
        } elseif ($this->isInstallmentPayment()) {
            return $this->cancelInstallmentPlan();
        }
        // برای سفارشات نقدی چیزی برای لغو نیست
        return true;
    }

    /**
     * آیا طرح پرداخت لغو شده؟
     */
    public function isPaymentPlanCancelled(): bool
    {
        if ($this->isCreditPayment() && function_exists('module_is_active') && module_is_active('CreditPay')) {
            $creditOrder = \Modules\CreditPay\Models\CreditOrder::where('order_id', $this->order_id)->first();
            if ($creditOrder) {
                return in_array($creditOrder->status, [
                    \Modules\CreditPay\Models\CreditOrder::STATUS_CANCELLED,
                    \Modules\CreditPay\Models\CreditOrder::STATUS_REFUNDED,
                ]);
            }
        }

        if ($this->isInstallmentPayment() && function_exists('module_is_active') && module_is_active('InstallmentPayment')) {
            $installmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $this->order_id)->first();
            if ($installmentPlan) {
                return $installmentPlan->status === \Modules\InstallmentPayment\Models\InstallmentPlan::STATUS_CANCELLED;
            }
        }

        return false;
    }
}
