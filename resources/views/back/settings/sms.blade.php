@extends('back.layouts.master')
@push('styles')
    <style>
        .sms-settings-wrap {
            --sms-primary: #7367f0;
            --sms-success: #28c76f;
            --sms-warning: #ff9f43;
            --sms-info: #00cfe8;
            --sms-danger: #ea5455;
            --sms-birthday: #e83e8c;
        }

        /* --- کارت معرفی --- */
        .sms-intro-card {
            background: linear-gradient(135deg, rgba(115, 103, 240, .09), rgba(115, 103, 240, .02));
            border: 1px solid rgba(115, 103, 240, .15);
        }
        .sms-intro-icon {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border-radius: 14px;
            background: var(--sms-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-left: 16px;
        }

        /* --- عنوان هر بخش --- */
        .sms-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
            color: #5e5873;
            margin-bottom: 1rem;
            padding-bottom: .75rem;
            border-bottom: 1px dashed #e4e4e4;
        }
        .sms-section-title i { color: var(--sms-primary); }

        /* --- گرید کارت‌های سوییچی --- */
        .sms-toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .sms-toggle-grid-single {
            grid-template-columns: minmax(260px, 480px);
        }
        .sms-toggle-card {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #ebe9f1;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fff;
            transition: all .2s ease;
            border-right: 3px solid var(--sms-primary);
        }
        .sms-toggle-card:hover {
            box-shadow: 0 4px 14px rgba(115, 103, 240, .12);
            transform: translateY(-1px);
        }
        .sms-toggle-card.accent-warning { border-right-color: var(--sms-warning); }
        .sms-toggle-card.accent-info { border-right-color: var(--sms-info); }
        .sms-toggle-card.accent-success { border-right-color: var(--sms-success); }
        .sms-toggle-card.accent-danger { border-right-color: var(--sms-danger); }
        .sms-toggle-card.accent-birthday { border-right-color: var(--sms-birthday); }

        .sms-toggle-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(115, 103, 240, .1);
            color: var(--sms-primary);
        }
        .accent-warning .sms-toggle-icon { background: rgba(255, 159, 67, .12); color: var(--sms-warning); }
        .accent-info .sms-toggle-icon { background: rgba(0, 207, 232, .12); color: var(--sms-info); }
        .accent-success .sms-toggle-icon { background: rgba(40, 199, 111, .12); color: var(--sms-success); }
        .accent-danger .sms-toggle-icon { background: rgba(234, 84, 85, .12); color: var(--sms-danger); }
        .accent-birthday .sms-toggle-icon { background: rgba(232, 62, 140, .12); color: var(--sms-birthday); }

        .sms-toggle-content { flex: 1; min-width: 0; }
        .sms-toggle-content strong { display: block; font-size: 13.5px; color: #5e5873; }
        .sms-toggle-content small { display: block; color: #a8a4b8; font-size: 11.5px; margin-top: 2px; }
        .sms-toggle-switch { margin: 0; }
        .sms-toggle-switch .vs-checkbox-con { margin: 0; }

        /* --- کارت مجزای تبریک تولد --- */
        .sms-birthday-outer-card {
            border: 1px dashed var(--sms-birthday);
            background: linear-gradient(135deg, rgba(232, 62, 140, .04), transparent);
        }
        .sms-birthday-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(0, 207, 232, .08);
            border: 1px solid rgba(0, 207, 232, .25);
            color: #2b6f7a;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 12.5px;
            line-height: 2;
            margin-bottom: 16px;
        }
        .sms-birthday-alert i { margin-top: 3px; color: var(--sms-info); flex-shrink: 0; }

        /* --- کارت‌های پترن هر ارائه‌دهنده --- */
        .sms-pattern-card {
            border: 1px solid #ebe9f1;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            background: #fbfafd;
        }
        .sms-pattern-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        .sms-pattern-label i { color: var(--sms-primary); }
        .sms-sample-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sms-sample-text { background: #f5f4f9; font-size: 12.5px; }
        .btn-copy-sample {
            border: none;
            background: transparent;
            color: var(--sms-primary);
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 6px;
            transition: background .15s ease;
        }
        .btn-copy-sample:hover { background: rgba(115, 103, 240, .1); }
        .btn-copy-sample.copied { color: var(--sms-success); }

        /* --- تنظیمات اختصاصی بله --- */
        .bale-intro-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: rgba(115, 103, 240, .06);
            border: 1px solid rgba(115, 103, 240, .2);
            border-radius: 12px;
            padding: 16px;
            font-size: 13px;
            line-height: 2;
        }
        .bale-intro-alert > i {
            color: var(--sms-primary);
            font-size: 22px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .bale-intro-alert ol { padding-right: 18px; }
        .bale-intro-alert code {
            background: rgba(115, 103, 240, .12);
            color: var(--sms-primary);
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .bale-test-result-success {
            background: rgba(40, 199, 111, .1);
            border: 1px solid rgba(40, 199, 111, .3);
            color: #1a7a45;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .bale-test-result-error {
            background: rgba(234, 84, 85, .1);
            border: 1px solid rgba(234, 84, 85, .3);
            color: #8a2b2c;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .btn-test-bale:disabled,
        .btn-set-webhook:disabled,
        .btn-delete-webhook:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .sms-save-btn { display: flex; align-items: center; padding: 10px 28px; }

        @media (max-width: 767px) {
            .sms-intro-card .card-body { flex-direction: column; text-align: center; }
            .sms-intro-icon { margin: 0 0 12px 0; }
        }
    </style>
@endpush

@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">تنظیمات</li>
                                    <li class="breadcrumb-item active">تنظیمات پیامک</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section class="users-edit sms-settings-wrap">

                    {{-- ================= معرفی صفحه ================= --}}
                    <div class="card sms-intro-card">
                        <div class="card-body d-flex align-items-center flex-wrap">
                            <div class="sms-intro-icon">
                                <i class="feather icon-message-square"></i>
                            </div>
                            <div class="sms-intro-text">
                                <h4 class="mb-25">تنظیمات پنل و پیامک‌های خودکار</h4>
                                <p class="mb-0 text-muted">
                                    از این صفحه می‌توانید ارائه‌دهنده پیامک را انتخاب، اطلاعات اتصال به پنل را وارد و مشخص کنید
                                    در چه رویدادهایی (ثبت‌نام، سفارش، کیف پول و ...) پیامک به‌صورت خودکار ارسال شود.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form id="sms-form" action="{{ route('admin.settings.sms') }}" method="POST">
                        @csrf

                        {{-- ================= انتخاب ارائه‌دهنده ================= --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="sms-section-title">
                                    <i class="feather icon-server"></i>
                                    <span>ارائه‌دهنده پنل پیامک / پیام‌رسان</span>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label>ارائه دهنده پنل پیامک / پیام‌رسان</label>
                                        <select id="sms-panel-provider" class="form-control" name="sms_panel_provider">
                                            <option value="ippanel" {{ option('sms_panel_provider', 'ippanel') == 'ippanel' ? 'selected' : '' }}>ippanel</option>
                                            <option value="kavenegar" {{ option('sms_panel_provider', 'ippanel') == 'kavenegar' ? 'selected' : '' }}>کاوه نگار</option>
                                            <option value="melipayamak" {{ option('sms_panel_provider', 'ippanel') == 'melipayamak' ? 'selected' : '' }}>ملی پیامک</option>
                                            <option value="farazsms" {{ option('sms_panel_provider', 'ippanel') == 'farazsms' ? 'selected' : '' }}>فراز اس ام اس</option>
                                            <option value="idehpardazan" {{ option('sms_panel_provider', 'ippanel') == 'idehpardazan' ? 'selected' : '' }}>ایده پردازان</option>
                                            <option value="bale" {{ option('sms_panel_provider', 'ippanel') == 'bale' ? 'selected' : '' }}>بله (Bale Messenger)</option>
                                        </select>
                                        <small class="text-muted d-block mt-50">با انتخاب «بله»، بخش تنظیمات ربات بله در پایین صفحه فعال می‌شود.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label>شماره تلفن مدیر برای ارسال اطلاع‌رسانی‌ها</label>
                                        <div class="input-group mb-75">
                                            <input type="text" name="admin_mobile_number" class="form-control ltr" value="{{ option('admin_mobile_number') }}" placeholder="09xxxxxxxxx">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= گروه: ثبت‌نام، ورود و احراز هویت ================= --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="sms-section-title">
                                    <i class="feather icon-user-check"></i>
                                    <span>ثبت‌نام، ورود و احراز هویت</span>
                                </div>
                                <div class="sms-toggle-grid">
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'sms_on_seller_register', 'icon' => 'icon-briefcase', 'title' => 'ارسال پیامک موقع ایجاد فروشنده', 'accent' => 'primary'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'sms_on_user_register', 'icon' => 'icon-user-plus', 'title' => 'ارسال پیامک موقع ایجاد کاربر', 'accent' => 'primary'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'sms_to_verify_user', 'icon' => 'icon-shield', 'title' => 'تایید کاربر یا فروشنده با شماره همراه', 'accent' => 'primary'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'forgot_password_link', 'icon' => 'icon-key', 'title' => 'بازیابی رمز عبور با کد تایید', 'accent' => 'primary'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'login_with_code', 'icon' => 'icon-log-in', 'title' => 'ورود با رمز یکبار مصرف', 'accent' => 'primary'])
                                </div>
                            </div>
                        </div>

                        {{-- ================= گروه: سفارش‌ها ================= --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="sms-section-title">
                                    <i class="feather icon-shopping-cart"></i>
                                    <span>سفارش‌ها</span>
                                </div>
                                <div class="sms-toggle-grid">
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'sms_on_order_paid', 'icon' => 'icon-check-circle', 'title' => 'پرداخت سفارش', 'desc' => 'اطلاع‌رسانی به مدیر', 'accent' => 'warning'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'title' => 'لغو سفارش', 'desc' => 'اطلاع‌رسانی به مدیر', 'accent' => 'danger'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'seller_sms_on_order_paid', 'icon' => 'icon-check-circle', 'title' => 'پرداخت سفارش', 'desc' => 'اطلاع‌رسانی به فروشنده', 'accent' => 'warning'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'seller_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'title' => 'لغو سفارش', 'desc' => 'اطلاع‌رسانی به فروشنده', 'accent' => 'danger'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'user_sms_on_order_paid', 'icon' => 'icon-check-circle', 'title' => 'پرداخت سفارش', 'desc' => 'اطلاع‌رسانی به کاربر', 'accent' => 'warning'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'user_sms_on_order_cancelled', 'icon' => 'icon-x-circle', 'title' => 'لغو سفارش', 'desc' => 'اطلاع‌رسانی به کاربر', 'accent' => 'danger'])
                                </div>
                            </div>
                        </div>

                        {{-- ================= گروه: کیف پول ================= --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="sms-section-title">
                                    <i class="feather icon-credit-card"></i>
                                    <span>کیف پول</span>
                                </div>
                                <div class="sms-toggle-grid">
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'wallet_increase_sms', 'icon' => 'icon-arrow-up-circle', 'title' => 'افزایش موجودی کیف پول', 'accent' => 'success'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'wallet_decrease_sms', 'icon' => 'icon-arrow-down-circle', 'title' => 'کاهش موجودی کیف پول', 'accent' => 'danger'])
                                    @include('back.settings.partials.sms-toggle-card', ['name' => 'wallet_refund_sms', 'icon' => 'icon-rotate-ccw', 'title' => 'برگشت وجه به کیف پول', 'accent' => 'info'])
                                </div>
                            </div>
                        </div>

                        {{-- ================= گروه: تبریک تولد (جدا و با توضیح) ================= --}}
                        <div class="card sms-birthday-outer-card">
                            <div class="card-body">
                                <div class="sms-section-title">
                                    <i class="feather icon-gift"></i>
                                    <span>پیامک مناسبتی</span>
                                </div>
                                <div class="sms-toggle-grid sms-toggle-grid-single">
                                    @include('back.settings.partials.sms-toggle-card', [
                                        'name' => 'happy_birthday_sms',
                                        'icon' => 'icon-gift',
                                        'title' => 'ارسال پیامک تبریک تولد',
                                        'desc' => 'کاملاً خودکار، روزانه توسط زمان‌بند سیستم بررسی می‌شود',
                                        'accent' => 'birthday',
                                    ])
                                </div>
                                <div class="sms-birthday-alert mt-1">
                                    <i class="feather icon-info"></i>
                                    <span>
                                        این گزینه به هیچ رویداد خاصی (مثل ثبت سفارش یا ورود کاربر) وابسته نیست. با فعال بودن آن،
                                        سامانه هر روز به‌صورت خودکار تاریخ تولد ثبت‌شده کاربران را بررسی می‌کند و برای کاربرانی که
                                        امروز تولدشان است، پیامک ارسال می‌کند. کد پترن مربوطه را در بخش تنظیمات همان ارائه‌دهنده
                                        در پایین همین صفحه وارد کنید.
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ================= تنظیمات اختصاصی هر ارائه‌دهنده ================= --}}
                        @include('back.settings.partials.ippanel-sms')
                        @include('back.settings.partials.kavenegar-sms')
                        @include('back.settings.partials.melipayamak-sms')
                        @include('back.settings.partials.idehpardazan-sms')
                        @include('back.settings.partials.faraz-sms')
                        @include('back.settings.partials.bale-sms')

                        <div class="row">
                            <div class="col-12 d-flex flex-sm-row flex-column justify-content-end mt-1 mb-3">
                                <button type="submit" class="btn btn-primary glow sms-save-btn">
                                    <i class="feather icon-save mr-50"></i>
                                    ذخیره تغییرات
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>

@endsection


    @include('back.partials.plugins', ['plugins' => ['jquery.validate']])

    @php
        $help_videos = [
            config('general.video-helpes.sms-config')
        ];
    @endphp

    @push('scripts')
        <script src="{{ asset('back/assets/js/pages/settings/sms.js') }}?v=6"></script>
        <script>
            /* ============================================================
             |  کپی متن نمونه پترن با یک کلیک
             |============================================================ */
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-copy-sample');
                if (!btn) return;

                var container = btn.closest('.form-group') || btn.parentElement.parentElement;
                var textarea = container ? container.querySelector('.sms-sample-text') : null;
                if (!textarea) return;

                var restoreIcon = function () {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="feather icon-copy"></i>';
                };

                var showCopied = function () {
                    btn.classList.add('copied');
                    btn.innerHTML = '<i class="feather icon-check"></i>';
                    setTimeout(restoreIcon, 1500);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textarea.value).then(showCopied);
                } else {
                    textarea.removeAttribute('readonly');
                    textarea.select();
                    document.execCommand('copy');
                    textarea.setAttribute('readonly', 'readonly');
                    showCopied();
                }
            });

            /* ============================================================
             |  نمایش/مخفی کردن تنظیمات هر ارائه‌دهنده (شامل بله)
             |============================================================ */
            (function () {
                var providerSelect = document.getElementById('sms-panel-provider');
                var providerSettings = document.querySelectorAll('.sms-provider-settings');

                function toggleProviders() {
                    var selected = providerSelect.value;
                    providerSettings.forEach(function (el) {
                        el.style.display = (el.dataset.provider === selected) ? 'block' : 'none';
                    });
                }

                if (providerSelect) {
                    providerSelect.addEventListener('change', toggleProviders);
                    toggleProviders();
                }
            })();

            /* ============================================================
             |  عملیات ربات بله (تست اتصال، تنظیم/حذف وب‌هوک، کپی آدرس)
             |============================================================ */
            (function () {
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

                function showResult(container, success, html) {
                    if (!container) return;
                    container.style.display = 'block';
                    container.className = success ? 'bale-test-result-success mt-1' : 'bale-test-result-error mt-1';
                    container.innerHTML = html;
                }

                function ajax(url, data, btn, cb) {
                    if (btn) { btn.disabled = true; }
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data || {})
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (res) { cb(null, res); })
                        .catch(function (err) { cb(err); })
                        .finally(function () { if (btn) { btn.disabled = false; } });
                }

                // تست اتصال
                var btnTest = document.getElementById('btn-test-bale');
                if (btnTest) {
                    btnTest.addEventListener('click', function () {
                        // ابتدا فرم را ذخیره می‌کنیم تا توکن جدید اعمال شود
                        var tokenInput = document.querySelector('input[name="BALE_BOT_TOKEN"]');
                        ajax('{{ route("admin.settings.bale.test") }}', {
                            bot_token: tokenInput ? tokenInput.value : null
                        }, btnTest, function (err, res) {
                            var box = document.getElementById('bale-test-result');
                            if (err || !res) {
                                showResult(box, false, '<i class="feather icon-alert-triangle"></i> خطا در ارتباط با سرور');
                                return;
                            }
                            if (res.success) {
                                var info = '';
                                if (res.bot_name)  info += '<div>نام ربات: <strong>' + res.bot_name + '</strong></div>';
                                if (res.username)  info += '<div>یوزرنیم: <strong>@' + res.username + '</strong></div>';
                                if (res.bot_id)    info += '<div>شناسه ربات: <strong>' + res.bot_id + '</strong></div>';
                                showResult(box, true, '<i class="feather icon-check-circle"></i> ' + res.message + info);
                            } else {
                                showResult(box, false, '<i class="feather icon-x-circle"></i> ' + res.message);
                            }
                        });
                    });
                }

                // تنظیم وب‌هوک
                var btnSetWebhook = document.getElementById('btn-set-webhook');
                if (btnSetWebhook) {
                    btnSetWebhook.addEventListener('click', function () {
                        var urlInput = document.getElementById('bale-webhook-url');
                        ajax('{{ route("admin.settings.bale.set-webhook") }}', {
                            webhook_url: urlInput ? urlInput.value : null
                        }, btnSetWebhook, function (err, res) {
                            var box = document.getElementById('bale-test-result');
                            if (err || !res) {
                                showResult(box, false, '<i class="feather icon-alert-triangle"></i> خطا در ارتباط با سرور');
                                return;
                            }
                            showResult(box, !!res.success, '<i class="feather ' + (res.success ? 'icon-check-circle' : 'icon-x-circle') + '"></i> ' + (res.message || ''));
                        });
                    });
                }

                // حذف وب‌هوک
                var btnDeleteWebhook = document.getElementById('btn-delete-webhook');
                if (btnDeleteWebhook) {
                    btnDeleteWebhook.addEventListener('click', function () {
                        if (!confirm('آیا از حذف وب‌هوک مطمئن هستید؟')) return;
                        ajax('{{ route("admin.settings.bale.delete-webhook") }}', {}, btnDeleteWebhook, function (err, res) {
                            var box = document.getElementById('bale-test-result');
                            if (err || !res) {
                                showResult(box, false, '<i class="feather icon-alert-triangle"></i> خطا در ارتباط با سرور');
                                return;
                            }
                            showResult(box, !!res.success, '<i class="feather ' + (res.success ? 'icon-check-circle' : 'icon-x-circle') + '"></i> ' + (res.message || ''));
                        });
                    });
                }

                // کپی آدرس وب‌هوک
                var btnCopyWebhook = document.querySelector('.btn-copy-webhook');
                if (btnCopyWebhook) {
                    btnCopyWebhook.addEventListener('click', function () {
                        var urlInput = document.getElementById('bale-webhook-url');
                        if (!urlInput) return;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(urlInput.value).then(function () {
                                var old = btnCopyWebhook.innerHTML;
                                btnCopyWebhook.innerHTML = '<i class="feather icon-check"></i> کپی شد';
                                setTimeout(function () { btnCopyWebhook.innerHTML = old; }, 1500);
                            });
                        }
                    });
                }
            })();
        </script>
    @endpush
