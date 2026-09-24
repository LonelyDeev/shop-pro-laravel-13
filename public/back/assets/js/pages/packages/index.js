/* ============================================================
   Packages - Index page logic
   (modal detail logic is shared in modal.js)
   ============================================================ */
(function ($) {
    "use strict";

    let pendingSlug = null;
    let pendingName = null;
    let pendingIsFree = true;
    let pendingPrice = 0;
    let pendingPlans = [];
    let selectedPlanId = null;
    let selectedPlanPrice = 0;
    let pendingUseLicense = false;

    const csrfToken = window.csrfToken || $('meta[name="csrf-token"]').attr('content');

    function route(name, slug) {
        return (window.routes[name] || '').replace(':slug', slug);
    }
    function number_format(n) {
        return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function getPlanFinalPrice(plan) {
        return parseInt(plan.discount_price ?? plan.price ?? 0);
    }

    function getPlanDurationLabel(plan) {
        const months = plan.duration_months || 0;
        if (months === 0) return 'نامحدود';
        if (months < 12) return months + ' ماه';
        const years = months / 12;
        return (years === Math.floor(years) ? Math.floor(years) : years) + ' سال';
    }

    function findCheapestPlan(plans) {
        if (!plans || plans.length === 0) return null;
        return plans.reduce(function (cheapest, plan) {
            const price = getPlanFinalPrice(plan);
            if (!cheapest) return plan;
            return price < getPlanFinalPrice(cheapest) ? plan : cheapest;
        }, null);
    }

    /* ============================================================
       Init
       ============================================================ */
    $(function () {
        // باز کردن modal تأیید نصب (فقط از دکمه نصب کارت - نه از مدال جزئیات)
        $(document).on('click', '.btn-install', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            pendingSlug = $btn.data('slug');
            pendingName = $btn.data('name');
            pendingIsFree = String($btn.data('free')) === '1';
            pendingPrice = parseInt($btn.data('price')) || 0;
            pendingUseLicense = false;

            try {
                pendingPlans = $btn.data('plans') || [];
                if (typeof pendingPlans === 'string') pendingPlans = JSON.parse(pendingPlans);
            } catch (err) { pendingPlans = []; }

            $('#confirm-pkg-name').text(pendingName);

            // ★ پکیج پولیِ خریداری‌شده → اول لایسنس با API بررسی شود
            const isPurchased = String($btn.data('purchased')) === '1';

            if (pendingIsFree) {
                $('#confirm-license-section, #confirm-license-warning, #confirm-plans-section, #confirm-payment-info').addClass('d-none');
                updateConfirmButton(true);
                $('#confirm-btn-text').text('نصب پکیج');
                selectedPlanId = null;
                $('#install-confirm-modal').modal('show');
                return;
            }

            if (isPurchased) {
                const btnHtml = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                $.ajax({
                    url: route('checkPurchase', pendingSlug),
                    method: 'POST',
                    data: { _token: csrfToken }
                })
                    .done(function (resp) {
                        if (resp.purchased && resp.valid) {
                            renderLicenseMode(resp);          // ✔ نصب مجدد بدون پرداخت
                        } else if (resp.purchased) {
                            renderPaidFlow();                  // ✘ منقضی → خرید + هشدار
                            showLicenseWarning(resp.message);
                        } else {
                            renderPaidFlow();                  // جریان عادی
                        }
                        $('#install-confirm-modal').modal('show');
                    })
                    .fail(function () {
                        renderPaidFlow();
                        $('#install-confirm-modal').modal('show');
                    })
                    .always(function () {
                        $btn.prop('disabled', false).html(btnHtml);
                    });
                return;
            }

            renderPaidFlow();
            $('#install-confirm-modal').modal('show');
        });

        // انتخاب پلن (فقط یک پلن)
        $(document).on('click', '.pkg-plan-card', function () {
            $('.pkg-plan-card').removeClass('selected');
            $(this).addClass('selected');
            selectedPlanId = $(this).data('plan-id');
            selectedPlanPrice = parseInt($(this).data('plan-price')) || 0;

            // آپدیت نمایش قیمت
            if (selectedPlanPrice === 0) {
                $('#confirm-pkg-price').html('<span class="text-success">رایگان</span>');
                updateConfirmButton(true);
            } else {
                $('#confirm-pkg-price').text(number_format(selectedPlanPrice));
                updateConfirmButton(false);
            }
        });

        // تأیید و شروع نصب
        let confirmBtnDefaultHtml = null;

        $('#confirm-install-btn').on('click', function () {
            const $btn = $(this);
            if (!confirmBtnDefaultHtml) confirmBtnDefaultHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ارسال...');

            const data = { _token: csrfToken };
            if (pendingUseLicense) {
                data.use_license = 1;                 // ★ نصب مجدد با لایسنس
            } else if (selectedPlanId) {
                data.pricing_plan_id = selectedPlanId;
            }

            $.ajax({
                url: route('install', pendingSlug),
                method: 'POST',
                data: data,
                success: function (resp) {
                    if (resp.success && resp.redirect_url) {
                        $('#install-confirm-modal').modal('hide');
                        window.location.href = resp.redirect_url;
                    } else if (resp.success) {
                        $('#install-confirm-modal').modal('hide');
                        if (window.PkgProgress) {
                            window.PkgProgress.startPolling(pendingSlug, pendingName);
                        }
                    } else if (resp.needs_payment) {
                        // لایسنس بین لحظه بررسی و تأیید منقضی شده → سوییچ به خرید
                        pendingUseLicense = false;
                        $('#confirm-license-section').addClass('d-none');
                        showLicenseWarning(resp.message);
                        if (pendingPlans.length) {
                            renderPlans(pendingPlans);
                            $('#confirm-plans-section').removeClass('d-none');
                        }
                        $btn.prop('disabled', false).html(confirmBtnDefaultHtml);
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message || 'عملیات ناموفق بود.', confirmButtonText: 'بستن' });
                        $btn.prop('disabled', false).html(confirmBtnDefaultHtml);
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور.', confirmButtonText: 'بستن' });
                    $btn.prop('disabled', false).html(confirmBtnDefaultHtml);
                }
            });
        });

        // بررسی آپدیت‌ها
        $('#btn-check-updates').on('click', function () {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال بررسی...');
            $.ajax({
                url: route('checkUpdates', ''),
                method: 'POST',
                data: { _token: csrfToken },
                success: function (resp) {
                    if (resp.update_count > 0) {
                        let html = '<div class="text-right"><p>' + resp.update_count + ' آپدیت در دسترس است:</p><ul class="list-unstyled">';
                        resp.updates.forEach(function (u) {
                            html += '<li class="mb-1"><strong>' + escapeHtml(u.name) + '</strong> <span class="text-muted">(v' + u.current + ' → v' + u.latest + ')</span></li>';
                        });
                        html += '</ul></div>';
                        Swal.fire({ icon: 'info', title: 'آپدیت‌های جدید', html: html, confirmButtonText: 'مشاهده', showCancelButton: true, cancelButtonText: 'بستن' })
                            .then(function (result) { if (result.isConfirmed) window.location.href = '{{ route("admin.packages.installed") }}'; });
                    } else {
                        Swal.fire({ icon: 'success', title: 'همه چیز به‌روز است', text: 'هیچ آپدیتی برای ماژول‌های نصب‌شده موجود نیست.', confirmButtonText: 'بستن' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'خطا', text: 'بررسی آپدیت‌ها ناموفق بود.', confirmButtonText: 'بستن' });
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="feather icon-refresh-cw"></i> بررسی آپدیت‌ها');
                }
            });
        });

        // ریست هنگام بستن مدال
        $('#install-confirm-modal').on('hidden.bs.modal', function () {
            selectedPlanId = null;
            selectedPlanPrice = 0;
            pendingUseLicense = false;
            $('.pkg-plan-card').removeClass('selected');
            $('#confirm-license-section').addClass('d-none');
            $('#confirm-license-warning').addClass('d-none');
        });
    });

    /* جریان عادی پولی (پلن یا مبلغ ثابت) */
    function renderPaidFlow() {
        $('#confirm-license-section').addClass('d-none');
        if (pendingPlans && pendingPlans.length > 0) {
            $('#confirm-payment-info').addClass('d-none');
            renderPlans(pendingPlans);
            $('#confirm-plans-section').removeClass('d-none');
        } else {
            $('#confirm-plans-section').addClass('d-none');
            $('#confirm-payment-info').removeClass('d-none');
            $('#confirm-pkg-price').text(number_format(pendingPrice));
            updateConfirmButton(pendingPrice === 0);
            selectedPlanId = null;
        }
    }

    /* حالت نصب مجدد با لایسنس فعال */
    function renderLicenseMode(resp) {
        pendingUseLicense = true;
        selectedPlanId = null;

        $('#confirm-plans-section, #confirm-payment-info, #confirm-license-warning').addClass('d-none');

        let expiryText = '';
        if (resp.expires_at) {
            const days = daysRemaining(resp.expires_at);
            expiryText = ' (اعتبار تا ' + toJalali(resp.expires_at) +
                (days !== null ? ' — حدود ' + number_format(Math.max(days, 0)) + ' روز باقی‌مانده' : '') + ')';
        } else {
            expiryText = ' (نامحدود)';
        }
        $('#confirm-license-expiry').text(expiryText);

        $('#confirm-license-section').removeClass('d-none');
        updateConfirmButton(true);
        $('#confirm-btn-text').text('بررسی و نصب');
    }

    /* هشدار انقضا */
    function showLicenseWarning(message) {
        $('#confirm-license-warning').removeClass('d-none');
        $('#confirm-license-warning-text').text(message || 'لایسنس قبلی شما منقضی شده است. برای نصب، یکی از پلن‌ها را انتخاب کنید.');
    }

    /* تبدیل میلادی → جلالی */
    function toJalali(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        let gy = d.getFullYear(), gm = d.getMonth() + 1, gd = d.getDate();
        const g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        let jy = (gy <= 1600) ? 0 : 979;
        gy -= (gy <= 1600) ? 621 : 1600;
        const gy2 = (gm > 2) ? (gy + 1) : gy;
        let days = (365*gy) + Math.floor((gy2+3)/4) - Math.floor((gy2+99)/100) + Math.floor((gy2+399)/400) - 80 + gd + g_d_m[gm-1];
        jy += 33 * Math.floor(days/12053); days %= 12053;
        jy += 4 * Math.floor(days/1461);  days %= 1461;
        if (days > 365) { jy += Math.floor((days-1)/365); days = (days-1)%365; }
        const jm = (days < 186) ? 1 + Math.floor(days/31) : 7 + Math.floor((days-186)/30);
        const jd = 1 + ((days < 186) ? (days%31) : ((days-186)%30));
        return jy + '/' + String(jm).padStart(2,'0') + '/' + String(jd).padStart(2,'0');
    }

    function daysRemaining(dateStr) {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return null;
        return Math.ceil((d.getTime() - Date.now()) / 86400000);
    }

    /* ============================================================
       آپدیت متن دکمه‌ی تأیید بر اساس رایگان/پولی بودن
       ============================================================ */
    function updateConfirmButton(isFree) {
        const $btn = $('#confirm-install-btn');
        const $icon = $btn.find('i');
        const $text = $('#confirm-btn-text');
        if (isFree) {
            $icon.removeClass('icon-credit-card').addClass('icon-download-cloud');
            $text.text('نصب پکیج');
            $btn.removeClass('pkg-btn-warning').addClass('pkg-btn-primary');
        } else {
            $icon.removeClass('icon-download-cloud').addClass('icon-credit-card');
            $text.text('پرداخت و نصب');
            $btn.removeClass('pkg-btn-primary').addClass('pkg-btn-warning');
        }
    }

    /* ============================================================
       رندر لیست پلن‌ها
       ============================================================ */
    function renderPlans(plans) {
        const $list = $('#confirm-plans-list');
        $list.empty();

        if (!plans || plans.length === 0) {
            $('#confirm-plans-section').addClass('d-none');
            return;
        }

        const cheapestPlan = findCheapestPlan(plans);

        plans.forEach(function (plan) {
            const finalPrice = getPlanFinalPrice(plan);
            const isCheapest = cheapestPlan && plan.id === cheapestPlan.id;
            const isFree = (finalPrice === 0);
            const hasDiscount = plan.discount_price !== null && plan.discount_price !== undefined && plan.discount_price < plan.price;
            const isOneTime = plan.is_one_time === true || plan.is_one_time === 1 || plan.is_one_time === '1';

            // badges
            let badges = '';
            if (isOneTime) {
                badges += '<span class="pkg-plan-badge pkg-plan-badge-one-time">یک‌بار مصرف</span>';
            }
            if (isCheapest) {
                badges += '<span class="pkg-plan-badge pkg-plan-badge-cheapest">ارزان‌ترین</span>';
            }

            // price
            let priceHtml = '';
            if (isFree) {
                priceHtml = '<div class="pkg-plan-price"><span class="pkg-plan-price-free">رایگان</span></div>';
            } else {
                priceHtml = '<div class="pkg-plan-price">';
                if (hasDiscount) {
                    priceHtml += '<span class="pkg-plan-price-original">' + number_format(plan.price) + '</span>';
                }
                priceHtml += '<span class="pkg-plan-price-final">' + number_format(finalPrice) + '</span>';
                priceHtml += '<span class="pkg-plan-price-unit">تومان</span>';
                priceHtml += '</div>';
            }

            const descHtml = plan.description
                ? '<div class="pkg-plan-desc">' + escapeHtml(plan.description) + '</div>'
                : '';

            const html = `
                <div class="col-md-4">
                    <div class="pkg-plan-card ${isCheapest ? 'selected' : ''}" data-plan-id="${plan.id}" data-plan-price="${finalPrice}">
                        <div class="pkg-plan-radio"></div>
                        <div class="pkg-plan-info">
                            <div class="pkg-plan-name">${escapeHtml(plan.name)}</div>
                            <div class="pkg-plan-duration"><i class="feather icon-clock"></i> ${getPlanDurationLabel(plan)}</div>
                            ${badges ? '<div class="pkg-plan-badges">' + badges + '</div>' : ''}
                        </div>
                        ${priceHtml}
                        ${descHtml}
                    </div>
                </div>
            `;

            $list.append(html);
        });

        // انتخاب پیش‌فرض روی ارزان‌ترین
        if (cheapestPlan) {
            selectedPlanId = cheapestPlan.id;
            selectedPlanPrice = getPlanFinalPrice(cheapestPlan);
            const cheapestPrice = selectedPlanPrice;
            if (cheapestPrice === 0) {
                $('#confirm-pkg-price').html('<span class="text-success">رایگان</span>');
                updateConfirmButton(true);
            } else {
                $('#confirm-pkg-price').text(number_format(cheapestPrice));
                updateConfirmButton(false);
            }
        }
    }


    function fmt(n) {
        return (parseInt(n, 10) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    function esc(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function row(label, value, opts) {
        opts = opts || {};
        let val = esc(value);
        if (opts.copyable) {
            val += ' <button type="button" class="pay-copy-btn" data-copy="' + esc(value) + '"><i class="feather icon-copy"></i></button>';
        }
        return '<div class="pay-row">' +
            '<span class="pay-row-label">' + esc(label) + '</span>' +
            '<span class="pay-row-value ' + (opts.ltr ? 'ltr' : '') + '">' + val + '</span>' +
            '</div>';
    }

    let progressStarted = false;

    function showPaymentResult(r) {
        const $modal = $('#payment-result-modal');
        const success = r.status === 'success';

        $modal.toggleClass('pay-success', success).toggleClass('pay-failed', !success);
        $('#pay-title').text(r.title || (success ? 'پرداخت موفق' : 'پرداخت ناموفق'));
        $('#pay-message').text(r.message || '');

        let rows = '';
        if (r.package_name) rows += row('پکیج', r.package_name);
        if (r.amount !== null && r.amount !== undefined && r.amount !== '') {
            rows += row('مبلغ پرداخت‌شده', fmt(r.amount) + ' تومان');
        }
        if (r.transaction_id) rows += row('کد پیگیری', r.transaction_id, { ltr: true });
        if (r.license_key)    rows += row('کد لایسنس', r.license_key, { ltr: true, copyable: true });

        $('#pay-details').html(rows).toggleClass('d-none', rows === '');

        const $main = $('#pay-action-main').off('click');
        if (success) {
            $main.html('<i class="feather icon-activity"></i> مشاهده روند نصب');
            $main.on('click', function () { $modal.modal('hide'); });
        } else {
            const slug = String(r.package_slug || '').replace(/[^a-z0-9\-_]/gi, '');
            const $retry = $('.pkg-card[data-slug="' + slug + '"] .btn-install').first();
            if ($retry.length) {
                $main.html('<i class="feather icon-refresh-cw"></i> تلاش دوباره');
                $main.on('click', function () {
                    $modal.modal('hide');
                    setTimeout(function () { $retry.trigger('click'); }, 400);
                });
            } else {
                $main.html('<i class="feather icon-shopping-bag"></i> بازگشت به بازار');
                $main.on('click', function () { $modal.modal('hide'); });
            }
        }

        setTimeout(function () { $modal.modal('show'); }, 400);
    }

    // کپی لایسنس
    $(document).on('click', '.pay-copy-btn', function () {
        const text = String($(this).data('copy') || '');
        const $icon = $(this).find('i');
        const done = function () {
            $icon.removeClass('icon-copy').addClass('icon-check text-success');
            setTimeout(function () { $icon.removeClass('icon-check text-success').addClass('icon-copy'); }, 1800);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done, done);
        } else {
            const $tmp = $('<textarea>').css({ position: 'fixed', opacity: 0 }).val(text).appendTo('body');
            $tmp[0].select();
            try { document.execCommand('copy'); } catch (e) {}
            $tmp.remove();
            done();
        }
    });

    $(function () {
        const result = window.paymentResult;
        if (!result) return;

        showPaymentResult(result);

        // بعد از بستن مودال، در صورت موفقیت، polling وضعیت نصب شروع شود
        $('#payment-result-modal').one('hidden.bs.modal', function () {
            if (result.status === 'success' && !progressStarted &&
                result.package_slug && window.PkgProgress) {
                progressStarted = true;
                window.PkgProgress.startPolling(result.package_slug, result.package_name || result.package_slug);
            }
        });
    });
})(jQuery);
