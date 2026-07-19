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
            pendingSlug = $(this).data('slug');
            pendingName = $(this).data('name');
            pendingIsFree = $(this).data('free') === '1' || $(this).data('free') === true;
            pendingPrice = parseInt($(this).data('price')) || 0;

            try {
                pendingPlans = $(this).data('plans') || [];
                if (typeof pendingPlans === 'string') {
                    pendingPlans = JSON.parse(pendingPlans);
                }
            } catch (err) {
                pendingPlans = [];
            }

            $('#confirm-pkg-name').text(pendingName);

            if (pendingIsFree) {
                $('#confirm-plans-section').addClass('d-none');
                $('#confirm-payment-info').addClass('d-none');
                updateConfirmButton(true);
                selectedPlanId = null;
            } else if (pendingPlans && pendingPlans.length > 0) {
                renderPlans(pendingPlans);
                $('#confirm-plans-section').removeClass('d-none');
            } else {
                $('#confirm-plans-section').addClass('d-none');
                $('#confirm-payment-info').removeClass('d-none');
                $('#confirm-pkg-price').text(number_format(pendingPrice));
                updateConfirmButton(pendingPrice === 0);
                selectedPlanId = null;
            }

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
        $('#confirm-install-btn').on('click', function () {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ارسال...');

            const data = { _token: csrfToken };
            if (selectedPlanId) {
                data.pricing_plan_id = selectedPlanId;
            }

            $.ajax({
                url: route('install', pendingSlug),
                method: 'POST',
                data: data,
                success: function (resp) {
                    if (resp.success && resp.redirect_url) {
                        // اگه پرداخت داره (redirect به درگاه)
                        window.location.href = resp.redirect_url;
                    } else if (resp.success) {
                        // نصب رایگان شروع شد - بستن مدال و نمایش progress
                        $('#install-confirm-modal').modal('hide');
                        if (window.PkgProgress) {
                            window.PkgProgress.startPolling(pendingSlug, pendingName);
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطا', text: resp.message || 'عملیات ناموفق بود.', confirmButtonText: 'بستن' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطا', text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور.', confirmButtonText: 'بستن' });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $('#install-confirm-modal').modal('hide');
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
            $('.pkg-plan-card').removeClass('selected');
        });
    });

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

})(jQuery);
