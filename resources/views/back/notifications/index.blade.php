@extends('back.layouts.master')

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
                                    <li class="breadcrumb-item active">مدیریت اعلان‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ===== نوار بالا ===== --}}
                <div class="nt-topbar">
                    <h4 class="nt-page-title"><i class="feather icon-bell"></i> مدیریت اعلان‌ها</h4>
                    <button type="button" id="nt-add-btn" class="nt-btn nt-btn--primary">
                        <i class="feather icon-plus"></i> ارسال اعلان جدید
                    </button>
                </div>

                {{-- ===== آمار ===== --}}
                <div class="nt-stats">
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#818CF8;--c2:#4F46E5;"><i class="feather icon-bell"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-total">0</span><span class="nt-stat__label">اعلان‌های ارسال‌شده</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#34D399;--c2:#059669;"><i class="feather icon-users"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-recipients">0</span><span class="nt-stat__label">کل دریافت‌کنندگان</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#60A5FA;--c2:#2563EB;"><i class="feather icon-check-circle"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-read">0</span><span class="nt-stat__label">خوانده‌شده</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#FB923C;--c2:#EA580C;"><i class="feather icon-clock"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-unread">0</span><span class="nt-stat__label">در انتظار مشاهده</span></div>
                    </div>
                </div>

                {{-- ===== کارت اصلی ===== --}}
                <section class="card nt-card">
                    <div class="nt-card__header">
                        <nav class="nt-pills">
                            <button type="button" data-type="all" class="nt-pill nt-pill--active">همه</button>
                            <button type="button" data-type="info" class="nt-pill">اطلاعیه</button>
                            <button type="button" data-type="success" class="nt-pill">موفقیت</button>
                            <button type="button" data-type="warning" class="nt-pill">هشدار</button>
                            <button type="button" data-type="danger" class="nt-pill">مهم</button>
                        </nav>
                        <div class="nt-search">
                            <i class="feather icon-search"></i>
                            <input type="text" id="nt-search" placeholder="جستجو در عنوان یا پیام...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0 nt-table">
                            <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>پیام</th>
                                <th>نوع</th>
                                <th>دریافت‌کنندگان</th>
                                <th>گروه هدف</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody id="nt-tbody"></tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- ================= مودال ارسال / ویرایش ================= --}}
    <div class="modal fade" id="nt-form-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content nt-modal">
                <form id="nt-form" novalidate>
                    <div class="modal-header nt-modal__header">
                        <h4 class="modal-title" id="nt-modal-title">ارسال اعلان جدید</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body nt-modal__body">
                        <input type="hidden" name="batch_id" value="">

                        <div class="nt-field">
                            <label>عنوان <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="nt-input" maxlength="190" placeholder="مثلاً: تخفیف ویژه پایان فصل">
                            <span class="nt-error" id="nt-error-title"></span>
                        </div>

                        <div class="nt-field">
                            <label>پیام <span class="text-danger">*</span></label>
                            <textarea name="message" rows="3" class="nt-input" maxlength="1000" placeholder="متن اعلان..."></textarea>
                            <span class="nt-error" id="nt-error-message"></span>
                        </div>

                        <div class="nt-grid2">
                            <div class="nt-field">
                                <label>نوع اعلان</label>
                                <select name="type" class="nt-input">
                                    <option value="info">اطلاعیه</option>
                                    <option value="success">موفقیت</option>
                                    <option value="warning">هشدار</option>
                                    <option value="danger">مهم</option>
                                </select>
                                <span class="nt-error" id="nt-error-type"></span>
                            </div>
                            <div class="nt-field">
                                <label>لینک (اختیاری)</label>
                                <input type="text" name="link" class="nt-input nt-input--ltr" placeholder="https://example.com">
                                <span class="nt-error" id="nt-error-link"></span>
                            </div>
                        </div>

                        {{-- ============ کارت‌های انتخاب گیرنده ============ --}}
                        <div class="nt-field" id="nt-targets">
                            <label>گیرندگان <span class="text-danger">*</span></label>

                            <p class="nt-targets__note">
                                <i class="feather icon-info"></i>
                                گروه مورد نظر را فعال کنید. اگر شخص خاصی انتخاب نشود، اعلان به <b>همه اعضای همان گروه</b> ارسال می‌شود.
                            </p>

                            <div class="nt-target-grid">
                                {{-- ---- کارت کاربران ---- --}}
                                <div class="nt-target" id="nt-target-users">
                                    <label class="nt-target__head">
                                        <input type="checkbox" name="send_users" value="1">
                                        <span class="nt-target__icon nt-target__icon--users"><i class="feather icon-users"></i></span>
                                        <span class="nt-target__text">
                                        <span class="nt-target__title">ارسال به کاربران سایت</span>
                                        <span class="nt-target__sub">اعضای عادی سایت</span>
                                    </span>
                                        <span class="nt-target__checkbox"><i class="feather icon-check"></i></span>
                                    </label>
                                    <div class="nt-target__body">
                                        <div class="nt-target__body-inner">
                                            <div class="nt-picker" data-group="users">
                                                <div class="nt-picker__box">
                                                    <i class="feather icon-search"></i>
                                                    <input type="text" class="nt-picker__search" placeholder="جستجوی کاربر (نام یا موبایل)..." autocomplete="off">
                                                    <span class="nt-picker__spinner"></span>
                                                </div>
                                                <div class="nt-picker__results"></div>
                                            </div>
                                            <div class="nt-chips"></div>
                                            <p class="nt-target__hint"><i class="feather icon-info"></i> اگر کاربری انتخاب نشود، به <b>همه کاربران سایت</b> ارسال می‌شود.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- ---- کارت فروشندگان ---- --}}
                                <div class="nt-target" id="nt-target-sellers">
                                    <label class="nt-target__head">
                                        <input type="checkbox" name="send_sellers" value="1">
                                        <span class="nt-target__icon nt-target__icon--sellers"><i class="feather icon-shopping-bag"></i></span>
                                        <span class="nt-target__text">
                                        <span class="nt-target__title">ارسال به فروشندگان</span>
                                        <span class="nt-target__sub">فروشندگان ثبت‌نام‌شده</span>
                                    </span>
                                        <span class="nt-target__checkbox"><i class="feather icon-check"></i></span>
                                    </label>
                                    <div class="nt-target__body">
                                        <div class="nt-target__body-inner">
                                            <div class="nt-picker" data-group="sellers">
                                                <div class="nt-picker__box">
                                                    <i class="feather icon-search"></i>
                                                    <input type="text" class="nt-picker__search" placeholder="جستجوی فروشنده (نام یا موبایل)..." autocomplete="off">
                                                    <span class="nt-picker__spinner"></span>
                                                </div>
                                                <div class="nt-picker__results"></div>
                                            </div>
                                            <div class="nt-chips"></div>
                                            <p class="nt-target__hint"><i class="feather icon-info"></i> اگر فروشنده‌ای انتخاب نشود، به <b>همه فروشندگان</b> ارسال می‌شود.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <span class="nt-error" id="nt-error-send_users"></span>
                        </div>

                        {{-- خلاصه گیرندگان در حالت ویرایش --}}
                        <div class="nt-field" id="nt-edit-targets" style="display:none">
                            <label>گیرندگان</label>
                            <div id="nt-edit-badges" class="nt-edit-badges"></div>
                            <p class="nt-target__hint nt-target__hint--lock">
                                <i class="feather icon-lock"></i> گیرندگان پس از ارسال قابل تغییر نیستند؛ فقط متن و ظاهر اعلان ویرایش می‌شود.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer nt-modal__footer">
                        <button type="button" class="nt-btn nt-btn--ghost" data-dismiss="modal">انصراف</button>
                        <button type="submit" id="nt-submit-btn" class="nt-btn nt-btn--primary">
                            <i class="feather icon-send"></i> ارسال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= مودال حذف ================= --}}
    <div class="modal fade" id="nt-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content nt-modal">
                <div class="nt-delete-box">
                    <div class="nt-delete-box__icon"><i class="feather icon-trash-2"></i></div>
                    <h5>حذف اعلان</h5>
                    <p>آیا از حذف «<b id="nt-delete-title"></b>» مطمئن هستید؟<br>این عمل قابل بازگشت نیست.</p>
                    <div class="nt-delete-box__btns">
                        <button type="button" class="nt-btn nt-btn--ghost" data-dismiss="modal">انصراف</button>
                        <button type="button" id="nt-confirm-delete" class="nt-btn nt-btn--danger">
                            <i class="feather icon-trash-2"></i> حذف کن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="nt-toasts" class="nt-toasts"></div>

    <style>
        :root { --nt-p: #7c3aed; --nt-p-dark: #6d28d9; --nt-p-soft: #f5f3ff; }

        .nt-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        .nt-page-title { margin: 0; font-size: 18px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .nt-page-title i { color: var(--nt-p); }
        .nt-btn {
            display: inline-flex; align-items: center; gap: 6px; border: none; border-radius: 10px;
            padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: .2s; font-family: inherit;
        }
        .nt-btn--primary { background: var(--nt-p); color: #fff; }
        .nt-btn--primary:hover { background: var(--nt-p-dark); box-shadow: 0 6px 14px -4px rgba(124, 58, 237, .5); }
        .nt-btn--danger { background: #ef4444; color: #fff; }
        .nt-btn--danger:hover { background: #dc2626; box-shadow: 0 6px 14px -4px rgba(239, 68, 68, .5); }
        .nt-btn--ghost { background: #f1f5f9; color: #64748b; }
        .nt-btn--ghost:hover { background: #e2e8f0; }
        .nt-btn:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }

        .nt-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .nt-stat {
            display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #eef0f5;
            border-radius: 16px; padding: 16px 18px; box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .nt-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -8px rgba(15, 23, 42, .12); }
        .nt-stat__icon {
            width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0; display: grid; place-items: center;
            color: #fff; font-size: 21px; background: linear-gradient(135deg, var(--c1), var(--c2));
            box-shadow: 0 8px 16px -6px var(--c2);
        }
        .nt-stat__value { display: block; font-size: 21px; font-weight: 800; color: #1e293b; line-height: 1.3; }
        .nt-stat__label { font-size: 12.5px; color: #64748b; }

        .nt-card { border: 1px solid #eef0f5; border-radius: 16px; box-shadow: 0 2px 8px rgba(15, 23, 42, .04); overflow: visible; }
        .nt-card__header {
            display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
            padding: 14px 20px; background: #fbfcfe; border-bottom: 1px solid #f0f2f7;
        }
        .nt-pills { display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 12px; flex-wrap: wrap; }
        .nt-pill {
            padding: 7px 16px; border-radius: 9px; font-size: 12.5px; font-weight: 700; color: #64748b;
            background: transparent; border: none; cursor: pointer; transition: .2s; font-family: inherit;
        }
        .nt-pill:hover { color: var(--nt-p-dark); }
        .nt-pill--active { background: #fff; color: var(--nt-p-dark); box-shadow: 0 2px 6px rgba(15, 23, 42, .08); }
        .nt-search { position: relative; }
        .nt-search i { position: absolute; right: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px !important; pointer-events: none; }
        .nt-search input {
            border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 32px 8px 12px; font-size: 13px;
            min-width: 220px; outline: none; font-family: inherit; transition: border-color .2s, box-shadow .2s;
        }
        .nt-search input:focus { border-color: var(--nt-p); box-shadow: 0 0 0 3px rgba(124, 58, 237, .12); }

        .nt-table thead th { background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; padding: 12px 16px; white-space: nowrap; border-bottom: 1px solid #eef0f5; }
        .nt-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f4f6f9; color: #334155; font-size: 13.5px; vertical-align: middle; }
        .nt-table tbody tr { transition: background .15s; }
        .nt-table tbody tr:hover { background: #faf8ff; }
        .nt-table tbody tr:last-child td { border-bottom: none; }

        .nt-title-cell { display: flex; align-items: center; gap: 10px; }
        .nt-type-icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; flex-shrink: 0; }
        .nt-type-icon i { font-size: 15px !important; }
        .nt-title-text { font-weight: 700; color: #1e293b; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .nt-msg { display: block; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #64748b; font-size: 12.5px; }
        .nt-type-badge { display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .nt-time { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 12.5px; white-space: nowrap; }
        .nt-time i { color: #a78bfa; }

        /* دریافت‌کنندگان + نوار پیشرفت خوانده‌شده */
        .nt-rc { min-width: 130px; }
        .nt-rc__num { font-weight: 800; color: #1e293b; font-size: 13.5px; }
        .nt-rc__bar { display: block; height: 5px; border-radius: 99px; background: #eef0f5; margin: 5px 0 3px; overflow: hidden; }
        .nt-rc__bar span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #34D399, #059669); }
        .nt-rc__read { font-size: 10.5px; color: #94a3b8; }

        /* بَج گروه هدف */
        .nt-tbadge {
            display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
            border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .nt-tbadge i { font-size: 11px !important; }
        .nt-tbadge--users { background: #EFF6FF; color: #2563EB; }
        .nt-tbadge--sellers { background: #FFF7ED; color: #EA580C; }
        .nt-tbadge--count { background: #f1f5f9; color: #475569; }

        .nt-actions { display: inline-flex; gap: 6px; }
        .nt-act {
            width: 32px; height: 32px; border-radius: 9px; border: 1px solid #e2e8f0; background: #fff;
            display: inline-grid; place-items: center; cursor: pointer; transition: .2s;
        }
        .nt-act i { font-size: 14px !important; }
        .nt-act--edit { color: var(--nt-p); }
        .nt-act--edit:hover { background: var(--nt-p); border-color: var(--nt-p); color: #fff; }
        .nt-act--del { color: #ef4444; }
        .nt-act--del:hover { background: #ef4444; border-color: #ef4444; color: #fff; }

        .nt-modal { border: none; border-radius: 16px; overflow: hidden; }
        .nt-modal__header { padding: 16px 20px; border-bottom: 1px solid #f0f2f7; }
        .nt-modal__header .modal-title { font-size: 15px; font-weight: 800; color: #1e293b; }
        .nt-modal__body { padding: 20px; max-height: 70vh; overflow-y: auto; }
        .nt-modal__footer { padding: 14px 20px; border-top: 1px solid #f0f2f7; display: flex; gap: 8px; justify-content: flex-end; }

        .nt-field { margin-bottom: 14px; }
        .nt-field > label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; }
        .nt-input {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 9px 12px;
            font-size: 13.5px; outline: none; background: #fff; font-family: inherit; transition: .2s;
        }
        .nt-input:focus { border-color: var(--nt-p); box-shadow: 0 0 0 3px rgba(124, 58, 237, .12); }
        .nt-input--ltr { direction: ltr; text-align: left; }
        textarea.nt-input { resize: vertical; min-height: 80px; }
        .nt-error { display: block; color: #ef4444; font-size: 11.5px; margin-top: 4px; }
        .nt-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ============ کارت‌های انتخاب گیرنده ============ */
        .nt-targets__note {
            display: flex; align-items: center; gap: 7px; margin: 0 0 10px;
            font-size: 12px; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe;
            padding: 9px 12px; border-radius: 10px;
        }
        .nt-targets__note i { color: #2563eb; flex-shrink: 0; font-size: 14px !important; }

        .nt-target-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .nt-target { border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; transition: .25s; }
        .nt-target--active { border-color: var(--nt-p); background: #fbfaff; box-shadow: 0 8px 20px -10px rgba(124, 58, 237, .35); }

        .nt-target__head { display: flex; align-items: center; gap: 12px; padding: 14px; cursor: pointer; user-select: none; position: relative; }
        .nt-target__head input { position: absolute; opacity: 0; pointer-events: none; }
        .nt-target__icon { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; transition: .25s; }
        .nt-target__icon i { font-size: 18px !important; }
        .nt-target__icon--users { background: #EFF6FF; color: #2563EB; }
        .nt-target__icon--sellers { background: #FFF7ED; color: #EA580C; }
        .nt-target--active .nt-target__icon--users { background: linear-gradient(135deg, #60A5FA, #2563EB); color: #fff; }
        .nt-target--active .nt-target__icon--sellers { background: linear-gradient(135deg, #FB923C, #EA580C); color: #fff; }
        .nt-target__title { display: block; font-size: 13.5px; font-weight: 800; color: #1e293b; }
        .nt-target__sub { display: block; font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
        .nt-target__checkbox {
            width: 22px; height: 22px; border-radius: 7px; border: 2px solid #cbd5e1; margin-inline-start: auto;
            display: grid; place-items: center; color: #fff; flex-shrink: 0; transition: .2s;
        }
        .nt-target__checkbox i { font-size: 13px !important; opacity: 0; transform: scale(.5); transition: .2s; }
        .nt-target--active .nt-target__checkbox { background: var(--nt-p); border-color: var(--nt-p); }
        .nt-target--active .nt-target__checkbox i { opacity: 1; transform: scale(1); }

        .nt-target__body { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
        .nt-target--active .nt-target__body { max-height: 500px; }
        .nt-target__body-inner { padding: 0 14px 14px; }

        /* پیکر کاربر */
        .nt-picker { position: relative; }
        .nt-picker__box { position: relative; }
        .nt-picker__box > i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px !important; pointer-events: none; }
        .nt-picker__search {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 32px 8px 30px;
            font-size: 12.5px; outline: none; font-family: inherit; transition: .2s;
        }
        .nt-picker__search:focus { border-color: var(--nt-p); box-shadow: 0 0 0 3px rgba(124, 58, 237, .12); }
        .nt-picker__spinner {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            width: 14px; height: 14px; border: 2px solid #e2e8f0; border-top-color: var(--nt-p);
            border-radius: 50%; animation: nt-spin .7s linear infinite; display: none;
        }
        .nt-picker--loading .nt-picker__spinner { display: block; }
        .nt-picker__results {
            position: absolute; top: calc(100% + 5px); inset-inline: 0; z-index: 50;
            background: #fff; border: 1px solid #eef0f5; border-radius: 12px;
            box-shadow: 0 14px 30px -10px rgba(15, 23, 42, .2); max-height: 210px; overflow-y: auto; display: none;
        }
        .nt-picker--open .nt-picker__results { display: block; }
        .nt-picker__item {
            display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 12px;
            border: none; background: none; cursor: pointer; text-align: right; font-family: inherit; transition: background .15s;
        }
        .nt-picker__item:hover { background: #f8fafc; }
        .nt-picker__item--selected { opacity: .5; cursor: default; }
        .nt-picker__avatar {
            width: 30px; height: 30px; border-radius: 50%; background: var(--nt-p-soft); color: var(--nt-p);
            display: grid; place-items: center; font-size: 12px; font-weight: 800; flex-shrink: 0;
        }
        .nt-picker__name { display: block; font-size: 12.5px; font-weight: 700; color: #1e293b; }
        .nt-picker__mobile { display: block; font-size: 11px; color: #94a3b8; direction: ltr; }
        .nt-picker__status { margin-inline-start: auto; color: #10b981; font-size: 14px !important; }
        .nt-picker__empty { padding: 14px; text-align: center; font-size: 12px; color: #94a3b8; }

        /* چیپ‌های انتخاب‌شده */
        .nt-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .nt-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--nt-p-soft); color: var(--nt-p-dark); border: 1px solid #ddd6fe;
            border-radius: 99px; padding: 4px 6px 4px 10px; font-size: 12px; font-weight: 600;
        }
        .nt-chip__avatar { width: 20px; height: 20px; border-radius: 50%; background: var(--nt-p); color: #fff; display: grid; place-items: center; font-size: 10px; font-weight: 800; }
        .nt-chip__remove {
            border: none; background: rgba(124, 58, 237, .12); color: var(--nt-p-dark);
            width: 18px; height: 18px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; padding: 0; transition: .2s;
        }
        .nt-chip__remove i { font-size: 11px !important; }
        .nt-chip__remove:hover { background: var(--nt-p); color: #fff; }

        .nt-target__hint {
            display: flex; align-items: center; gap: 6px; margin: 10px 0 0;
            font-size: 11.5px; color: #92400e; background: #fffbeb; border: 1px solid #fde68a;
            padding: 7px 10px; border-radius: 10px;
        }
        .nt-target__hint i { color: #d97706; flex-shrink: 0; font-size: 13px !important; }
        .nt-target__hint--lock { color: #475569; background: #f8fafc; border-color: #e2e8f0; }
        .nt-target__hint--lock i { color: #64748b; }
        .nt-edit-badges { display: flex; flex-wrap: wrap; gap: 6px; }

        .nt-delete-box { text-align: center; padding: 28px 22px; }
        .nt-delete-box__icon { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: grid; place-items: center; font-size: 26px; }
        .nt-delete-box h5 { font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .nt-delete-box p { color: #64748b; font-size: 13px; margin-bottom: 18px; }
        .nt-delete-box__btns { display: flex; justify-content: center; gap: 8px; }

        .nt-toasts { position: fixed; top: 20px; inset-inline-end: 20px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; }
        .nt-toast {
            display: flex; align-items: center; gap: 8px; background: #1e293b; color: #fff;
            padding: 11px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; max-width: 340px;
            box-shadow: 0 10px 24px -8px rgba(15, 23, 42, .4); transform: translateY(-12px); opacity: 0; transition: .3s;
        }
        .nt-toast--show { transform: translateY(0); opacity: 1; }
        .nt-toast--success { background: #065f46; }
        .nt-toast--error { background: #991b1b; }
        .nt-toast i { font-size: 16px !important; }

        .nt-loading { text-align: center; color: #94a3b8; padding: 32px !important; font-size: 13px; }
        .nt-spinner {
            width: 22px; height: 22px; border: 3px solid #e2e8f0; border-top-color: var(--nt-p);
            border-radius: 50%; display: inline-block; margin-inline-end: 8px; vertical-align: middle;
            animation: nt-spin .7s linear infinite;
        }
        @keyframes nt-spin { to { transform: rotate(360deg); } }
        .nt-empty { text-align: center; padding: 36px 16px; }
        .nt-empty__icon { width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 50%; background: var(--nt-p-soft); color: var(--nt-p); display: grid; place-items: center; font-size: 26px; }
        .nt-empty h5 { font-weight: 800; color: #1e293b; font-size: 14px; }

        @media (max-width: 768px) {
            .nt-card__header { flex-direction: column; align-items: stretch; }
            .nt-search input { min-width: 0; width: 100%; }
            .nt-grid2, .nt-target-grid { grid-template-columns: 1fr; }
            .nt-msg { max-width: 130px; }
        }
    </style>

@endsection

@push('scripts')
    <script>
        window.NOTIF_ROUTES = {
            list:       '{{ route('admin.notifications.list') }}',
            recipients: '{{ route('admin.notifications.recipients') }}',
            store:      '{{ route('admin.notifications.store') }}',
            update:     '{{ route('admin.notifications.update', ['batchId' => ':id']) }}',
            destroy:    '{{ route('admin.notifications.destroy', ['batchId' => ':id']) }}'
        };
    </script>
    <script src="{{ asset('back/assets/js/pages/notifications/index.js') }}"></script>
@endpush
