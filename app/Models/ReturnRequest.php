<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    protected $table = 'return_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'refund_to_wallet' => 'boolean',
        'approved_at'      => 'datetime',
        'received_at'      => 'datetime',
        'completed_at'     => 'datetime',
        'rejected_at'      => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    // وضعیت‌ها
    public const STATUS_PENDING   = 'pending';    // در حال بررسی
    public const STATUS_APPROVED  = 'approved';   // تایید اولیه
    public const STATUS_RECEIVED  = 'received';  // محصول دریافت شد
    public const STATUS_COMPLETED = 'completed'; // تایید نهایی
    public const STATUS_REJECTED  = 'rejected';  // رد شده
    public const STATUS_CANCELLED = 'cancelled'; // لغو توسط کاربر

    public static function statusLabels(): array
    {
        return [
            'pending'   => ['label' => 'در حال بررسی', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => 'fa-clock'],
            'approved'  => ['label' => 'تایید اولیه', 'color' => '#3b82f6', 'bg' => '#dbeafe', 'icon' => 'fa-check-circle'],
            'received'  => ['label' => 'محصول دریافت شد', 'color' => '#8b5cf6', 'bg' => '#ede9fe', 'icon' => 'fa-box'],
            'completed' => ['label' => 'تایید نهایی', 'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-check-double'],
            'rejected'  => ['label' => 'رد شده', 'color' => '#ef4444', 'bg' => '#fee2e2', 'icon' => 'fa-times-circle'],
            'cancelled' => ['label' => 'لغو توسط کاربر', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'icon' => 'fa-ban'],
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReturnImage::class);
    }

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isReceived(): bool { return $this->status === self::STATUS_RECEIVED; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }
    public function canBeCancelled(): bool { return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]); }
    public function canBeRefunded(): bool { return $this->status === self::STATUS_RECEIVED && !$this->refund_to_wallet; }

    /**
     * محاسبه مبلغ قابل برگشت
     */
    public function calculateRefundAmount(): int
    {
        $item = $this->orderItem;
        if (!$item) return 0;
        // فقط مبلغ محصول (بدون هزینه ارسال)
        return (int) ($item->price * $item->quantity);
    }

    /**
     * آیا هنوز در مهلت مرجوعی است؟
     */
    public static function isWithinReturnPeriod($orderItemId): bool
    {
        $item = OrderItem::with('order')->find($orderItemId);
        if (!$item || !$item->order || $item->order->status !== 'paid') return false;
        if ($item->shipping_status !== 'delivered') return false;

        $days = (int) (\App\Models\Setting::where('key', 'return_days_limit')->first()?->value ?? 7);
        $deliveredAt = $item->delivered_at ?? $item->order->paid_at;

        if (!$deliveredAt) return false;

        return $deliveredAt->addDays($days)->isFuture();
    }
}
