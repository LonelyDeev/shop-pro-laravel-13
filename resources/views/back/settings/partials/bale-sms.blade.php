@php
    $balePatterns = [
        'auth' => [
            'title'    => 'ثبت‌نام، ورود و احراز هویت',
            'icon'     => 'icon-user-check',
            'patterns' => [
                'user_verify_pattern_code_bale' => [
                    'label'  => 'کد تایید / ورود',
                    'sample' => "کد تایید: %code%\n او پی شاپ",
                ],
                'user_register_pattern_code_bale' => [
                    'label'  => 'خوش‌آمدگویی کاربر',
                    'sample' => "%fullname% عزیز خوش آمدید.\n او پی شاپ",
                ],
                'seller_register_pattern_code_bale' => [
                    'label'  => 'خوش‌آمدگویی فروشنده',
                    'sample' => "%fullname% فروشنده عزیز خوش آمدید.\n او پی شاپ",
                ],
            ],
        ],
        'order' => [
            'title'    => 'سفارش‌ها',
            'icon'     => 'icon-shopping-cart',
            'patterns' => [
                'order_paid_pattern_code_bale' => [
                    'label'  => 'پرداخت سفارش (اطلاع‌رسانی به مدیر)',
                    'sample' => "سفارش جدید با شماره سفارش %order_id% ثبت و پرداخت شد.\n او پی شاپ",
                ],
                'seller_order_paid_pattern_code_bale' => [
                    'label'  => 'پرداخت سفارش (اطلاع‌رسانی به فروشنده)',
                    'sample' => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\n او پی شاپ",
                ],
                'user_order_paid_pattern_code_bale' => [
                    'label'  => 'پرداخت سفارش (اطلاع‌رسانی به کاربر)',
                    'sample' => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\n او پی شاپ",
                ],
                'order_cancelled_pattern_code_bale' => [
                    'label'  => 'لغو سفارش (اطلاع‌رسانی به مدیر)',
                    'sample' => "سفارش شماره %order_id% لغو شد.\n او پی شاپ",
                ],
                'seller_order_cancelled_pattern_code_bale' => [
                    'label'  => 'لغو سفارش (اطلاع‌رسانی به فروشنده)',
                    'sample' => "سفارش شماره %order_id% لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.\n او پی شاپ",
                ],
                'user_order_cancelled_pattern_code_bale' => [
                    'label'  => 'لغو سفارش (اطلاع‌رسانی به کاربر)',
                    'sample' => "سفارش شماره %order_id% به دلیل %reason% لغو شد. مبلغ %refund_amount% تومان به کیف پول شما برگشت داده شد.\n او پی شاپ",
                ],
            ],
        ],
        'wallet' => [
            'title'    => 'کیف پول',
            'icon'     => 'icon-credit-card',
            'patterns' => [
                'wallet_increase_pattern_code_bale' => [
                    'label'  => 'افزایش موجودی کیف پول',
                    'sample' => "مبلغ %amount% تومان به اعتبار کیف پول شما اضافه شد.\n او پی شاپ",
                ],
                'wallet_decrease_pattern_code_bale' => [
                    'label'  => 'کاهش موجودی کیف پول',
                    'sample' => "مبلغ %amount% تومان از اعتبار کیف پول شما کسر شد.\n او پی شاپ",
                ],
                'wallet_refund_pattern_code_bale' => [
                    'label'  => 'برگشت وجه به کیف پول',
                    'sample' => "مبلغ %amount% تومان بابت لغو سفارش %order_id% به کیف پول شما برگشت داده شد.\n او پی شاپ",
                ],
            ],
        ],
        'misc' => [
            'title'    => 'پیام‌های مناسبتی و گروهی',
            'icon'     => 'icon-gift',
            'patterns' => [
                'happy_birthday_pattern_code_bale' => [
                    'label'  => 'تبریک تولد',
                    'sample' => "%fullname% عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید ... تولدتان مبارک\n او پی شاپ",
                ],
                'user_message_pattern_code_bale' => [
                    'label'  => 'پیام گروهی به کاربران',
                    'sample' => "%message%",
                ],
            ],
        ],
    ];
@endphp

<div class="sms-provider-settings" id="bale-settings" data-provider="bale" style="display:none;">
    <div class="card">
        <div class="card-body">
            <div class="sms-section-title">
                <i class="feather icon-message-circle"></i>
                <span>تنظیمات ربات بله (Bale Messenger)</span>
            </div>

            <div class="bale-intro-alert mb-2">
                <i class="feather icon-info"></i>
                <div>
                    <strong>راه‌اندازی ربات بله:</strong>
                    <ol class="mb-0 mt-50">
                        <li>در اپلیکیشن بله، به <code>@BotFather</code> پیام دهید و ربات جدید بسازید تا توکن دریافت کنید.</li>
                        <li>توکن دریافتی را در فیلد زیر وارد کنید.</li>
                        <li>آدرس وب‌هوک را با دکمه «تنظیم وب‌هوک» روی سرور بله ثبت کنید.</li>
                        <li>کاربران باید در سایت، دکمه «اتصال به بله» را بزنند و کد ۶ رقمی را به ربات ارسال کنند تا <code>chat_id</code> آن‌ها ذخیره شود.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>توکن ربات بله (Bot Token)</label>
                    <div class="input-group">
                        <input type="text" name="BALE_BOT_TOKEN" class="form-control ltr" value="{{ option('BALE_BOT_TOKEN') }}" placeholder="123456789:ABCdef...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-test-bale" id="btn-test-bale">
                                <i class="feather icon-wifi"></i> تست اتصال
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-50">توکن را از <code>@BotFather</code> در اپلیکیشن بله دریافت کنید.</small>
                </div>
                <div class="col-md-6">
                    <label>Chat ID ادمین (برای اطلاع‌رسانی‌های مدیریتی)</label>
                    <input type="text" name="bale_admin_chat_id" class="form-control ltr" value="{{ option('bale_admin_chat_id') }}" placeholder="مثلاً 1234567890">
                    <small class="text-muted d-block mt-50">برای دریافت Chat ID خود، ربات را استارت کرده و یک پیام ارسال کنید، سپس از دکمه «دریافت Chat ID» استفاده کنید.</small>
                </div>
            </div>

            <div class="row mt-1">
                <div class="col-md-8">
                    <label>آدرس وب‌هوک (Webhook URL)</label>
                    <div class="input-group">
                        <input type="text" id="bale-webhook-url" class="form-control ltr" readonly value="{{ route('bale.webhook') }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-copy-webhook">
                                <i class="feather icon-copy"></i> کپی
                            </button>
                            <button type="button" class="btn btn-primary btn-set-webhook" id="btn-set-webhook">
                                <i class="feather icon-link"></i> تنظیم وب‌هوک
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-50">این آدرس را در سرور بله ثبت کنید تا پیام‌های کاربران به سایت شما ارسال شود.</small>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-delete-webhook w-100" id="btn-delete-webhook">
                        <i class="feather icon-trash-2"></i> حذف وب‌هوک
                    </button>
                </div>
            </div>

            <div id="bale-test-result" class="mt-1" style="display:none;"></div>
        </div>
    </div>

    {{-- ================= پترن‌های پیام بله ================= --}}
    @foreach ($balePatterns as $groupKey => $group)
        <div class="card">
            <div class="card-body">
                <div class="sms-section-title">
                    <i class="feather {{ $group['icon'] }}"></i>
                    <span>کدهای پترن بله - {{ $group['title'] }}</span>
                </div>
                <p class="text-muted mb-1" style="font-size:12.5px;">
                    کد پترن پیش‌فرض سیستم را می‌توانید تغییر دهید. اگر خالی بگذارید، از کد پیش‌فرض استفاده می‌شود.
                    متن نمونه برای کپی در بخش زیر هر فیلد قرار دارد.
                </p>

                <div class="row">
                    @foreach ($group['patterns'] as $optionKey => $pattern)
                        <div class="col-md-6 mb-1">
                            <div class="sms-pattern-card">
                                <div class="d-flex justify-content-between align-items-center mb-50">
                                    <label class="sms-pattern-label mb-0">
                                        <i class="feather icon-hash"></i>
                                        {{ $pattern['label'] }}
                                    </label>
                                </div>
                                <input type="text" name="{{ $optionKey }}" class="form-control ltr mb-50"
                                       value="{{ option($optionKey) }}"
                                       placeholder="کد پیش‌فرض: {{ explode('_pattern_code_bale', $optionKey)[0] }}">

                                <div class="sms-sample-label">
                                    <small class="text-muted">متن نمونه پیام:</small>
                                    <button type="button" class="btn-copy-sample" title="کپی متن">
                                        <i class="feather icon-copy"></i>
                                    </button>
                                </div>
                                <textarea class="form-control sms-sample-text" readonly rows="3">{{ $pattern['sample'] }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
