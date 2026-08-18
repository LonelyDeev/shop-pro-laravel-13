@php
    /**
     * ============================================================================
     *  Partial نمایش وضعیت مرجوعی در صفحه نمایش آیتم سفارش (سمت ادمین)
     * ============================================================================
     *
     *  نحوه استفاده:
     *  @include('back.returns.partials.order_item_return_info', ['orderItem' => $orderItem])
     *
     *  این partial بررسی می‌کنه که آیا آیتم سفارش مرجوع شده یا نه و اگه مرجوع
     *  شده باشه، بنر وضعیت مرجوعی رو نمایش می‌ده.
     *
     *  اگه سفارش اعتباری یا اقساطی باشه و طرح لغو شده باشه، اون رو هم نشون می‌ده.
     */
@endphp

@if($orderItem->refunded || $orderItem->return_status !== 'none')
@php
    // پیدا کردن درخواست مرجوعی مرتبط
    $returnRequest = \App\Models\ReturnRequest::where('order_item_id', $orderItem->id)
        ->whereNotIn('status', ['cancelled'])
        ->latest()
        ->first();

    $statusInfo = null;
    $ptInfo = null;
    if ($returnRequest) {
        $statusInfo = \App\Models\ReturnRequest::statusLabels()[$returnRequest->status] ?? null;
        $ptInfo = \App\Models\ReturnRequest::paymentTypeLabels()[$returnRequest->payment_type] ?? null;
    }
@endphp

<div class="card mb-3" style="border: 2px solid {{ $statusInfo ? $statusInfo['color'] : '#f59e0b' }}; border-radius: 12px; overflow: hidden;">

    {{-- هدر بنر --}}
    <div style="background: {{ $statusInfo ? $statusInfo['bg'] : '#fffbeb' }}; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">

        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: {{ $statusInfo ? $statusInfo['color'] : '#f59e0b' }}22; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: {{ $statusInfo ? $statusInfo['color'] : '#f59e0b' }};">
                <i class="fas {{ $statusInfo ? $statusInfo['icon'] : 'fa-undo-alt' }}"></i>
            </div>
            <div>
                <h5 style="margin: 0; font-size: 1rem; font-weight: 700; color: {{ $statusInfo ? $statusInfo['color'] : '#92400e' }};">
                    مرجوعی محصول
                </h5>
                <div style="font-size: 0.78rem; margin-top: 10px;">
                    @if($returnRequest)
                        <span style="background: {{ $statusInfo['color'] }}; color: #fff; padding: 2px 10px; border-radius: 999px; font-weight: 600;">
                            {{ $statusInfo['label'] }}
                        </span>
                        @if($ptInfo)
                        <span style="background: {{ $ptInfo['bg'] }}; color: {{ $ptInfo['color'] }}; padding: 2px 10px; border-radius: 999px; font-weight: 600; margin-right: 6px;">
                            <i class="fas {{ $ptInfo['icon'] }}"></i> {{ $ptInfo['label'] }}
                        </span>
                        @endif
                    @else
                        <span style="background: #f59e0b; color: #fff; padding: 2px 10px; border-radius: 999px; font-weight: 600;">
                            مرجوع شده
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            @if($returnRequest)
                <a href="{{ route('admin.returns.show', $returnRequest) }}" target="_blank" class="btn btn-sm btn-light">
                    <i class="fas fa-external-link-alt"></i> مشاهده درخواست مرجوعی
                </a>
            @endif
        </div>
    </div>

    {{-- بدنه بنر --}}
    @if($returnRequest)
    <div style="padding: 16px 20px; background: #fff;">

        {{-- خلاصه مبالغ --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-bottom: 12px;">

            {{-- مبلغ کل برگشتی --}}
            <div style="background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px;">
                <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 4px;">مبلغ کل برگشتی</div>
                <div style="font-size: 0.95rem; font-weight: 800; color: #10b981;">{{ number_format($returnRequest->refund_amount) }} ت</div>
            </div>

            {{-- بازگشت به کیف پول --}}
            @if($returnRequest->wallet_refund_amount > 0)
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px;">
                <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 4px;">
                    بازگشت به کیف پول
                    @if($returnRequest->paid_to_wallet)
                        <i class="fas fa-check-circle text-success" style="font-size: 0.7rem;"></i>
                    @endif
                </div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #059669;">{{ number_format($returnRequest->wallet_refund_amount) }} ت</div>
            </div>
            @endif

            {{-- بازگشت به اعتبار --}}
            @if($returnRequest->credit_restore_amount > 0)
            <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 12px;">
                <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 4px;">
                    بازگشت به اعتبار
                    @if($returnRequest->credit_restored)
                        <i class="fas fa-check-circle text-primary" style="font-size: 0.7rem;"></i>
                    @endif
                </div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #4338ca;">{{ number_format($returnRequest->credit_restore_amount) }} ت</div>
            </div>
            @endif

            {{-- تاریخ تکمیل --}}
            @if($returnRequest->completed_at)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
                <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 4px;">تاریخ تکمیل</div>
                <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">{{ jdate($returnRequest->completed_at)->format('Y/m/d H:i') }}</div>
            </div>
            @endif
        </div>

        {{-- هشدار لغو طرح اعتباری/اقساطی --}}
        @if($returnRequest->isCreditPayment() || $returnRequest->isInstallmentPayment())
            @php $planCancelled = $returnRequest->isPaymentPlanCancelled(); @endphp
            <div style="background: {{ $planCancelled ? '#fef2f2' : '#fffbeb' }}; border: 1px solid {{ $planCancelled ? '#fecaca' : '#fde68a' }}; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; font-size: 0.82rem;">
                <div style="display: flex; gap: 8px; align-items: flex-start;">
                    <i class="fas {{ $planCancelled ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }}" style="margin-top: 2px;"></i>
                    <div>
                        <strong style="color: {{ $planCancelled ? '#065f46' : '#92400e' }};">
                            @if($planCancelled)
                                طرح {{ $returnRequest->paymentTypeLabel() }} لغو شد
                            @else
                                طرح {{ $returnRequest->paymentTypeLabel() }} هنوز فعال است
                            @endif
                        </strong>
                        <div style="color: {{ $planCancelled ? '#065f46' : '#92400e' }}; margin-top: 4px;">
                            @if($planCancelled)
                                @if($returnRequest->isCreditPayment())
                                    سفارش اعتباری به وضعیت «مرجوع شده» تغییر یافت و تمام اقساط پرداخت‌نشده باطل شدند.
                                @else
                                    طرح اقساطی به وضعیت «لغو شده» تغییر یافت و تمام اقساط پرداخت‌نشده باطل شدند.
                                @endif
                            @else
                                این سفارش از نوع {{ $returnRequest->paymentTypeLabel() }} است. با تکمیل مرجوعی، طرح پرداخت لغو خواهد شد.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- دلیل مرجوعی --}}
        @if($returnRequest->reason)
        <div style="font-size: 0.82rem; color: #475569; margin-bottom: 8px;">
            <strong>دلیل:</strong> {{ $returnRequest->reason->title }}
        </div>
        @endif

        {{-- توضیحات کاربر --}}
        @if($returnRequest->description)
        <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; font-size: 0.82rem;">
            <div style="color: #64748b; margin-bottom: 4px; font-weight: 600;">
                <i class="fas fa-comment-dots"></i> توضیحات کاربر:
            </div>
            <div style="color: #334155;">{{ $returnRequest->description }}</div>
        </div>
        @endif

        {{-- یادداشت ادمین --}}
        @if($returnRequest->admin_notes)
        <div style="background: #eef2ff; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; font-size: 0.82rem;">
            <div style="color: #4338ca; margin-bottom: 4px; font-weight: 600;">
                <i class="fas fa-user-shield"></i> یادداشت ادمین:
            </div>
            <div style="color: #3730a3;">{{ $returnRequest->admin_notes }}</div>
        </div>
        @endif

        {{-- نتیجه بررسی --}}
        @if($returnRequest->inspection_result)
        <div style="font-size: 0.82rem; color: #475569; margin-bottom: 8px;">
            <strong>نتیجه بررسی محصول:</strong> {{ $returnRequest->inspection_result }}
        </div>
        @endif

        {{-- نکته هزینه ارسال --}}
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 12px; font-size: 0.78rem; color: #92400e; margin-top: 8px;">
            <i class="fas fa-info-circle"></i>
            <strong>نکته:</strong> هزینه ارسال جزو مبلغ بازگشتی محاسبه نشده است.
        </div>
    </div>
    @else
    <div style="padding: 16px 20px; background: #fff; font-size: 0.85rem; color: #475569;">
        <i class="fas fa-check-circle text-warning"></i>
        این آیتم به‌عنوان مرجوع‌شده علامت‌گذاری شده اما درخواست مرجوعی مرتبط یافت نشد.
        <br>
        <small class="text-muted">مبلغ بازگشتی: {{ number_format($orderItem->refunded_amount ?? 0) }} تومان</small>
    </div>
    @endif
</div>
@endif
